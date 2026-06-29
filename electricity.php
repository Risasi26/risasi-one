<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/SMSService.php';

$message = '';
$error = '';

// Ensure electricity rotation exists
$rotationStmt = $pdo->query('SELECT * FROM electricity_rotation LIMIT 1');
$rotation = $rotationStmt->fetch();

if (!$rotation) {
    $firstTenant = $pdo->query("SELECT id FROM tenants WHERE status = 'active' ORDER BY id ASC LIMIT 1")->fetch();
    if ($firstTenant) {
        $insert = $pdo->prepare('INSERT INTO electricity_rotation (current_tenant_id) VALUES (?)');
        $insert->execute([$firstTenant['id']]);
        $rotation = ['current_tenant_id' => $firstTenant['id']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activeTenantsStmt = $pdo->prepare('SELECT * FROM tenants WHERE status = ? ORDER BY id ASC');
    $activeTenantsStmt->execute(['active']);
    $activeTenants = $activeTenantsStmt->fetchAll();

    if (empty($activeTenants)) {
        $error = 'No active tenants available for electricity rotation.';
    } else {
        $currentId = $rotation['current_tenant_id'];
        $currentTenant = null;
        foreach ($activeTenants as $tenant) {
            if ($tenant['id'] == $currentId) {
                $currentTenant = $tenant;
                break;
            }
        }

        if (!$currentTenant) {
            $currentTenant = $activeTenants[0];
        }

        $nextTenant = null;
        foreach ($activeTenants as $index => $tenant) {
            if ($tenant['id'] == $currentTenant['id']) {
                $nextTenant = $activeTenants[($index + 1) % count($activeTenants)];
                break;
            }
        }

        $now = date('Y-m-d H:i:s');
        $directMessage = "Ni zamu yako kulipa umeme sasa. Tafadhali lipa ili huduma iendelee. Asante.";

        if ($currentTenant['contact_type'] === 'WhatsApp') {
            $directChannel = 'https://wa.me/' . preg_replace('/\D+/', '', $currentTenant['phone_number']);
            $insertLog = $pdo->prepare('INSERT INTO message_logs (tenant_id, tenant_name, phone_number, message_type, message_body, delivery_channel, provider, status, provider_response, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertLog->execute([$currentTenant['id'], $currentTenant['full_name'], $currentTenant['phone_number'], 'electricity', $directMessage, $directChannel, 'WhatsApp', 'n/a', 'WhatsApp link generated; no SMS sent.', $now]);
        } else {
            $result = sendSMS($currentTenant['phone_number'], $directMessage);
            logSMSResult($currentTenant['id'], $currentTenant['phone_number'], $directMessage, $result['status'], $result['provider_response'], 'electricity');
            $directChannel = 'SMS';
        }

        foreach ($activeTenants as $tenant) {
            if ($tenant['id'] === $currentTenant['id']) {
                continue;
            }
            $announcementBody = sprintf('Zamu ya kulipa umeme ni kwa %s (Chumba %s).', $currentTenant['full_name'], $currentTenant['room_number']);

            if ($tenant['contact_type'] === 'WhatsApp') {
                $channel = 'https://wa.me/' . preg_replace('/\D+/', '', $tenant['phone_number']);
                $insertLog = $pdo->prepare('INSERT INTO message_logs (tenant_id, tenant_name, phone_number, message_type, message_body, delivery_channel, provider, status, provider_response, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $insertLog->execute([$tenant['id'], $tenant['full_name'], $tenant['phone_number'], 'announcement', $announcementBody, $channel, 'WhatsApp', 'n/a', 'WhatsApp link generated; no SMS sent.', $now]);
            } else {
                $result = sendSMS($tenant['phone_number'], $announcementBody);
                logSMSResult($tenant['id'], $tenant['phone_number'], $announcementBody, $result['status'], $result['provider_response'], 'announcement');
            }
        }

        if ($nextTenant) {
            $updateRotation = $pdo->prepare('UPDATE electricity_rotation SET current_tenant_id = ?');
            $updateRotation->execute([$nextTenant['id']]);
            $rotation['current_tenant_id'] = $nextTenant['id'];
        }

        $message = 'Electricity reminder sent successfully and rotation moved to the next active tenant.';
    }
}

$currentTenant = null;
if ($rotation) {
    $tenantStmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ? LIMIT 1');
    $tenantStmt->execute([$rotation['current_tenant_id']]);
    $currentTenant = $tenantStmt->fetch();
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Electricity rotation</h2>
            <p>Send a reminder and move the turn to the next tenant.</p>
        </div>
    </div>
    <?php if ($message): ?>
        <div class="alert"><?php echo e($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error"><?php echo e($error); ?></div>
    <?php endif; ?>
    <div class="card">
        <h2>Current electricity payer</h2>
        <?php if ($currentTenant): ?>
            <p><strong><?php echo e($currentTenant['full_name']); ?></strong></p>
            <p>Room: <?php echo e($currentTenant['room_number']); ?></p>
            <p>Phone: <?php echo e($currentTenant['phone_number']); ?></p>
            <p>Contact type: <?php echo e($currentTenant['contact_type']); ?></p>
            <form method="post">
                <button type="submit" class="button button-primary">Send Electricity Reminder</button>
            </form>
        <?php else: ?>
            <p>No active tenant found for electricity rotation. Add an active tenant first.</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

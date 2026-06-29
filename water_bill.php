<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/SMSService.php';

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $totalAmount = trim($_POST['total_amount'] ?? '');
    $controlNumber = trim($_POST['control_number'] ?? '');
    $billMonth = trim($_POST['bill_month'] ?? '');

    if ($totalAmount === '' || !is_numeric($totalAmount) || $totalAmount <= 0) {
        $errors[] = 'Please enter a valid total water bill amount.';
    }
    if ($controlNumber === '') {
        $errors[] = 'Control number is required.';
    }
    if ($billMonth === '') {
        $errors[] = 'Bill month is required.';
    }

    $activeTenantsStmt = $pdo->prepare('SELECT * FROM tenants WHERE status = ? ORDER BY id ASC');
    $activeTenantsStmt->execute(['active']);
    $activeTenants = $activeTenantsStmt->fetchAll();

    if (empty($activeTenants)) {
        $errors[] = 'There are no active tenants to send the bill to.';
    }

    if (empty($errors)) {
        $tenantCount = count($activeTenants);
        $share = round($totalAmount / $tenantCount, 0);
        $messageBody = sprintf('Bill ya maji ya mwezi huu ni TSh %s. Kila mpangaji anatakiwa kulipa TSh %s. Control number ni %s.', number_format($totalAmount, 0, '.', ','), number_format($share, 0, '.', ','), e($controlNumber));

        $insertBill = $pdo->prepare('INSERT INTO water_bills (bill_month, total_amount, control_number, tenant_count, created_at) VALUES (?, ?, ?, ?, NOW())');
        $insertBill->execute([$billMonth, $totalAmount, $controlNumber, $tenantCount]);

        foreach ($activeTenants as $tenant) {
            if ($tenant['contact_type'] === 'WhatsApp') {
                $deliveryChannel = 'https://wa.me/' . preg_replace('/\D+/', '', $tenant['phone_number']);
                $insertLog = $pdo->prepare('INSERT INTO message_logs (tenant_id, tenant_name, phone_number, message_type, message_body, delivery_channel, provider, status, provider_response, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                $insertLog->execute([$tenant['id'], $tenant['full_name'], $tenant['phone_number'], 'water', $messageBody, $deliveryChannel, 'WhatsApp', 'n/a', 'WhatsApp link generated; no SMS sent.']);
            } else {
                $result = sendSMS($tenant['phone_number'], $messageBody);
                logSMSResult($tenant['id'], $tenant['phone_number'], $messageBody, $result['status'], $result['provider_response'], 'water');
            }
        }

        $message = 'Water bill generated and sent to all active tenants.';
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Water bill</h2>
            <p>Create and distribute the monthly water bill.</p>
        </div>
    </div>
    <div class="card">
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php echo e(implode('<br>', $errors)); ?>
            </div>
        <?php elseif ($message): ?>
            <div class="alert"><?php echo e($message); ?></div>
        <?php endif; ?>
        <form method="post" class="form-panel" novalidate>
            <div class="input-group">
                <label for="bill_month">Bill Month</label>
                <input type="month" id="bill_month" name="bill_month" required value="<?php echo e($_POST['bill_month'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="total_amount">Total Amount (TSh)</label>
                <input type="number" id="total_amount" name="total_amount" min="1" required value="<?php echo e($_POST['total_amount'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="control_number">Control Number</label>
                <input type="text" id="control_number" name="control_number" required value="<?php echo e($_POST['control_number'] ?? ''); ?>">
            </div>
            <button type="submit" class="button button-primary">Generate and Send Water Bill</button>
        </form>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

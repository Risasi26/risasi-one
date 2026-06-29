<?php
require_once 'includes/auth.php';
require_once 'config/database.php';
require_once 'includes/SMSService.php';

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $body = trim($_POST['message'] ?? '');

    if ($title === '') {
        $errors[] = 'Announcement title is required.';
    }
    if ($body === '') {
        $errors[] = 'Announcement message is required.';
    }

    $activeTenantsStmt = $pdo->prepare('SELECT * FROM tenants WHERE status = ? ORDER BY id ASC');
    $activeTenantsStmt->execute(['active']);
    $activeTenants = $activeTenantsStmt->fetchAll();

    if (empty($activeTenants)) {
        $errors[] = 'There are no active tenants to send this announcement to.';
    }

    if (empty($errors)) {
        $insertAnnouncement = $pdo->prepare('INSERT INTO announcements (title, message, created_at) VALUES (?, ?, NOW())');
        $insertAnnouncement->execute([$title, $body]);

        foreach ($activeTenants as $tenant) {
            if ($tenant['contact_type'] === 'WhatsApp') {
                $deliveryChannel = 'https://wa.me/' . preg_replace('/\D+/', '', $tenant['phone_number']);
                $insertLog = $pdo->prepare('INSERT INTO message_logs (tenant_id, tenant_name, phone_number, message_type, message_body, delivery_channel, provider, status, provider_response, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
                $insertLog->execute([$tenant['id'], $tenant['full_name'], $tenant['phone_number'], 'announcement', $body, $deliveryChannel, 'WhatsApp', 'n/a', 'WhatsApp link generated; no SMS sent.']);
            } else {
                $result = sendSMS($tenant['phone_number'], $body);
                logSMSResult($tenant['id'], $tenant['phone_number'], $body, $result['status'], $result['provider_response'], 'announcement');
            }
        }

        $message = 'Announcement sent to all active tenants.';
    }
}

$announcements = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 10')->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Announcements</h2>
            <p>Create announcements for all active tenants.</p>
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
                <label for="title">Title</label>
                <input type="text" id="title" name="title" required value="<?php echo e($_POST['title'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="message">Message</label>
                <textarea id="message" name="message" required><?php echo e($_POST['message'] ?? ''); ?></textarea>
            </div>
            <button type="submit" class="button button-primary">Send announcement</button>
        </form>
    </div>
    <div class="card">
        <h2>Recent announcements</h2>
        <?php if (count($announcements) > 0): ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $announcement): ?>
                            <tr>
                                <td><?php echo e($announcement['title']); ?></td>
                                <td><?php echo e($announcement['message']); ?></td>
                                <td><?php echo e($announcement['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p>No announcements have been sent yet.</p>
        <?php endif; ?>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

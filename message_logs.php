<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$stmt = $pdo->query('SELECT * FROM message_logs ORDER BY created_at DESC LIMIT 100');
$logs = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Message logs</h2>
            <p>Review all simulated notifications sent to tenants.</p>
        </div>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tenant</th>
                        <th>Phone</th>
                        <th>Type</th>
                        <th>Message</th>
                        <th>Channel</th>
                        <th>Provider</th>
                        <th>Status</th>
                        <th>Provider response</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($logs) === 0): ?>
                        <tr><td colspan="6">No messages have been logged yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo e($log['tenant_name']); ?></td>
                                <td><?php echo e($log['phone_number']); ?></td>
                                <td><?php echo e(ucfirst($log['message_type'])); ?></td>
                                <td><?php echo e($log['message_body']); ?></td>
                                <td>
                                    <?php if (strpos($log['delivery_channel'], 'https://wa.me') === 0): ?>
                                        <a href="<?php echo e($log['delivery_channel']); ?>" target="_blank">WhatsApp link</a>
                                    <?php else: ?>
                                        <?php echo e($log['delivery_channel']); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo e($log['provider'] ?? 'N/A'); ?></td>
                                <td><?php echo e($log['status'] ?? 'N/A'); ?></td>
                                <td><?php echo e($log['provider_response'] ?? ''); ?></td>
                                <td><?php echo e($log['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

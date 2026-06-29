<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Fetch summary data for dashboard cards
$totalTenants = $pdo->query("SELECT COUNT(*) FROM tenants WHERE status = 'active'")->fetchColumn();
$totalMessages = $pdo->query('SELECT COUNT(*) FROM message_logs')->fetchColumn();

$currentElectricity = null;
$electricityStmt = $pdo->query('SELECT er.current_tenant_id, t.full_name, t.room_number, t.phone_number, t.contact_type FROM electricity_rotation er LEFT JOIN tenants t ON er.current_tenant_id = t.id');
$currentElectricity = $electricityStmt->fetch();

$lastWater = $pdo->query('SELECT * FROM water_bills ORDER BY created_at DESC LIMIT 1')->fetch();
$announcements = $pdo->query('SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5')->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Dashboard</h2>
            <p>Welcome back, <?php echo e($_SESSION['admin_name']); ?>.</p>
        </div>
    </div>
    <div class="card-grid">
        <div class="card">
            <h2>Total active tenants</h2>
            <strong><?php echo e($totalTenants); ?></strong>
        </div>
        <div class="card">
            <h2>Current electricity payer</h2>
            <strong><?php echo $currentElectricity && $currentElectricity['full_name'] ? e($currentElectricity['full_name']) : 'No active tenant'; ?></strong>
            <?php if ($currentElectricity && $currentElectricity['room_number']): ?>
                <p>Room <?php echo e($currentElectricity['room_number']); ?></p>
            <?php endif; ?>
        </div>
        <div class="card">
            <h2>Last water bill</h2>
            <?php if ($lastWater): ?>
                <strong><?php echo e($lastWater['bill_month']); ?> - TSh <?php echo number_format($lastWater['total_amount'], 0, '.', ','); ?></strong>
            <?php else: ?>
                <strong>Not generated yet</strong>
            <?php endif; ?>
        </div>
        <div class="card">
            <h2>Total messages sent</h2>
            <strong><?php echo e($totalMessages); ?></strong>
        </div>
    </div>
    <div class="card">
        <div class="section-header">
            <h2>Recent announcements</h2>
        </div>
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

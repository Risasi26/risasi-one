<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

// Fetch all tenants by newest first
$stmt = $pdo->query('SELECT * FROM tenants ORDER BY created_at DESC');
$tenants = $stmt->fetchAll();
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Tenant management</h2>
            <p>View and manage tenant records.</p>
        </div>
        <a href="tenant_add.php" class="button button-primary">Add tenant</a>
    </div>
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Room</th>
                        <th>Contact type</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tenants) === 0): ?>
                        <tr><td colspan="7">No tenants found. Add one to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tenants as $tenant): ?>
                            <tr>
                                <td><?php echo e($tenant['full_name']); ?></td>
                                <td><?php echo e($tenant['phone_number']); ?></td>
                                <td><?php echo e($tenant['room_number']); ?></td>
                                <td><?php echo e($tenant['contact_type']); ?></td>
                                <td><span class="status-pill <?php echo $tenant['status'] === 'active' ? 'status-active' : 'status-inactive'; ?>"><?php echo e(ucfirst($tenant['status'])); ?></span></td>
                                <td><?php echo e($tenant['created_at']); ?></td>
                                <td class="actions">
                                    <a href="tenant_edit.php?id=<?php echo e($tenant['id']); ?>" class="button button-small link-button">Edit</a>
                                    <a href="tenant_edit.php?delete=<?php echo e($tenant['id']); ?>" class="button button-small link-button confirm-delete">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

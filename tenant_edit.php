<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$errors = [];

if (isset($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM tenants WHERE id = ?');
    $stmt->execute([$deleteId]);
    $_SESSION['flash_success'] = 'Tenant deleted successfully.';
    header('Location: tenants.php');
    exit;
}

if (!$id) {
    header('Location: tenants.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$tenant = $stmt->fetch();

if (!$tenant) {
    header('Location: tenants.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phoneNumber = trim($_POST['phone_number'] ?? '');
    $roomNumber = trim($_POST['room_number'] ?? '');
    $contactType = $_POST['contact_type'] ?? '';
    $status = $_POST['status'] ?? 'active';

    if ($fullName === '') {
        $errors[] = 'Full name is required.';
    }
    if ($phoneNumber === '') {
        $errors[] = 'Phone number is required.';
    }
    if ($roomNumber === '') {
        $errors[] = 'Room number is required.';
    }
    if (!in_array($contactType, ['WhatsApp', 'SMS/USSD'], true)) {
        $errors[] = 'Contact type is required.';
    }

    if (empty($errors)) {
        $update = $pdo->prepare('UPDATE tenants SET full_name = ?, phone_number = ?, room_number = ?, contact_type = ?, status = ? WHERE id = ?');
        $update->execute([$fullName, $phoneNumber, $roomNumber, $contactType, $status, $id]);
        $_SESSION['flash_success'] = 'Tenant updated successfully.';
        header('Location: tenants.php');
        exit;
    }
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>
<main class="content">
    <div class="section-header">
        <div>
            <h2>Edit tenant</h2>
            <p>Update tenant information.</p>
        </div>
        <a href="tenants.php" class="button button-secondary">Back to tenants</a>
    </div>
    <div class="card">
        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <?php echo e(implode('<br>', $errors)); ?>
            </div>
        <?php endif; ?>
        <form method="post" class="form-panel" novalidate>
            <div class="input-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required value="<?php echo e($_POST['full_name'] ?? $tenant['full_name']); ?>">
            </div>
            <div class="input-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" required value="<?php echo e($_POST['phone_number'] ?? $tenant['phone_number']); ?>">
            </div>
            <div class="input-group">
                <label for="room_number">Room Number</label>
                <input type="text" id="room_number" name="room_number" required value="<?php echo e($_POST['room_number'] ?? $tenant['room_number']); ?>">
            </div>
            <div class="input-group">
                <label for="contact_type">Contact Type</label>
                <select id="contact_type" name="contact_type" required>
                    <option value="">Select type</option>
                    <option value="WhatsApp" <?php echo ((isset($_POST['contact_type']) ? $_POST['contact_type'] : $tenant['contact_type']) === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                    <option value="SMS/USSD" <?php echo ((isset($_POST['contact_type']) ? $_POST['contact_type'] : $tenant['contact_type']) === 'SMS/USSD') ? 'selected' : ''; ?>>SMS/USSD</option>
                </select>
            </div>
            <div class="input-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo ((isset($_POST['status']) ? $_POST['status'] : $tenant['status']) === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo ((isset($_POST['status']) ? $_POST['status'] : $tenant['status']) === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="actions">
                <button type="submit" class="button button-primary">Update tenant</button>
                <a href="tenant_edit.php?delete=<?php echo e($tenant['id']); ?>" class="button button-secondary confirm-delete">Delete tenant</a>
            </div>
        </form>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

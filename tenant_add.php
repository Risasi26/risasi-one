<?php
require_once 'includes/auth.php';
require_once 'config/database.php';

$errors = [];
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
        $stmt = $pdo->prepare('INSERT INTO tenants (full_name, phone_number, room_number, contact_type, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())');
        $stmt->execute([$fullName, $phoneNumber, $roomNumber, $contactType, $status]);
        $_SESSION['flash_success'] = 'Tenant successfully added.';
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
            <h2>Add tenant</h2>
            <p>Create a new tenant profile.</p>
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
                <input type="text" id="full_name" name="full_name" required value="<?php echo e($_POST['full_name'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" id="phone_number" name="phone_number" required placeholder="2557XXXXXXX" value="<?php echo e($_POST['phone_number'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="room_number">Room Number</label>
                <input type="text" id="room_number" name="room_number" required value="<?php echo e($_POST['room_number'] ?? ''); ?>">
            </div>
            <div class="input-group">
                <label for="contact_type">Contact Type</label>
                <select id="contact_type" name="contact_type" required>
                    <option value="">Select type</option>
                    <option value="WhatsApp" <?php echo (($_POST['contact_type'] ?? '') === 'WhatsApp') ? 'selected' : ''; ?>>WhatsApp</option>
                    <option value="SMS/USSD" <?php echo (($_POST['contact_type'] ?? '') === 'SMS/USSD') ? 'selected' : ''; ?>>SMS/USSD</option>
                </select>
            </div>
            <div class="input-group">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="active" <?php echo (($_POST['status'] ?? '') === 'active') ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo (($_POST['status'] ?? '') === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <button type="submit" class="button button-primary">Save tenant</button>
        </form>
    </div>
</main>
<?php include 'includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/UserRepository.php';
require_role('Administrator');
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $userPk = filter_var($_POST['user_pk'] ?? null, FILTER_VALIDATE_INT);
        $role = $_POST['role'] ?? '';
        if ($userPk === false || !$role) throw new Exception('Invalid request.');
        UserRepository::updateRole((int)$userPk, $role);
        $message = 'Role updated successfully.';
    } catch (Exception $e) { $error = $e->getMessage(); }
}
$users = UserRepository::listUsers();
?>
<h2 class="h4 mb-3">Manage Account Roles</h2>
<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
<table class="table table-striped align-middle"><thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Current Role</th><th>Update Role</th></tr></thead><tbody><?php foreach ($users as $u): ?><tr><td><?php echo h($u['user_id']); ?></td><td><?php echo h($u['full_name']); ?></td><td><?php echo h($u['email']); ?></td><td><?php echo h($u['role']); ?></td><td><form method="post" class="d-flex gap-2"><input type="hidden" name="user_pk" value="<?php echo (int)$u['id']; ?>"><select class="form-select form-select-sm" name="role"><?php foreach (['Student','Instructor','Administrator'] as $role): ?><option value="<?php echo $role; ?>" <?php echo $u['role'] === $role ? 'selected' : ''; ?>><?php echo $role; ?></option><?php endforeach; ?></select><button class="btn btn-sm btn-primary" type="submit">Save</button></form></td></tr><?php endforeach; ?></tbody></table>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

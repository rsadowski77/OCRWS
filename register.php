<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/UserRepository.php';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if ($user_id === '') $errors['user_id'] = 'User ID is required.';
    if ($full_name === '') $errors['full_name'] = 'Full name is required.';
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Valid email is required.';
    if ($phone === '') $errors['phone'] = 'Phone is required.';
    if (strlen($password) < 8) $errors['password'] = 'Password must be at least 8 characters.';
    if ($password !== $confirm) $errors['confirm_password'] = 'Passwords do not match.';
    if (empty($errors)) {
        if (UserRepository::findByUserId($user_id)) $errors['user_id'] = 'That User ID is already in use.';
        if (UserRepository::findByEmail($email)) $errors['email'] = 'That email is already registered.';
    }
    if (empty($errors)) {
        try {
            UserRepository::createStudentWithProfile([
                'user_id' => $user_id,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'full_name' => $full_name,
                'email' => $email,
                'phone' => $phone,
            ]);
            header('Location: login.php?msg=' . urlencode('Registration successful. Please log in.'));
            exit;
        } catch (Exception $e) {
            $errors['form'] = 'Registration failed. Please try again.';
        }
    }
}
?>
<div class="row justify-content-center"><div class="col-md-7 col-lg-6"><div class="card shadow-sm"><div class="card-body">
<h2 class="h4 mb-3">Create Student Account</h2>
<?php if (!empty($errors['form'])): ?><div class="alert alert-danger"><?php echo h($errors['form']); ?></div><?php endif; ?>
<form method="post" novalidate>
<div class="mb-3"><label class="form-label">User ID</label><input class="form-control <?php echo isset($errors['user_id']) ? 'is-invalid' : ''; ?>" name="user_id" value="<?php echo old('user_id'); ?>"><?php if (isset($errors['user_id'])): ?><div class="invalid-feedback"><?php echo h($errors['user_id']); ?></div><?php endif; ?></div>
<div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" name="password"><?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?php echo h($errors['password']); ?></div><?php endif; ?></div>
<div class="mb-3"><label class="form-label">Confirm Password</label><input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" name="confirm_password"><?php if (isset($errors['confirm_password'])): ?><div class="invalid-feedback"><?php echo h($errors['confirm_password']); ?></div><?php endif; ?></div>
<div class="mb-3"><label class="form-label">Full Name</label><input class="form-control <?php echo isset($errors['full_name']) ? 'is-invalid' : ''; ?>" name="full_name" value="<?php echo old('full_name'); ?>"><?php if (isset($errors['full_name'])): ?><div class="invalid-feedback"><?php echo h($errors['full_name']); ?></div><?php endif; ?></div>
<div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" name="email" value="<?php echo old('email'); ?>"><?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?php echo h($errors['email']); ?></div><?php endif; ?></div>
<div class="mb-3"><label class="form-label">Phone</label><input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" name="phone" value="<?php echo old('phone'); ?>"><?php if (isset($errors['phone'])): ?><div class="invalid-feedback"><?php echo h($errors['phone']); ?></div><?php endif; ?></div>
<button class="btn btn-warning w-100" type="submit">Register</button>
</form></div></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

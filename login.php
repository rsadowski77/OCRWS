<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/UserRepository.php';
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = trim($_POST['user_id'] ?? '');
    $password = $_POST['password'] ?? '';
    $user = UserRepository::findByUserId($user_id);
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_pk'] = (int)$user['id'];
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role'] = $user['role'];
        header('Location: index.php?msg=' . urlencode('Login successful.'));
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<div class="row justify-content-center"><div class="col-md-6 col-lg-5"><div class="card shadow-sm"><div class="card-body">
<h2 class="h4 mb-3">Login</h2>
<?php if (!empty($_GET['msg'])): ?><div class="alert alert-success"><?php echo h($_GET['msg']); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
<form method="post"><div class="mb-3"><label class="form-label">User ID</label><input class="form-control" name="user_id" value="<?php echo old('user_id'); ?>"></div><div class="mb-3"><label class="form-label">Password</label><input type="password" class="form-control" name="password"></div><button class="btn btn-primary w-100" type="submit">Login</button></form>
</div></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

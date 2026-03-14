<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>OCRWS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">OCRWS</a>
    <div class="d-flex align-items-center flex-wrap">
      <?php if (!empty($_SESSION['user_id'])): ?>
        <a class="nav-link text-white me-3" href="courses.php">Courses</a>
        <a class="nav-link text-white me-4" href="my_schedule.php">My Schedule</a>
        <?php if (has_role('Instructor')): ?>
          <a class="nav-link text-white me-3" href="manage_offerings.php">Schedule Offerings</a>
          <a class="nav-link text-white me-4" href="roster.php">Rosters</a>
        <?php endif; ?>
<?php if (has_role('Administrator')): ?>
	<a class="nav-link text-white me-3" href="manage_courses.php">Manage Courses</a>
	<a class="nav-link text-white me-3" href="manage_users.php">Manage Roles</a>
	<a class="nav-link text-white me-4" href="admin_student_schedule.php">Admin Schedule</a>
<?php endif; ?>
        <span class="navbar-text text-white me-3">Signed in as <?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-outline-light btn-sm me-2" href="login.php">Login</a>
        <a class="btn btn-warning btn-sm" href="register.php">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<main class="container py-4">

<?php require_once __DIR__ . '/includes/master.php'; ?>
<div class="p-4 p-md-5 mb-4 text-white bg-primary rounded">
  <div class="col-md-10 px-0">
    <h1 class="display-6">Online Course Registration & Waitlist System</h1>
    <p class="lead my-3">Week 5 adds role-based access control and waitlist functionality for Students, Instructors, and Administrators.</p>
  </div>
</div>
<?php if (!empty($_GET['msg'])): ?>
  <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
<?php endif; ?>
<div class="row g-4">
  <div class="col-md-4"><div class="card h-100"><div class="card-body"><h5 class="card-title">Students</h5><p class="card-text">Browse offerings, enroll, join waitlists, claim seats, and manage schedules.</p></div></div></div>
  <div class="col-md-4"><div class="card h-100"><div class="card-body"><h5 class="card-title">Instructors</h5><p class="card-text">Schedule course offerings and view rosters for assigned offerings.</p></div></div></div>
  <div class="col-md-4"><div class="card h-100"><div class="card-body"><h5 class="card-title">Administrators</h5><p class="card-text">Manage roles, courses, student schedules, and override capacity by moving students from waitlists into enrollments.</p></div></div></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

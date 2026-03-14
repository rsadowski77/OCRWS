<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_role('Administrator');
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $code = trim($_POST['course_code'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT);
        if ($code === '' || $title === '' || $capacity === false || $capacity <= 0) throw new Exception('Please provide valid course details.');
        CourseRepository::createCourse($code, $title, (int)$capacity);
        $message = 'Course created successfully.';
    } catch (Exception $e) { $error = $e->getMessage(); }
}
$courses = CourseRepository::listCourses();
?>
<h2 class="h4 mb-3">Manage Courses</h2>
<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
<div class="row g-4"><div class="col-lg-5"><div class="card"><div class="card-body"><h3 class="h6">Create Course</h3><form method="post"><div class="mb-2"><label class="form-label">Course Code</label><input class="form-control" name="course_code" placeholder="SE420"></div><div class="mb-2"><label class="form-label">Title</label><input class="form-control" name="title" placeholder="Advanced Software Engineering"></div><div class="mb-3"><label class="form-label">Capacity</label><input type="number" min="1" class="form-control" name="capacity" value="30"></div><button class="btn btn-primary" type="submit">Create Course</button></form></div></div></div><div class="col-lg-7"><div class="card"><div class="card-body"><h3 class="h6">Current Catalog</h3><table class="table table-sm"><thead><tr><th>Code</th><th>Title</th><th>Capacity</th></tr></thead><tbody><?php foreach ($courses as $c): ?><tr><td><?php echo h($c['course_code']); ?></td><td><?php echo h($c['title']); ?></td><td><?php echo (int)$c['capacity']; ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

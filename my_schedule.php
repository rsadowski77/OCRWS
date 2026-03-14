<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_role('Student');

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $offeringPk = filter_var($_POST['offering_pk'] ?? null, FILTER_VALIDATE_INT);
    if ($offeringPk === false || $offeringPk === null) {
        $error = 'Invalid offering.';
    } else {
        CourseRepository::drop((int)$_SESSION['user_pk'], (int)$offeringPk);
        $message = 'Class dropped successfully.';
    }
}

$rows = CourseRepository::listMyEnrollments((int)$_SESSION['user_pk']);
?>
<h2 class="h4 mb-3">My Schedule</h2>
<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
<div class="mb-3"><a class="btn btn-outline-primary" href="courses.php">Browse Courses</a></div>

<div class="table-responsive">
<table class="table table-bordered align-middle">
  <thead><tr><th>Semester</th><th>Course</th><th>Title</th><th>Instructor</th><th></th></tr></thead>
  <tbody>
    <?php if (!$rows): ?><tr><td colspan="4" class="text-center text-muted">No enrollments yet.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?php echo h($r['term'] . ' ' . $r['year']); ?></td>
        <td><?php echo h($r['course_code']); ?></td>
        <td><?php echo h($r['title']); ?></td>
	<td><?php echo h($r['instructor_name']); ?></td>
        <td class="text-center">
          <form method="post" class="m-0">
            <input type="hidden" name="offering_pk" value="<?php echo (int)$r['offering_pk']; ?>">
            <button class="btn btn-sm btn-danger" type="submit">Drop</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

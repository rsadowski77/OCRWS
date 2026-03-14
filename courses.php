<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_once __DIR__ . '/includes/WaitlistRepository.php';
require_role('Student');
$semesters = CourseRepository::listSemesters();
$selectedSemester = int_get('semester') ?? ($semesters[0]['id'] ?? null);
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $offeringPk = filter_var($_POST['offering_pk'] ?? null, FILTER_VALIDATE_INT);
        if ($offeringPk === false || $offeringPk === null) throw new Exception('Invalid offering.');
        $action = $_POST['action'] ?? 'enroll';
        if ($action === 'claim') {
            WaitlistRepository::claimSeat((int)$_SESSION['user_pk'], (int)$offeringPk);
            $message = 'Seat claimed successfully.';
        } else {
            $result = WaitlistRepository::enrollOrWaitlist((int)$_SESSION['user_pk'], (int)$offeringPk);
            $message = $result === 'Enrolled' ? 'Enrollment successful.' : 'Course is full. You have been added to the waitlist.';
        }
    } catch (Exception $e) { $error = $e->getMessage(); }
}
$offerings = $selectedSemester ? CourseRepository::listOfferingsBySemester((int)$selectedSemester, (int)$_SESSION['user_pk']) : [];
?>
<h2 class="h4 mb-3">Course Offerings</h2>
<?php if ($message): ?>
	<div class="alert alert-success"><?php echo h($message); ?></div>
<?php endif; ?>
<?php if ($error): ?>
	<div class="alert alert-danger"><?php echo h($error); ?></div>
<?php endif; ?>
<form method="get" class="row g-2 align-items-end mb-3"><div class="col-md-6"><label class="form-label">Semester</label><select class="form-select" name="semester" onchange="this.form.submit()">
	<?php foreach ($semesters as $s): ?><option value="<?php echo (int)$s['id']; ?>" <?php echo ((int)$s['id'] === (int)$selectedSemester) ? 'selected' : ''; ?>><?php echo h($s['term'] . ' ' . $s['year']); ?></option><?php endforeach; ?></select></div></form>

<div class="table-responsive"><table class="table table-striped align-middle"><thead><tr><th>Course</th><th>Title</th><th>Instructor</th><th class="text-center">Capacity</th><th class="text-center">Enrolled</th><th class="text-center">Waitlist</th><th>Status</th><th></th></tr></thead>

<tbody>
<?php if (!$offerings): ?><tr><td colspan="8" class="text-center text-muted">No offerings found.</td></tr><?php endif ?>
<?php foreach ($offerings as $o): $capacity=(int)$o['capacity']; $enrolled=(int)$o['enrolled_count'];
	$waitlist=(int)$o['waitlist_count'];
	$left=max(0,$capacity-$enrolled);
	$myPos=$o['my_waitlist_position']!==null?(int)$o['my_waitlist_position']:null;
	$canClaim=($left>0 && $myPos===1 && (int)$o['first_wait_user_pk']===(int)$_SESSION['user_pk']); 
?>

<tr><td><?php echo h($o['course_code']) ?></td>
<td><?php echo h($o['title']) ?></td>
<td><?php echo h($o['instructor_name'] ?? 'TBD') ?></td>
<td class="text-center"><?php echo $capacity ?></td>
<td class="text-center"><?php echo $enrolled ?></td>
<td class="text-center"><?php echo $waitlist ?></td>
<td><?php if ((int)$o['is_enrolled']===1): ?><span class="badge bg-success">Enrolled</span>
	<?php elseif ($myPos !== null): ?>
		<span class="badge bg-warning text-dark">Waitlist #<?php echo $myPos ?>
	</span><?php else: ?>
		<span class="badge bg-secondary"><?php echo $left > 0 ? 'Open' : 'Full' ?></span>
	<?php endif?>
</td>
<td class="text-end">
	<?php if ((int)$o['is_enrolled']===1): ?>
		<a class="btn btn-sm btn-outline-secondary disabled" href="#">Registered</a>
	<?php elseif ($canClaim): ?>
		<form method="post" class="m-0"><input type="hidden" name="offering_pk" value="<?php echo (int)$o['offering_pk']; ?>"><input type="hidden" name="action" value="claim"><button class="btn btn-sm btn-primary" type="submit">Claim Seat</button></form>
	<?php else: ?>
		<form method="post" class="m-0"><input type="hidden" name="offering_pk" value="<?php echo (int)$o['offering_pk']; ?>"><button class="btn btn-sm btn-success" type="submit"><?php echo $left > 0 ? 'Enroll' : 'Join Waitlist'; ?></button></form>
	<?php endif; ?></td></tr>
<?php endforeach; ?>
	</tbody></table></div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

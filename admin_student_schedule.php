<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/UserRepository.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_once __DIR__ . '/includes/WaitlistRepository.php';
require_role('Administrator');
$students = UserRepository::listStudents();
$semesters = CourseRepository::listSemesters();
$selectedStudent = int_get('student') ?? ($students[0]['id'] ?? null);
$selectedSemester = int_get('semester') ?? ($semesters[0]['id'] ?? null);
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $studentPk = filter_var($_POST['student_pk'] ?? null, FILTER_VALIDATE_INT);
        $offeringPk = filter_var($_POST['offering_pk'] ?? null, FILTER_VALIDATE_INT);
        $action = $_POST['action'] ?? '';
        if ($studentPk === false || $offeringPk === false) throw new Exception('Invalid request.');
        if ($action === 'drop') {
            CourseRepository::drop((int)$studentPk, (int)$offeringPk);
            $message = 'Student removed from course successfully.';
        } elseif ($action === 'enroll') {
            if (CourseRepository::seatsLeft((int)$offeringPk) > 0) {
                CourseRepository::enrollDirect((int)$studentPk, (int)$offeringPk);
                $message = 'Student enrolled successfully.';
            } else {
                $result = WaitlistRepository::enrollOrWaitlist((int)$studentPk, (int)$offeringPk);
                $message = $result === 'Enrolled' ? 'Student enrolled successfully.' : 'Course is full. Student has been added to the waitlist.';
            }
        } else throw new Exception('Unknown action.');
    } catch (Exception $e) { $error = $e->getMessage(); }
}
$offerings = $selectedSemester ? CourseRepository::listOfferingsBySemester((int)$selectedSemester, (int)$selectedStudent) : [];
$current = $selectedStudent ? CourseRepository::listMyEnrollments((int)$selectedStudent) : [];
?>
<h2 class="h4 mb-3">Administrator Student Schedule Management</h2>
<?php if ($message): ?>
	<div class="alert alert-success">
		<?php echo h($message); ?></div>
	<?php endif; ?>
<?php if ($error): ?>
	<div class="alert alert-danger">
		<?php echo h($error); ?></div>
	<?php endif; ?>

<form method="get" class="row g-3 mb-4"><div class="col-md-5">
	<label class="form-label">Student</label>
		<select class="form-select" name="student">
			<?php foreach ($students as $s): ?>
				<option value="<?php echo (int)$s['id']; ?>" 
					<?php echo ((int)$selectedStudent === (int)$s['id']) ? 'selected' : ''; ?>>
					<?php echo h(($s['full_name'] ?: $s['user_id']) . ' [' . $s['user_id'] . ']'); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
<div class="col-md-4">
	<label class="form-label">Semester</label>
	<select class="form-select" name="semester" onchange="this.form.submit()">
		<?php foreach ($semesters as $sem): ?>
			<option value="<?php echo (int)$sem['id']; ?>" 
				<?php echo ((int)$selectedSemester === (int)$sem['id']) ? 'selected' : ''; ?>>
				<?php echo h($sem['term'] . ' ' . $sem['year']); ?>	
			</option>
		<?php endforeach; ?>
	</select>
</div>
</form>

<div class="row g-4">
	<div class="col-lg-7">
		<div class="card">
			<div class="card-body">
				<h3 class="h6">Offerings for Enrollment</h3>
					<table class="table table-sm">
						<thead><tr>
							<th>Course</th>
							<th>Title</th>
							<th>Seats Left</th>
						</tr></thead>
						<tbody>
							<?php foreach ($offerings as $o): $left=max(0,(int)$o['capacity']-(int)$o['enrolled_count']); ?>
								<tr><td><?php echo h($o['course_code']); ?></td>
								<td><?php echo h($o['title']); ?></td>
								<td><?php echo $left; ?></td>
								<td class="text-end">
									<form method="post" class="m-0">
										<input type="hidden" name="student_pk" value="<?php echo (int)$selectedStudent; ?>">
										<input type="hidden" name="offering_pk" value="<?php echo (int)$o['offering_pk']; ?>">
										<input type="hidden" name="action" value="enroll">
										<button class="btn btn-sm btn-success" type="submit">Register for Student</button></form></td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
			</div>
		</div>
	</div>
	<div class="col-lg-5">
		<div class="card">
			<div class="card-body">
				<h3 class="h6">Current Schedule</h3>
					<table class="table table-sm">
						<thead><tr><th>Course</th>
						<th>Semester</th>
						<th></th></tr></thead>
						<tbody>
							<?php if (!$current): ?><tr><td colspan="3" class="text-muted text-center">No current enrollments.</td></tr>
							<?php endif; ?>
							<?php foreach ($current as $row): ?>
								<tr><td><?php echo h($row['course_code']); ?></td>
								<td><?php echo h($row['term'] . ' ' . $row['year']); ?></td>
								<td class="text-end">
									<form method="post" class="m-0">
										<input type="hidden" name="student_pk" value="<?php echo (int)$selectedStudent; ?>">
										<input type="hidden" name="offering_pk" value="<?php echo (int)$row['offering_pk']; ?>">
										<input type="hidden" name="action" value="drop">
										<button class="btn btn-sm btn-danger" type="submit">Drop</button>
									</form>
								</td></tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

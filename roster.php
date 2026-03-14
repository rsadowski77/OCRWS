<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_once __DIR__ . '/includes/WaitlistRepository.php';
require_role('Instructor');

$selectedOffering = int_get('offering');
$offerings = has_role('Administrator')
    ? CourseRepository::listAllOfferings()
    : CourseRepository::listInstructorOfferings((int)$_SESSION['user_pk']);
$summary = $selectedOffering ? CourseRepository::offeringSummary($selectedOffering) : null;
$roster = $selectedOffering ? CourseRepository::listRoster($selectedOffering) : [];
$waitlist = $selectedOffering ? WaitlistRepository::listWaitlist($selectedOffering) : [];
?>
<h2 class="h4 mb-3">Course Rosters</h2>

<form method="get" class="row g-2 align-items-end mb-3">
  <div class="col-md-8">
    <label class="form-label">
      <?php echo has_role('Administrator') ? 'All Offerings' : 'My Offerings'; ?>
    </label>
    <select class="form-select" name="offering" onchange="this.form.submit()">
      <option value="">Select offering...</option>
      <?php foreach ($offerings as $o): ?>
        <option value="<?php echo (int)$o['offering_pk']; ?>"
          <?php echo ((int)$selectedOffering === (int)$o['offering_pk']) ? 'selected' : ''; ?>>
          <?php
	$instructorDisplay = !empty($o['instructor_name'])
		? $o['instructor_name'] : (!empty($o['instructor_user_id']) ? $o['instructor_user_id'] : 'TBD');
            echo h($o['term'] . ' ' . $o['year'] . ' - ' . $o['course_code'] . ' - ' . $o['title'] . ' - ' . $instructorDisplay);
          ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
</form>

<?php if ($summary): ?>
	<h3 class="h6"><?php echo h($summary['term'] . ' ' . $summary['year'] . ' - ' . $summary['course_code'] . ' - ' . $summary['title']); ?></h3>
<div class="row g-4">
	<div class="col-md-7">
		<div class="card">
			<div class="card-body">
				<h4 class="h6">Enrolled Students</h4><table class="table table-sm">
<thead><tr>
	<th>User ID</th>
	<th>Name</th>
	<th>Email</th>
</tr></thead>

<tbody>
<?php if (!$roster): ?>
	<tr><td colspan="3" class="text-muted text-center">No enrolled students.</td></tr>
<?php endif; ?>
<?php foreach ($roster as $r): ?>
	<tr><td><?php echo h($r['user_id']); ?></td><td><?php echo h($r['full_name']); ?></td><td><?php echo h($r['email']); ?></td></tr>
<?php endforeach; ?>
	</tbody></table></div></div></div>
<div class="col-md-5"><div class="card"><div class="card-body"><h4 class="h6">Waitlist</h4><table class="table table-sm"><thead><tr><th>Pos</th>
<th>User ID</th>
<th>Name</th></tr>

</thead><tbody><?php if (!$waitlist): ?>
	<tr><td colspan="3" class="text-muted text-center">No waitlist entries.</td></tr><?php endif; ?><?php foreach ($waitlist as $w): ?><tr><td><?php echo (int)$w['position']; ?></td><td><?php echo h($w['user_id']); ?></td>
	<td><?php echo h($w['full_name']); ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

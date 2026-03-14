<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_once __DIR__ . '/includes/WaitlistRepository.php';
require_role('Administrator');
$offeringPk = int_get('offering');
$message = null; $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $waitlistId = filter_var($_POST['waitlist_id'] ?? null, FILTER_VALIDATE_INT);
        if ($waitlistId === false || $waitlistId === null) throw new Exception('Invalid waitlist entry.');
        WaitlistRepository::adminOverrideEnroll((int)$waitlistId);
        $message = 'Student manually enrolled from waitlist.';
    } catch (Exception $e) { $error = $e->getMessage(); }
}
$summary = $offeringPk ? CourseRepository::offeringSummary($offeringPk) : null;
$waitlist = $offeringPk ? WaitlistRepository::listWaitlist($offeringPk) : [];
?>
<h2 class="h4 mb-3">Administrator Waitlist Override</h2>
<?php if ($message): ?><div class="alert alert-success"><?php echo h($message); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><?php echo h($error); ?></div><?php endif; ?>
<?php if ($summary): ?><h3 class="h6"><?php echo h($summary['term'] . ' ' . $summary['year'] . ' - ' . $summary['course_code'] . ' - ' . $summary['title']); ?></h3><?php else: ?><div class="alert alert-info">Open this page with an offering query string, for example: waitlist_admin.php?offering=1</div><?php endif; ?>
<table class="table table-striped"><thead><tr><th>Position</th><th>User ID</th><th>Name</th><th>Email</th><th></th></tr></thead><tbody><?php if (!$waitlist): ?><tr><td colspan="5" class="text-muted text-center">No waitlist entries.</td></tr><?php endif; ?><?php foreach ($waitlist as $w): ?><tr><td><?php echo (int)$w['position']; ?></td><td><?php echo h($w['user_id']); ?></td><td><?php echo h($w['full_name']); ?></td><td><?php echo h($w['email']); ?></td><td class="text-end"><form method="post" class="m-0"><input type="hidden" name="waitlist_id" value="<?php echo (int)$w['id']; ?>"><button class="btn btn-sm btn-warning" type="submit">Override Enroll</button></form></td></tr><?php endforeach; ?></tbody></table>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

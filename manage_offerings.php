
<?php
require_once __DIR__ . '/includes/master.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/CourseRepository.php';
require_once __DIR__ . '/includes/UserRepository.php';
require_once __DIR__ . '/includes/WaitlistRepository.php';

require_role('Instructor');

$semesters = CourseRepository::listSemesters();
$courses = CourseRepository::listCourses();
$instructors = UserRepository::listInstructors();

$message = null;
$error = null;

$selectedOffering = int_get('override_offering');
$allOfferings = has_role('Administrator') ? CourseRepository::listAllOfferings() : [];
$selectedWaitlist = ($selectedOffering && has_role('Administrator'))
    ? WaitlistRepository::listWaitlist((int)$selectedOffering)
    : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'create_offering') {
            $semesterPk = filter_var($_POST['semester_pk'] ?? null, FILTER_VALIDATE_INT);
            $coursePk = filter_var($_POST['course_pk'] ?? null, FILTER_VALIDATE_INT);

            $instructorPk = has_role('Administrator')
                ? filter_var($_POST['instructor_pk'] ?? null, FILTER_VALIDATE_INT)
                : (int)$_SESSION['user_pk'];

            if ($semesterPk === false || $coursePk === false || $instructorPk === false) {
                throw new Exception('Invalid semester, course, or instructor.');
            }

            CourseRepository::createOffering((int)$semesterPk, (int)$coursePk, (int)$instructorPk);
            $message = 'Course offering scheduled successfully.';
        }

        if ($action === 'override_waitlist') {
            if (!has_role('Administrator')) {
                throw new Exception('Only administrators can override waitlists.');
            }

            $waitlistId = filter_var($_POST['waitlist_id'] ?? null, FILTER_VALIDATE_INT);

            if ($waitlistId === false || $waitlistId === null) {
                throw new Exception('Invalid waitlist entry.');
            }

            WaitlistRepository::adminOverrideEnroll((int)$waitlistId);
            $message = 'Student manually enrolled from waitlist.';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }

    // Refresh after any changes
    $semesters = CourseRepository::listSemesters();
    $courses = CourseRepository::listCourses();
    $instructors = UserRepository::listInstructors();
    $allOfferings = has_role('Administrator') ? CourseRepository::listAllOfferings() : [];
    $selectedWaitlist = ($selectedOffering && has_role('Administrator'))
        ? WaitlistRepository::listWaitlist((int)$selectedOffering)
        : [];
}
?>

<h2 class="h4 mb-3">Schedule Course Offerings</h2>

<?php if ($message): ?>
  <div class="alert alert-success"><?php echo h($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="alert alert-danger"><?php echo h($error); ?></div>
<?php endif; ?>

<div class="row g-4">
  <div class="col-lg-6">
    <div class="card">
      <div class="card-body">
        <h3 class="h6">Create Offering</h3>
        <form method="post">
          <input type="hidden" name="action" value="create_offering">

          <div class="mb-2">
            <label class="form-label">Semester</label>
            <select class="form-select" name="semester_pk" required>
              <?php foreach ($semesters as $s): ?>
                <option value="<?php echo (int)$s['id']; ?>">
                  <?php echo h($s['term'] . ' ' . $s['year']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-2">
            <label class="form-label">Course</label>
            <select class="form-select" name="course_pk" required>
              <?php foreach ($courses as $c): ?>
                <option value="<?php echo (int)$c['id']; ?>">
                  <?php echo h($c['course_code'] . ' - ' . $c['title']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <?php if (has_role('Administrator')): ?>
            <div class="mb-3">
              <label class="form-label">Instructor</label>
              <select class="form-select" name="instructor_pk" required>
                <?php foreach ($instructors as $i): ?>
                  <option value="<?php echo (int)$i['id']; ?>">
                    <?php echo h(($i['full_name'] ?: $i['user_id']) . ' [' . $i['user_id'] . ']'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endif; ?>

          <button class="btn btn-primary" type="submit">Schedule Offering</button>
        </form>
      </div>
    </div>
  </div>

  <?php if (has_role('Administrator')): ?>
    <div class="col-lg-6">
      <div class="card">
        <div class="card-body">
          <h3 class="h6">Override Capacity from Waitlist</h3>

          <form method="get" class="mb-3">
            <label class="form-label">Select Offering</label>
            <select class="form-select" name="override_offering" onchange="this.form.submit()">
              <option value="">Choose an offering...</option>
              <?php foreach ($allOfferings as $o): ?>
                <?php
                  $instructorDisplay = !empty($o['instructor_name'])
                    ? $o['instructor_name']
                    : (!empty($o['instructor_user_id']) ? $o['instructor_user_id'] : 'TBD');
                ?>
                <option value="<?php echo (int)$o['offering_pk']; ?>"
                  <?php echo ((int)$selectedOffering === (int)$o['offering_pk']) ? 'selected' : ''; ?>>
                  <?php echo h($o['term'] . ' ' . $o['year'] . ' - ' . $o['course_code'] . ' - ' . $o['title'] . ' - ' . $instructorDisplay); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>

          <?php if ($selectedOffering): ?>
            <table class="table table-sm align-middle">
              <thead>
                <tr>
                  <th>Position</th>
                  <th>User ID</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$selectedWaitlist): ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">No waitlist entries for this offering.</td>
                  </tr>
                <?php endif; ?>

                <?php foreach ($selectedWaitlist as $w): ?>
                  <tr>
                    <td><?php echo (int)$w['position']; ?></td>
                    <td><?php echo h($w['user_id']); ?></td>
                    <td><?php echo h($w['full_name']); ?></td>
                    <td><?php echo h($w['email']); ?></td>
                    <td class="text-end">
                      <form method="post" class="m-0">
                        <input type="hidden" name="action" value="override_waitlist">
                        <input type="hidden" name="waitlist_id" value="<?php echo (int)$w['id']; ?>">
                        <button class="btn btn-sm btn-warning" type="submit">Override Enroll</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
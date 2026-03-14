<?php
require_once __DIR__ . '/Database.php';
class CourseRepository {
    public static function listSemesters(): array {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM semesters ORDER BY year DESC, term_order ASC')->fetchAll();
    }
    public static function listCourses(): array {
        $pdo = Database::connection();
        return $pdo->query('SELECT * FROM courses ORDER BY course_code')->fetchAll();
    }
    public static function createCourse(string $code, string $title, int $capacity): int {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO courses (course_code, title, capacity) VALUES (:code, :title, :capacity)');
        $stmt->execute(['code' => $code, 'title' => $title, 'capacity' => $capacity]);
        return (int)$pdo->lastInsertId();
    }
    public static function createOffering(int $semesterPk, int $coursePk, int $instructorPk): int {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO course_offerings (semester_pk, course_pk, instructor_pk) VALUES (:semester_pk, :course_pk, :instructor_pk)');
        $stmt->execute(['semester_pk' => $semesterPk, 'course_pk' => $coursePk, 'instructor_pk' => $instructorPk]);
        return (int)$pdo->lastInsertId();
    }
    public static function listOfferingsBySemester(int $semesterPk, ?int $currentUserPk = null): array {
        $pdo = Database::connection();
        $sql = 'SELECT co.id AS offering_pk, c.course_code, c.title, c.capacity, CONCAT(p.full_name, " (", u.user_id, ")") AS instructor_name,
                   (SELECT COUNT(*) FROM enrollments e WHERE e.offering_pk = co.id) AS enrolled_count,
                   (SELECT COUNT(*) FROM waitlist_entries w WHERE w.offering_pk = co.id AND w.status = "Waiting") AS waitlist_count,
                   EXISTS (SELECT 1 FROM enrollments e2 WHERE e2.offering_pk = co.id AND e2.user_pk = :current_user_pk) AS is_enrolled,
                   (SELECT w1.position FROM waitlist_entries w1 WHERE w1.offering_pk = co.id AND w1.user_pk = :current_user_pk AND w1.status = "Waiting" LIMIT 1) AS my_waitlist_position,
                   (SELECT w2.user_pk FROM waitlist_entries w2 WHERE w2.offering_pk = co.id AND w2.status = "Waiting" ORDER BY w2.position ASC LIMIT 1) AS first_wait_user_pk
            FROM course_offerings co
            JOIN courses c ON c.id = co.course_pk
            LEFT JOIN users u ON u.id = co.instructor_pk
            LEFT JOIN profiles p ON p.user_pk = u.id
            WHERE co.semester_pk = :semester_pk
            ORDER BY c.course_code';
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['semester_pk' => $semesterPk, 'current_user_pk' => $currentUserPk ?? 0]);
        return $stmt->fetchAll();
    }
    public static function listMyEnrollments(int $userPk): array {
    $pdo = Database::connection();
    $stmt = $pdo->prepare('
        SELECT
            co.id AS offering_pk,
            c.course_code,
            c.title,
            s.term,
            s.year,
            u.user_id AS instructor_user_id,
            p.full_name AS instructor_name
        FROM enrollments e
        JOIN course_offerings co ON co.id = e.offering_pk
        JOIN courses c ON c.id = co.course_pk
        JOIN semesters s ON s.id = co.semester_pk
        LEFT JOIN users u ON u.id = co.instructor_pk
        LEFT JOIN profiles p ON p.user_pk = u.id
        WHERE e.user_pk = :user_pk
        ORDER BY s.year DESC, s.term_order ASC, c.course_code
    ');
    $stmt->execute(['user_pk' => $userPk]);
    return $stmt->fetchAll();
}
public static function listAllOfferings(): array {
    $pdo = Database::connection();
    $stmt = $pdo->query('
        SELECT
            co.id AS offering_pk,
            c.course_code,
            c.title,
            s.term,
            s.year,
            u.user_id AS instructor_user_id,
            p.full_name AS instructor_name
        FROM course_offerings co
        JOIN courses c ON c.id = co.course_pk
        JOIN semesters s ON s.id = co.semester_pk
        LEFT JOIN users u ON u.id = co.instructor_pk
        LEFT JOIN profiles p ON p.user_pk = u.id
        ORDER BY s.year DESC, s.term_order ASC, c.course_code
    ');
    return $stmt->fetchAll();
}
public static function listRoster(int $offeringPk): array {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT u.id, u.user_id, p.full_name, p.email FROM enrollments e JOIN users u ON u.id = e.user_pk LEFT JOIN profiles p ON p.user_pk = u.id WHERE e.offering_pk = :offering_pk ORDER BY p.full_name, u.user_id');
        $stmt->execute(['offering_pk' => $offeringPk]);
        return $stmt->fetchAll();
    }
public static function listInstructorOfferings(int $instructorPk): array {
    $pdo = Database::connection();
    $stmt = $pdo->prepare('
        SELECT
            co.id AS offering_pk,
            c.course_code,
            c.title,
            s.term,
            s.year,
            u.user_id AS instructor_user_id,
            p.full_name AS instructor_name
        FROM course_offerings co
        JOIN courses c ON c.id = co.course_pk
        JOIN semesters s ON s.id = co.semester_pk
        LEFT JOIN users u ON u.id = co.instructor_pk
        LEFT JOIN profiles p ON p.user_pk = u.id
        WHERE co.instructor_pk = :instructor_pk
        ORDER BY s.year DESC, s.term_order ASC, c.course_code
    ');
    $stmt->execute(['instructor_pk' => $instructorPk]);
    return $stmt->fetchAll();
}
public static function seatsLeft(int $offeringPk): int {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT c.capacity - (SELECT COUNT(*) FROM enrollments e WHERE e.offering_pk = co.id) AS seats_left FROM course_offerings co JOIN courses c ON c.id = co.course_pk WHERE co.id = :offering_pk');
        $stmt->execute(['offering_pk' => $offeringPk]);
        $row = $stmt->fetch();
        return $row ? max(0, (int)$row['seats_left']) : 0;
    }
    public static function enrollDirect(int $userPk, int $offeringPk): void {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO enrollments (user_pk, offering_pk, created_at) VALUES (:user_pk, :offering_pk, NOW())');
        $stmt->execute(['user_pk' => $userPk, 'offering_pk' => $offeringPk]);
    }
public static function drop(int $userPk, int $offeringPk): void {
    $pdo = Database::connection();
    $pdo->beginTransaction();

    try {
        // Remove active enrollment
        $stmt = $pdo->prepare('
            DELETE FROM enrollments
            WHERE user_pk = :user_pk
              AND offering_pk = :offering_pk
        ');
        $stmt->execute([
            'user_pk' => $userPk,
            'offering_pk' => $offeringPk
        ]);

        // Optional: mark any existing waitlist lifecycle row as dropped
        $stmt = $pdo->prepare('
            UPDATE waitlist_entries
            SET status = "Dropped"
            WHERE user_pk = :user_pk
              AND offering_pk = :offering_pk
              AND status = "Enrolled"
        ');
        $stmt->execute([
            'user_pk' => $userPk,
            'offering_pk' => $offeringPk
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }

    // After drop succeeds, try automatic promotion
    require_once __DIR__ . '/WaitlistRepository.php';
    WaitlistRepository::autoPromoteFirstWaitlisted($offeringPk);
}
public static function offeringSummary(int $offeringPk): ?array {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT co.id AS offering_pk, c.course_code, c.title, s.term, s.year FROM course_offerings co JOIN courses c ON c.id = co.course_pk JOIN semesters s ON s.id = co.semester_pk WHERE co.id = :offering_pk LIMIT 1');
        $stmt->execute(['offering_pk' => $offeringPk]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

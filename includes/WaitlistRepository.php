<?php
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/CourseRepository.php';
class WaitlistRepository {
public static function enrollOrWaitlist(int $userPk, int $offeringPk): string {
    $pdo = Database::connection();

    // Already enrolled?
    $stmt = $pdo->prepare('
        SELECT 1
        FROM enrollments
        WHERE user_pk = :user_pk AND offering_pk = :offering_pk
    ');
    $stmt->execute([
        'user_pk' => $userPk,
        'offering_pk' => $offeringPk
    ]);

    if ($stmt->fetch()) {
        throw new Exception('You are already enrolled in this offering.');
    }

    // Seat available → enroll directly
    if (CourseRepository::seatsLeft($offeringPk) > 0) {
        CourseRepository::enrollDirect($userPk, $offeringPk);

        // If a waitlist row already exists, mark it Enrolled
        $stmt = $pdo->prepare('
            SELECT id
            FROM waitlist_entries
            WHERE user_pk = :user_pk AND offering_pk = :offering_pk
            LIMIT 1
        ');
        $stmt->execute([
            'user_pk' => $userPk,
            'offering_pk' => $offeringPk
        ]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare('
                UPDATE waitlist_entries
                SET status = "Enrolled"
                WHERE id = :id
            ');
            $stmt->execute(['id' => $existing['id']]);
        }

        return 'Enrolled';
    }

    // Otherwise: waitlist
    $stmt = $pdo->prepare('
        SELECT id
        FROM waitlist_entries
        WHERE user_pk = :user_pk AND offering_pk = :offering_pk
        LIMIT 1
    ');
    $stmt->execute([
        'user_pk' => $userPk,
        'offering_pk' => $offeringPk
    ]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare('
            UPDATE waitlist_entries
            SET status = "Waiting"
            WHERE id = :id
        ');
        $stmt->execute(['id' => $existing['id']]);
    } else {
        $stmt = $pdo->prepare('
            SELECT COALESCE(MAX(position), 0) + 1 AS next_position
            FROM waitlist_entries
            WHERE offering_pk = :offering_pk AND status = "Waiting"
        ');
        $stmt->execute(['offering_pk' => $offeringPk]);
        $next = (int)$stmt->fetch()['next_position'];

        $stmt = $pdo->prepare('
            INSERT INTO waitlist_entries (user_pk, offering_pk, position, status, created_at)
            VALUES (:user_pk, :offering_pk, :position, "Waiting", NOW())
        ');
        $stmt->execute([
            'user_pk' => $userPk,
            'offering_pk' => $offeringPk,
            'position' => $next
        ]);
    }

    return 'Waitlisted';
}
public static function autoPromoteFirstWaitlisted(int $offeringPk): bool {
    $pdo = Database::connection();
    $pdo->beginTransaction();

    try {
        // Make sure a seat is actually available
        if (CourseRepository::seatsLeft($offeringPk) <= 0) {
            $pdo->rollBack();
            return false;
        }

        // Find the first waiting student
        $stmt = $pdo->prepare('
            SELECT id, user_pk, offering_pk
            FROM waitlist_entries
            WHERE offering_pk = :offering_pk
              AND status = "Waiting"
            ORDER BY position ASC
            LIMIT 1
        ');
        $stmt->execute(['offering_pk' => $offeringPk]);
        $entry = $stmt->fetch();

        if (!$entry) {
            $pdo->rollBack();
            return false;
        }

        // Double-check the student is not already enrolled
        $stmt = $pdo->prepare('
            SELECT 1
            FROM enrollments
            WHERE user_pk = :user_pk
              AND offering_pk = :offering_pk
            LIMIT 1
        ');
        $stmt->execute([
            'user_pk' => $entry['user_pk'],
            'offering_pk' => $offeringPk
        ]);

        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare('
                INSERT INTO enrollments (user_pk, offering_pk, created_at)
                VALUES (:user_pk, :offering_pk, NOW())
            ');
            $stmt->execute([
                'user_pk' => $entry['user_pk'],
                'offering_pk' => $offeringPk
            ]);
        }

        // Mark waitlist entry as enrolled/processed
        $stmt = $pdo->prepare('
            UPDATE waitlist_entries
            SET status = "Enrolled"
            WHERE id = :id
        ');
        $stmt->execute(['id' => $entry['id']]);

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
    public static function listWaitlist(int $offeringPk): array {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT w.id, w.user_pk, w.position, w.status, u.user_id, p.full_name, p.email FROM waitlist_entries w JOIN users u ON u.id = w.user_pk LEFT JOIN profiles p ON p.user_pk = u.id WHERE w.offering_pk = :offering_pk AND w.status = "Waiting" ORDER BY w.position ASC');
        $stmt->execute(['offering_pk' => $offeringPk]);
        return $stmt->fetchAll();
    }
    public static function canClaimSeat(int $userPk, int $offeringPk): bool {
        if (CourseRepository::seatsLeft($offeringPk) <= 0) return false;
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT user_pk FROM waitlist_entries WHERE offering_pk = :offering_pk AND status = "Waiting" ORDER BY position ASC LIMIT 1');
        $stmt->execute(['offering_pk' => $offeringPk]);
        $row = $stmt->fetch();
        return $row && (int)$row['user_pk'] === $userPk;
    }
    public static function claimSeat(int $userPk, int $offeringPk): void {
    if (!self::canClaimSeat($userPk, $offeringPk)) {
        throw new Exception('You are not currently eligible to claim this seat.');
    }

    $pdo = Database::connection();
    $pdo->beginTransaction();

    try {
        CourseRepository::enrollDirect($userPk, $offeringPk);

        $stmt = $pdo->prepare('
            UPDATE waitlist_entries
            SET status = "Enrolled"
            WHERE user_pk = :user_pk
              AND offering_pk = :offering_pk
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
}
public static function adminOverrideEnroll(int $waitlistEntryId): void {
    $pdo = Database::connection();
    $pdo->beginTransaction();

    try {
        $stmt = $pdo->prepare('
            SELECT id, user_pk, offering_pk
            FROM waitlist_entries
            WHERE id = :id AND status = "Waiting"
            LIMIT 1
        ');
        $stmt->execute(['id' => $waitlistEntryId]);
        $row = $stmt->fetch();

        if (!$row) {
            throw new Exception('Waitlist entry not found.');
        }

        // Only insert enrollment if not already enrolled
        $stmt = $pdo->prepare('
            SELECT 1
            FROM enrollments
            WHERE user_pk = :user_pk AND offering_pk = :offering_pk
        ');
        $stmt->execute([
            'user_pk' => $row['user_pk'],
            'offering_pk' => $row['offering_pk']
        ]);

        if (!$stmt->fetch()) {
            CourseRepository::enrollDirect((int)$row['user_pk'], (int)$row['offering_pk']);
        }

        $stmt = $pdo->prepare('
            UPDATE waitlist_entries
            SET status = "Enrolled"
            WHERE id = :id
        ');
        $stmt->execute(['id' => $waitlistEntryId]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
}

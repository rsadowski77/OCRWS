<?php
require_once __DIR__ . '/Database.php';
class UserRepository {
    public static function findByUserId(string $userId): ?array {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = :user_id LIMIT 1');
        $stmt->execute(['user_id' => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function findByEmail(string $email): ?array {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM profiles WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
    public static function createStudentWithProfile(array $data): int {
        $pdo = Database::connection();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO users (user_id, password_hash, role, created_at) VALUES (:user_id, :password_hash, :role, NOW())');
            $stmt->execute(['user_id' => $data['user_id'], 'password_hash' => $data['password_hash'], 'role' => 'Student']);
            $userPk = (int)$pdo->lastInsertId();
            $stmt2 = $pdo->prepare('INSERT INTO profiles (user_pk, full_name, email, phone, created_at) VALUES (:user_pk, :full_name, :email, :phone, NOW())');
            $stmt2->execute(['user_pk' => $userPk, 'full_name' => $data['full_name'], 'email' => $data['email'], 'phone' => $data['phone']]);
            $pdo->commit();
            return $userPk;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    public static function listUsers(): array {
        $pdo = Database::connection();
        return $pdo->query('SELECT u.id, u.user_id, u.role, p.full_name, p.email FROM users u LEFT JOIN profiles p ON p.user_pk = u.id ORDER BY FIELD(u.role, "Administrator", "Instructor", "Student"), u.user_id')->fetchAll();
    }
    public static function listStudents(): array {
        $pdo = Database::connection();
        return $pdo->query('SELECT u.id, u.user_id, p.full_name FROM users u LEFT JOIN profiles p ON p.user_pk = u.id WHERE u.role = "Student" ORDER BY p.full_name, u.user_id')->fetchAll();
    }
    public static function listInstructors(): array {
        $pdo = Database::connection();
        return $pdo->query('SELECT u.id, u.user_id, p.full_name FROM users u LEFT JOIN profiles p ON p.user_pk = u.id WHERE u.role IN ("Instructor", "Administrator") ORDER BY p.full_name, u.user_id')->fetchAll();
    }
    public static function updateRole(int $userPk, string $role): void {
        $allowed = ['Student', 'Instructor', 'Administrator'];
        if (!in_array($role, $allowed, true)) throw new Exception('Invalid role.');
        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $stmt->execute(['role' => $role, 'id' => $userPk]);
    }
}

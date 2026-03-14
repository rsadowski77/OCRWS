<?php
if (session_status() === PHP_SESSION_NONE) session_start();
function role_rank(string $role): int {
    return match ($role) {
        'Administrator' => 3,
        'Instructor' => 2,
        default => 1,
    };
}
function has_role(string $requiredRole): bool {
    if (empty($_SESSION['role'])) return false;
    return role_rank($_SESSION['role']) >= role_rank($requiredRole);
}
function require_login(): void {
    if (empty($_SESSION['user_id'])) { header('Location: login.php'); exit; }
}
function require_role(string $requiredRole): void {
    require_login();
    if (!has_role($requiredRole)) { http_response_code(403); exit('Access denied.'); }
}

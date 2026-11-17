<?php
// includes/auth_guard.php
// Session Management & Role-Based Access Control (RBAC)

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function requireAuth(?string $requiredRole = null): void
{
    if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
        header('Location: /QuizMaster_Test/index.php?error=unauthorized');
        exit;
    }

    if ($requiredRole !== null && $_SESSION['role'] !== $requiredRole) {
        if ($_SESSION['role'] === 'admin') {
            header('Location: /QuizMaster_Test/admin/dashboard.php');
        } else {
            header('Location: /QuizMaster_Test/student/portal.php');
        }
        exit;
    }
}
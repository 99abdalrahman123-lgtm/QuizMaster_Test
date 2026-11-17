<?php
// actions/auth_action.php
// Authentication & RBAC Business Logic (Login, Register, Logout)

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Handle Logout Request (GET)
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
    header('Location: /QuizMaster_Test/index.php?success=logged_out');
    exit;
}

// Enforce POST Method for Login & Registration
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuizMaster_Test/index.php');
    exit;
}

$action   = $_POST['action'] ?? '';
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($action === 'register') {
    $fullName        = trim($_POST['full_name'] ?? '');
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role            = 'student';

    // Validate Required Fields
    if ($fullName === '' || $email === '' || $password === '' || $confirmPassword === '') {
        header('Location: /QuizMaster_Test/index.php?tab=register&error=empty_fields');
        exit;
    }

    // Validate Email Format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: /QuizMaster_Test/index.php?tab=register&error=invalid_email');
        exit;
    }

    // Validate Password Length & Match
    if (strlen($password) < 6) {
        header('Location: /QuizMaster_Test/index.php?tab=register&error=weak_password');
        exit;
    }

    if ($password !== $confirmPassword) {
        header('Location: /QuizMaster_Test/index.php?tab=register&error=password_mismatch');
        exit;
    }

    try {
        // Check if Email Already Exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            header('Location: /QuizMaster_Test/index.php?tab=register&error=email_exists');
            exit;
        }

        // Hash Password & Insert New Student
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $insertStmt = $pdo->prepare(
            'INSERT INTO users (full_name, email, password_hash, role) VALUES (:full_name, :email, :password_hash, :role)'
        );
        $insertStmt->execute([
            'full_name'     => $fullName,
            'email'         => $email,
            'password_hash' => $passwordHash,
            'role'          => $role
        ]);

        header('Location: /QuizMaster_Test/index.php?success=registered');
        exit;
    } catch (PDOException $e) {
        error_log('Registration Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/index.php?tab=register&error=server_error');
        exit;
    }
}

if ($action === 'login') {
    if ($email === '' || $password === '') {
        header('Location: /QuizMaster_Test/index.php?error=empty_fields');
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'SELECT id, full_name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
        );
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            header('Location: /QuizMaster_Test/index.php?error=invalid_credentials');
            exit;
        }

        // Regenerate Session ID to Prevent Session Fixation
        session_regenerate_id(true);

        $_SESSION['user_id']   = (int) $user['id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        // Role-Based Redirection (RBAC)
        if ($user['role'] === 'admin') {
            header('Location: /QuizMaster_Test/admin/dashboard.php');
        } else {
            header('Location: /QuizMaster_Test/student/portal.php');
        }
        exit;
    } catch (PDOException $e) {
        error_log('Login Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/index.php?error=server_error');
        exit;
    }
}

// Fallback Redirect for Invalid Action
header('Location: /QuizMaster_Test/index.php');
exit;
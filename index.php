<?php
// index.php
// Unified Authentication Portal (Sign In & Student Registration)

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect Authenticated Users Based on Role
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: /QuizMaster_Test/admin/dashboard.php');
    } else {
        header('Location: /QuizMaster_Test/student/portal.php');
    }
    exit;
}

$activeTab = (isset($_GET['tab']) && $_GET['tab'] === 'register') ? 'register' : 'login';

$errorMessages = [
    'unauthorized'        => 'Please sign in to access that page.',
    'empty_fields'        => 'All fields are required.',
    'invalid_email'       => 'Please enter a valid academic or personal email address.',
    'weak_password'       => 'Password must be at least 6 characters long.',
    'password_mismatch'   => 'Password and confirmation do not match.',
    'email_exists'        => 'An account with this email already exists.',
    'invalid_credentials' => 'Invalid email or password.',
    'server_error'        => 'An unexpected server error occurred. Please try again.'
];

$successMessages = [
    'registered' => 'Account created successfully. You can now sign in.',
    'logged_out' => 'You have been signed out securely.'
];

$errorKey   = $_GET['error'] ?? '';
$successKey = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster | Assessment Platform Authentication</title>
    <link rel="stylesheet" href="/QuizMaster_Test/assets/css/style.css">
</head>
<body>
    <div class="auth-layout">
        <!-- Left Brand Panel -->
        <aside class="auth-brand-panel">
            <div class="brand-logo">
                <span class="brand-badge">QM</span>
                <span>QuizMaster</span>
            </div>

            <div class="brand-hero">
                <h1>Academic Precision Assessment Engine</h1>
                <p>Structured digital examination environment with automated grading, real-time attempt tracking, and docked scientific calculation tools.</p>
                <ul class="feature-list">
                    <li><span class="feature-dot"></span>Role-Based Access Control for Instructors &amp; Students</li>
                    <li><span class="feature-dot"></span>Timed Assessment Sessions with Auto-Submission</li>
                    <li><span class="feature-dot"></span>Instant Score Analytics &amp; Question Bank Management</li>
                </ul>
            </div>

            <div class="brand-footer">
                QuizMaster Platform &bull; 3-Tier Web Architecture
            </div>
        </aside>

        <!-- Right Auth Card Panel -->
        <main class="auth-form-panel">
            <div class="auth-card">
                <div class="auth-header">
                    <h2 id="auth-title"><?= $activeTab === 'register' ? 'Create Student Account' : 'Welcome Back' ?></h2>
                    <p id="auth-subtitle"><?= $activeTab === 'register' ? 'Register to join active quizzes and track your grades.' : 'Sign in with your institutional credentials.' ?></p>
                </div>

                <!-- Tab Switcher -->
                <div class="auth-tabs">
                    <button type="button" class="tab-btn <?= $activeTab === 'login' ? 'active' : '' ?>" id="tab-login" onclick="switchTab('login')">
                        Sign In
                    </button>
                    <button type="button" class="tab-btn <?= $activeTab === 'register' ? 'active' : '' ?>" id="tab-register" onclick="switchTab('register')">
                        Create Account
                    </button>
                </div>

                <!-- Feedback Alerts -->
                <?php if ($errorKey !== '' && isset($errorMessages[$errorKey])): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($errorMessages[$errorKey], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <?php if ($successKey !== '' && isset($successMessages[$successKey])): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($successMessages[$successKey], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form id="form-login" action="/QuizMaster_Test/actions/auth_action.php" method="POST" class="<?= $activeTab === 'login' ? '' : 'hidden' ?>">
                    <input type="hidden" name="action" value="login">

                    <div class="form-group">
                        <label for="login-email">Email Address</label>
                        <input type="email" id="login-email" name="email" class="form-control" placeholder="name@university.edu" required>
                    </div>

                    <div class="form-group">
                        <label for="login-password">Password</label>
                        <input type="password" id="login-password" name="password" class="form-control" placeholder="Enter your password" required>
                    </div>

                    <button type="submit" class="btn-primary">Sign In to Portal</button>
                </form>

                <!-- Register Form -->
                <form id="form-register" action="/QuizMaster_Test/actions/auth_action.php" method="POST" class="<?= $activeTab === 'register' ? '' : 'hidden' ?>">
                    <input type="hidden" name="action" value="register">

                    <div class="form-group">
                        <label for="reg-name">Full Name</label>
                        <input type="text" id="reg-name" name="full_name" class="form-control" placeholder="Abdelrahman Sherif" required>
                    </div>

                    <div class="form-group">
                        <label for="reg-email">Email Address</label>
                        <input type="email" id="reg-email" name="email" class="form-control" placeholder="student@university.edu" required>
                    </div>

                    <div class="form-group">
                        <label for="reg-password">Password</label>
                        <input type="password" id="reg-password" name="password" class="form-control" placeholder="Minimum 6 characters" minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label for="reg-confirm">Confirm Password</label>
                        <input type="password" id="reg-confirm" name="confirm_password" class="form-control" placeholder="Re-enter password" minlength="6" required>
                    </div>

                    <button type="submit" class="btn-primary">Create Student Account</button>
                </form>
            </div>
        </main>
    </div>

    <script>
        function switchTab(tab) {
            const loginForm = document.getElementById('form-login');
            const registerForm = document.getElementById('form-register');
            const loginTab = document.getElementById('tab-login');
            const registerTab = document.getElementById('tab-register');
            const title = document.getElementById('auth-title');
            const subtitle = document.getElementById('auth-subtitle');

            if (tab === 'register') {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                loginTab.classList.remove('active');
                registerTab.classList.add('active');
                title.textContent = 'Create Student Account';
                subtitle.textContent = 'Register to join active quizzes and track your grades.';
            } else {
                registerForm.classList.add('hidden');
                loginForm.classList.remove('hidden');
                registerTab.classList.remove('active');
                loginTab.classList.add('active');
                title.textContent = 'Welcome Back';
                subtitle.textContent = 'Sign in with your institutional credentials.';
            }
        }
    </script>
</body>
</html>
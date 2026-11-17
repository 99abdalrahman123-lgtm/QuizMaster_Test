<?php
// student/portal.php
// Student Portal — Join Assessment & Attempt History (QM-14)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('student');

$studentId   = (int) $_SESSION['user_id'];
$studentName = htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8');

// Fetch attempt history with quiz details
$historyStmt = $pdo->prepare(
    'SELECT a.id AS attempt_id, q.title AS quiz_title,
            a.score, a.total_points, a.submitted_at
     FROM attempts a
     JOIN quizzes q ON q.id = a.quiz_id
     WHERE a.student_id = :student_id
     ORDER BY a.submitted_at DESC'
);
$historyStmt->execute(['student_id' => $studentId]);
$attempts = $historyStmt->fetchAll();

// Feedback resolution
$errorMessages = [
    'invalid_code'     => 'No active quiz found with that code. Please check and try again.',
    'quiz_closed'      => 'This quiz is currently closed and not accepting submissions.',
    'empty_quiz'       => 'This quiz has no questions yet. Contact your instructor.',
    'already_attempted'=> 'You have already submitted an attempt for this quiz.',
    'invalid_quiz'     => 'Invalid quiz identifier.',
    'server_error'     => 'A server error occurred. Please try again.',
];
$successMessages = [
    'exam_submitted' => 'Your exam has been submitted and graded.',
];

$errorMsg   = $errorMessages[$_GET['error'] ?? '']   ?? '';
$successMsg = $successMessages[$_GET['success'] ?? ''] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal — QuizMaster</title>
    <link rel="stylesheet" href="/QuizMaster_Test/assets/css/style.css">
</head>
<body>

<!-- Top Navigation Bar -->
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand-logo">
            <span class="brand-badge">QM</span>
            QuizMaster
        </div>
        <div class="topbar-actions">
            <span class="user-greeting">
                <span class="role-tag">Student</span>
                <?= $studentName ?>
            </span>
            <a href="/QuizMaster_Test/actions/auth_action.php?action=logout" class="btn-outline">Sign Out</a>
        </div>
    </div>
</header>

<main class="container">

    <div class="page-header">
        <div>
            <h1>Student Portal</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">
                Welcome back, <?= $studentName ?>. Enter a code to join an active assessment.
            </p>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($errorMsg !== ''): ?>
        <div class="alert alert-error" role="alert"><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($successMsg, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="dashboard-grid">

        <!-- LEFT: Join Assessment Card -->
        <div class="card">
            <div class="card-header">
                <h2>Join Active Assessment</h2>
            </div>
            <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1.25rem;">
                Enter the unique access code provided by your instructor to begin.
            </p>
            <form action="/QuizMaster_Test/actions/exam_action.php" method="POST" novalidate>
                <input type="hidden" name="action" value="join_quiz">
                <div class="form-group">
                    <label for="access-code">Access Code <span style="color:#DC2626">*</span></label>
                    <input
                        type="text"
                        id="access-code"
                        name="access_code"
                        class="form-control"
                        placeholder="e.g., QM-3A7F2B"
                        maxlength="20"
                        autocomplete="off"
                        style="font-family: 'Consolas', monospace; font-size:1.05rem; letter-spacing:0.1em; text-transform:uppercase;"
                        required
                    >
                </div>
                <button type="submit" class="btn-primary" style="margin-top:0.5rem;">
                    Start Assessment &rarr;
                </button>
            </form>
        </div>

        <!-- RIGHT: Attempt History -->
        <div class="card">
            <div class="card-header">
                <h2>Attempt History</h2>
                <span style="color:var(--text-muted); font-size:0.85rem;"><?= count($attempts) ?> attempt<?= count($attempts) !== 1 ? 's' : '' ?></span>
            </div>

            <?php if (count($attempts) === 0): ?>
                <div class="empty-state">
                    <p>No attempts yet. Join an assessment to get started.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Score</th>
                                <th>%</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($attempts as $attempt): ?>
                            <?php
                                $pct    = $attempt['total_points'] > 0
                                            ? round((float)$attempt['score'] / (float)$attempt['total_points'] * 100, 1)
                                            : 0.0;
                                $passed = $pct >= 50;
                                $date   = date('d M Y', strtotime($attempt['submitted_at']));
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($attempt['quiz_title'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td style="text-align:center;">
                                    <?= htmlspecialchars((string)$attempt['score'], ENT_QUOTES, 'UTF-8') ?>
                                    / <?= htmlspecialchars((string)$attempt['total_points'], ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td style="text-align:center;">
                                    <span class="code-badge"><?= $pct ?>%</span>
                                </td>
                                <td>
                                    <span class="status-pill <?= $passed ? 'status-active' : 'status-closed' ?>">
                                        <?= $passed ? 'Passed' : 'Failed' ?>
                                    </span>
                                </td>
                                <td style="font-size:0.82rem; color:var(--text-muted);"><?= $date ?></td>
                                <td>
                                    <a href="/QuizMaster_Test/student/result.php?attempt_id=<?= (int)$attempt['attempt_id'] ?>"
                                        class="btn-sm btn-secondary-sm">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>

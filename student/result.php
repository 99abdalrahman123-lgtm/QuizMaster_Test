<?php
// student/result.php
// Exam Result & Score Summary Page (QM-15)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('student');

$studentId  = (int) $_SESSION['user_id'];
$attemptId  = (int) ($_GET['attempt_id'] ?? 0);

if ($attemptId <= 0) {
    header('Location: /QuizMaster_Test/student/portal.php');
    exit;
}

// Fetch attempt — verify it belongs to this student
$stmt = $pdo->prepare(
    'SELECT a.id, a.score, a.total_points, a.submitted_at,
            q.title AS quiz_title, q.description AS quiz_description
     FROM attempts a
     JOIN quizzes q ON q.id = a.quiz_id
     WHERE a.id = :attempt_id AND a.student_id = :student_id
     LIMIT 1'
);
$stmt->execute(['attempt_id' => $attemptId, 'student_id' => $studentId]);
$attempt = $stmt->fetch();

if (!$attempt) {
    header('Location: /QuizMaster_Test/student/portal.php');
    exit;
}

$score       = (float) $attempt['score'];
$totalPoints = (float) $attempt['total_points'];
$percentage  = $totalPoints > 0 ? round($score / $totalPoints * 100, 1) : 0.0;
$passed      = $percentage >= 50;
$studentName = htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8');
$quizTitle   = htmlspecialchars($attempt['quiz_title'],  ENT_QUOTES, 'UTF-8');
$submittedAt = htmlspecialchars($attempt['submitted_at'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Result — <?= $quizTitle ?> | QuizMaster</title>
    <link rel="stylesheet" href="/QuizMaster_Test/assets/css/style.css">
</head>
<body>

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
            <h1>Assessment Result</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;"><?= $quizTitle ?></p>
        </div>
    </div>

    <!-- Result Hero Card -->
    <div class="result-hero-card">

        <!-- Pass / Fail Banner -->
        <div class="result-status-banner <?= $passed ? 'result-passed' : 'result-failed' ?>">
            <span class="result-status-icon"><?= $passed ? '&#10003;' : '&#10007;' ?></span>
            <span class="result-status-text"><?= $passed ? 'Passed' : 'Failed' ?></span>
        </div>

        <!-- Score Grid -->
        <div class="result-score-grid">
            <div class="result-metric">
                <div class="result-metric-label">Your Score</div>
                <div class="result-metric-value">
                    <?= htmlspecialchars(number_format($score, 2), ENT_QUOTES, 'UTF-8') ?>
                    <span class="result-metric-total">/ <?= htmlspecialchars(number_format($totalPoints, 2), ENT_QUOTES, 'UTF-8') ?></span>
                </div>
            </div>
            <div class="result-metric">
                <div class="result-metric-label">Percentage</div>
                <div class="result-metric-value result-pct"><?= $percentage ?>%</div>
            </div>
            <div class="result-metric">
                <div class="result-metric-label">Pass Mark</div>
                <div class="result-metric-value">50%</div>
            </div>
        </div>

        <!-- Details -->
        <div class="result-details">
            <div class="result-detail-row">
                <span class="result-detail-label">Student</span>
                <span class="result-detail-value"><?= $studentName ?></span>
            </div>
            <div class="result-detail-row">
                <span class="result-detail-label">Assessment</span>
                <span class="result-detail-value"><?= $quizTitle ?></span>
            </div>
            <div class="result-detail-row">
                <span class="result-detail-label">Submitted</span>
                <span class="result-detail-value"><?= $submittedAt ?></span>
            </div>
        </div>

        <!-- CTA -->
        <div style="text-align:center; margin-top:1.75rem;">
            <a href="/QuizMaster_Test/student/portal.php" class="btn-primary"
                style="display:inline-block; width:auto; padding:0.65rem 2rem; text-decoration:none;">
                &larr; Return to Student Portal
            </a>
        </div>

    </div>

</main>
</body>
</html>

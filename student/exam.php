<?php
// student/exam.php
// Active Exam Interface with MCQ Questions & Docked Calculator (QM-14, QM-15)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('student');

$studentId = (int) $_SESSION['user_id'];
$quizId    = (int) ($_GET['quiz_id'] ?? 0);

if ($quizId <= 0) {
    header('Location: /QuizMaster_Test/student/portal.php?error=invalid_quiz');
    exit;
}

// Verify quiz is active
$quizStmt = $pdo->prepare(
    'SELECT id, title, description, status FROM quizzes WHERE id = :id LIMIT 1'
);
$quizStmt->execute(['id' => $quizId]);
$quiz = $quizStmt->fetch();

if (!$quiz || $quiz['status'] !== 'active') {
    header('Location: /QuizMaster_Test/student/portal.php?error=quiz_closed');
    exit;
}

// Guard: prevent re-entry if already attempted
$attemptStmt = $pdo->prepare(
    'SELECT id FROM attempts WHERE student_id = :student_id AND quiz_id = :quiz_id LIMIT 1'
);
$attemptStmt->execute(['student_id' => $studentId, 'quiz_id' => $quizId]);
if ($attemptStmt->fetch()) {
    header('Location: /QuizMaster_Test/student/portal.php?error=already_attempted');
    exit;
}

// Fetch questions — correct_option intentionally NOT included in SELECT
$qStmt = $pdo->prepare(
    'SELECT id, question_text, option_a, option_b, option_c, option_d, points
     FROM questions WHERE quiz_id = :quiz_id ORDER BY id ASC'
);
$qStmt->execute(['quiz_id' => $quizId]);
$questions = $qStmt->fetchAll();

if (count($questions) === 0) {
    header('Location: /QuizMaster_Test/student/portal.php?error=empty_quiz');
    exit;
}

$quizTitle = htmlspecialchars($quiz['title'], ENT_QUOTES, 'UTF-8');
$totalQ    = count($questions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $quizTitle ?> — QuizMaster Exam</title>
    <link rel="stylesheet" href="/QuizMaster_Test/assets/css/style.css">
    <style>
        /* Prevent accidental navigation during exam */
        body { user-select: none; }
    </style>
</head>
<body>

<!-- Minimal Exam Topbar -->
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand-logo">
            <span class="brand-badge">QM</span>
            QuizMaster &mdash; Exam Mode
        </div>
        <div class="topbar-actions">
            <span class="user-greeting">
                <span class="role-tag">Exam</span>
                <?= htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
    </div>
</header>

<main class="container">

    <!-- Exam Header -->
    <div class="page-header" style="margin-bottom:1.25rem;">
        <div>
            <h1><?= $quizTitle ?></h1>
            <?php if (!empty($quiz['description'])): ?>
                <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">
                    <?= htmlspecialchars($quiz['description'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>
        </div>
        <div style="color:var(--text-muted); font-size:0.9rem; white-space:nowrap;">
            <?= $totalQ ?> question<?= $totalQ !== 1 ? 's' : '' ?>
        </div>
    </div>

    <!-- Exam Workspace: Questions | Calculator -->
    <form action="/QuizMaster_Test/actions/exam_action.php" method="POST"
        id="exam-form"
        onsubmit="return confirm('Submit this exam? You cannot change your answers after submission.');">
        <input type="hidden" name="action"  value="submit_exam">
        <input type="hidden" name="quiz_id" value="<?= $quizId ?>">

        <div class="exam-layout">

            <!-- Left: MCQ Questions -->
            <div class="exam-questions">
                <?php foreach ($questions as $index => $q): ?>
                    <?php $qId = (int) $q['id']; ?>
                    <div class="question-card">
                        <div class="question-card-header">
                            <span class="question-number">Question <?= $index + 1 ?> of <?= $totalQ ?></span>
                            <span style="color:var(--text-muted); font-size:0.8rem;">
                                <?= (int)$q['points'] ?> pt<?= (int)$q['points'] !== 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <p class="question-text" style="user-select:text;">
                            <?= htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8') ?>
                        </p>
                        <ul class="options-list" style="list-style:none; padding:0;">
                            <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label): ?>
                                <li class="option-item" style="margin-bottom:0.5rem;">
                                    <label class="exam-option-label">
                                        <input
                                            type="radio"
                                            name="answers[<?= $qId ?>]"
                                            value="<?= $label ?>"
                                            style="accent-color: var(--primary);"
                                        >
                                        <span class="option-label"><?= $label ?></span>
                                        <span style="user-select:text;">
                                            <?= htmlspecialchars($q['option_' . $key], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>

                <!-- Submit Button -->
                <div style="margin-top:1.5rem; padding-bottom:2rem;">
                    <button type="submit" class="btn-primary">
                        Submit Final Assessment
                    </button>
                </div>
            </div>

            <!-- Right: Docked Calculator -->
            <div class="exam-sidebar">
                <?php require_once __DIR__ . '/../includes/calculator.php'; ?>
            </div>

        </div>
    </form>

</main>

<script src="/QuizMaster_Test/assets/js/calculator.js"></script>
</body>
</html>

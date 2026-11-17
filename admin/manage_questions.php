<?php
// admin/manage_questions.php
// MCQ Question Bank Management Interface (QM-12)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('admin');

$adminId = (int) $_SESSION['user_id'];
$quizId  = (int) ($_GET['quiz_id'] ?? 0);

if ($quizId <= 0) {
    header('Location: /QuizMaster_Test/admin/dashboard.php?error=invalid_quiz');
    exit;
}

// Verify quiz belongs to this admin
$quizStmt = $pdo->prepare(
    'SELECT id, title, access_code, status FROM quizzes WHERE id = :id AND admin_id = :admin_id LIMIT 1'
);
$quizStmt->execute(['id' => $quizId, 'admin_id' => $adminId]);
$quiz = $quizStmt->fetch();

if (!$quiz) {
    header('Location: /QuizMaster_Test/admin/dashboard.php?error=not_found');
    exit;
}

// Fetch all questions for this quiz
$questionsStmt = $pdo->prepare(
    'SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option, points
     FROM questions WHERE quiz_id = :quiz_id ORDER BY id ASC'
);
$questionsStmt->execute(['quiz_id' => $quizId]);
$questions = $questionsStmt->fetchAll();

// Feedback resolution
$errorMessages = [
    'empty_fields'    => 'All question fields are required.',
    'invalid_option'  => 'Correct option must be A, B, C, or D.',
    'invalid_question'=> 'Invalid question identifier.',
    'not_found'       => 'Question not found.',
    'server_error'    => 'A server error occurred. Please try again.',
];
$successMessages = [
    'question_added'   => 'Question added successfully.',
    'question_deleted' => 'Question deleted successfully.',
];

$errorMsg   = $errorMessages[$_GET['error'] ?? '']   ?? '';
$successMsg = $successMessages[$_GET['success'] ?? ''] ?? '';

$quizTitle  = htmlspecialchars($quiz['title'],       ENT_QUOTES, 'UTF-8');
$accessCode = htmlspecialchars($quiz['access_code'], ENT_QUOTES, 'UTF-8');
$adminName  = htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8');

$optionLabels = ['A', 'B', 'C', 'D'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Question Bank — <?= $quizTitle ?> | QuizMaster</title>
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
            <a href="/QuizMaster_Test/admin/dashboard.php" class="btn-outline">← Dashboard</a>
            <a href="/QuizMaster_Test/admin/results.php" class="btn-outline">Results &amp; Analytics</a>
            <span class="user-greeting">
                <span class="role-tag">Instructor</span>
                <?= $adminName ?>
            </span>
            <a href="/QuizMaster_Test/actions/auth_action.php?action=logout" class="btn-outline">Sign Out</a>
        </div>
    </div>
</header>

<main class="container">

    <!-- Page Header with Quiz Info -->
    <div class="page-header">
        <div>
            <h1>Question Bank</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
                <?= $quizTitle ?>
                &nbsp;<span class="code-badge"><?= $accessCode ?></span>
                &nbsp;<span class="status-pill <?= $quiz['status'] === 'active' ? 'status-active' : 'status-closed' ?>">
                    <?= htmlspecialchars(ucfirst($quiz['status']), ENT_QUOTES, 'UTF-8') ?>
                </span>
            </p>
        </div>
        <div style="color: var(--text-muted); font-size: 0.9rem;">
            <?= count($questions) ?> question<?= count($questions) !== 1 ? 's' : '' ?>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($errorMsg !== ''): ?>
        <div class="alert alert-error" role="alert"><?= $errorMsg ?></div>
    <?php endif; ?>
    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success" role="alert"><?= $successMsg ?></div>
    <?php endif; ?>

    <div class="dashboard-grid">

        <!-- LEFT: Add Question Form -->
        <div class="card">
            <div class="card-header">
                <h2>Add New Question</h2>
            </div>

            <form action="/QuizMaster_Test/actions/question_action.php" method="POST" novalidate>
                <input type="hidden" name="action"  value="add_question">
                <input type="hidden" name="quiz_id" value="<?= $quizId ?>">

                <div class="form-group">
                    <label for="q-text">Question Text <span style="color:#DC2626">*</span></label>
                    <textarea id="q-text" name="question_text" class="form-control" rows="3"
                        placeholder="Enter the question…" required style="resize:vertical;"></textarea>
                </div>

                <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label): ?>
                <div class="form-group">
                    <label for="opt-<?= $key ?>">Option <?= $label ?> <span style="color:#DC2626">*</span></label>
                    <input type="text" id="opt-<?= $key ?>" name="option_<?= $key ?>"
                        class="form-control" placeholder="Option <?= $label ?>…" required>
                </div>
                <?php endforeach; ?>

                <div class="form-group">
                    <label for="correct-option">Correct Option <span style="color:#DC2626">*</span></label>
                    <select id="correct-option" name="correct_option" class="form-control" required>
                        <option value="" disabled selected>— Select correct answer —</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="points">Points</label>
                    <input type="number" id="points" name="points" class="form-control"
                        value="1" min="1" max="100">
                </div>

                <button type="submit" class="btn-primary" style="margin-top:0.5rem;">Add Question</button>
            </form>
        </div>

        <!-- RIGHT: Questions List -->
        <div class="card">
            <div class="card-header">
                <h2>Added Questions</h2>
                <span style="color: var(--text-muted); font-size: 0.85rem;"><?= count($questions) ?> total</span>
            </div>

            <?php if (count($questions) === 0): ?>
                <div class="empty-state">
                    <p>No questions yet. Use the form on the left to add MCQ questions.</p>
                </div>
            <?php else: ?>
                <?php foreach ($questions as $index => $q): ?>
                    <?php
                        $qText = htmlspecialchars($q['question_text'], ENT_QUOTES, 'UTF-8');
                        $qId   = (int) $q['id'];
                        $correctOption = $q['correct_option'];
                    ?>
                    <div class="question-card">
                        <div class="question-card-header">
                            <span class="question-number">Q<?= $index + 1 ?></span>
                            <span style="color:var(--text-muted); font-size:0.8rem;"><?= (int)$q['points'] ?> pt<?= (int)$q['points'] !== 1 ? 's' : '' ?></span>
                        </div>
                        <p class="question-text"><?= $qText ?></p>
                        <ul class="options-list">
                            <?php foreach (['a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D'] as $key => $label): ?>
                                <?php $isCorrect = ($label === $correctOption); ?>
                                <li class="option-item <?= $isCorrect ? 'option-correct' : '' ?>">
                                    <span class="option-label"><?= $label ?></span>
                                    <?= htmlspecialchars($q['option_' . $key], ENT_QUOTES, 'UTF-8') ?>
                                    <?php if ($isCorrect): ?>
                                        <span class="option-correct-badge">✓ Correct</span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form action="/QuizMaster_Test/actions/question_action.php" method="POST" style="margin-top:0.75rem;"
                            onsubmit="return confirm('Delete this question permanently?');">
                            <input type="hidden" name="action"      value="delete_question">
                            <input type="hidden" name="quiz_id"     value="<?= $quizId ?>">
                            <input type="hidden" name="question_id" value="<?= $qId ?>">
                            <button type="submit" class="btn-sm btn-danger-sm">Delete Question</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

</body>
</html>

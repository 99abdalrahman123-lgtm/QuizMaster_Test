<?php
// admin/results.php
// Instructor Results & Analytics Dashboard (QM-13)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('admin');

$adminId   = (int) $_SESSION['user_id'];
$adminName = htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8');

// Optional quiz filter
$filterQuizId = isset($_GET['quiz_id']) ? (int) $_GET['quiz_id'] : 0;

// Fetch all quizzes owned by this admin (for filter bar)
$quizListStmt = $pdo->prepare(
    'SELECT id, title FROM quizzes WHERE admin_id = :admin_id ORDER BY created_at DESC'
);
$quizListStmt->execute(['admin_id' => $adminId]);
$quizList = $quizListStmt->fetchAll();

// Build results query with optional quiz filter
if ($filterQuizId > 0) {
    $resultsStmt = $pdo->prepare(
        'SELECT a.id AS attempt_id, u.full_name, u.email,
                q.title AS quiz_title, a.score, a.total_points, a.submitted_at
         FROM attempts a
         JOIN users   u ON u.id = a.student_id
         JOIN quizzes q ON q.id = a.quiz_id
         WHERE q.admin_id = :admin_id AND a.quiz_id = :quiz_id
         ORDER BY a.submitted_at DESC'
    );
    $resultsStmt->execute(['admin_id' => $adminId, 'quiz_id' => $filterQuizId]);
} else {
    $resultsStmt = $pdo->prepare(
        'SELECT a.id AS attempt_id, u.full_name, u.email,
                q.title AS quiz_title, a.score, a.total_points, a.submitted_at
         FROM attempts a
         JOIN users   u ON u.id = a.student_id
         JOIN quizzes q ON q.id = a.quiz_id
         WHERE q.admin_id = :admin_id
         ORDER BY a.submitted_at DESC'
    );
    $resultsStmt->execute(['admin_id' => $adminId]);
}
$results = $resultsStmt->fetchAll();

// KPI Calculations
$totalSubmissions = count($results);
$avgPercent       = 0.0;
$passCount        = 0;

foreach ($results as $r) {
    $pct = $r['total_points'] > 0
        ? round((float)$r['score'] / (float)$r['total_points'] * 100, 1)
        : 0.0;
    $avgPercent += $pct;
    if ($pct >= 50) {
        $passCount++;
    }
}

$avgPercent = $totalSubmissions > 0 ? round($avgPercent / $totalSubmissions, 1) : 0.0;
$passRate   = $totalSubmissions > 0 ? round($passCount / $totalSubmissions * 100, 1) : 0.0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results &amp; Analytics — QuizMaster</title>
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
            <a href="/QuizMaster_Test/admin/dashboard.php" class="btn-outline">← Dashboard</a>
            <span class="user-greeting">
                <span class="role-tag">Instructor</span>
                <?= $adminName ?>
            </span>
            <a href="/QuizMaster_Test/actions/auth_action.php?action=logout" class="btn-outline">Sign Out</a>
        </div>
    </div>
</header>

<main class="container">

    <div class="page-header">
        <div>
            <h1>Results &amp; Analytics</h1>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-top:0.25rem;">
                Student performance across all your assessments.
            </p>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Submissions</div>
            <div class="stat-value"><?= $totalSubmissions ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Average Score</div>
            <div class="stat-value"><?= $avgPercent ?>%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Pass Rate (&ge;50%)</div>
            <div class="stat-value"><?= $passRate ?>%</div>
        </div>
    </div>

    <!-- Quiz Filter Bar -->
    <div class="card" style="margin-bottom:1.5rem; padding:1rem 1.5rem;">
        <form method="GET" action="/QuizMaster_Test/admin/results.php" style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
            <label for="quiz-filter" style="font-weight:600; font-size:0.875rem; white-space:nowrap;">Filter by Quiz:</label>
            <select id="quiz-filter" name="quiz_id" class="form-control" style="max-width:320px;">
                <option value="0" <?= $filterQuizId === 0 ? 'selected' : '' ?>>All Quizzes</option>
                <?php foreach ($quizList as $ql): ?>
                    <option value="<?= (int)$ql['id'] ?>" <?= $filterQuizId === (int)$ql['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ql['title'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-sm btn-primary-sm">Apply Filter</button>
            <?php if ($filterQuizId > 0): ?>
                <a href="/QuizMaster_Test/admin/results.php" class="btn-sm btn-secondary-sm">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Results Table -->
    <div class="card">
        <div class="card-header">
            <h2>Submission Log</h2>
            <span style="color:var(--text-muted); font-size:0.85rem;"><?= $totalSubmissions ?> record<?= $totalSubmissions !== 1 ? 's' : '' ?></span>
        </div>

        <?php if ($totalSubmissions === 0): ?>
            <div class="empty-state">
                <p>No submissions yet<?= $filterQuizId > 0 ? ' for this quiz' : '' ?>.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Quiz</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($results as $r): ?>
                        <?php
                            $pct    = $r['total_points'] > 0
                                        ? round((float)$r['score'] / (float)$r['total_points'] * 100, 1)
                                        : 0.0;
                            $passed = $pct >= 50;
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($r['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                                <div class="table-subtext"><?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?></div>
                            </td>
                            <td><?= htmlspecialchars($r['quiz_title'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="text-align:center;">
                                <?= htmlspecialchars($r['score'], ENT_QUOTES, 'UTF-8') ?>
                                / <?= htmlspecialchars($r['total_points'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="text-align:center;">
                                <span class="code-badge"><?= $pct ?>%</span>
                            </td>
                            <td>
                                <span class="status-pill <?= $passed ? 'status-active' : 'status-closed' ?>">
                                    <?= $passed ? 'Passed' : 'Failed' ?>
                                </span>
                            </td>
                            <td style="font-size:0.82rem; color:var(--text-muted);">
                                <?= htmlspecialchars($r['submitted_at'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</main>
</body>
</html>

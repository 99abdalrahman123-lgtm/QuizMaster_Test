<?php
// admin/dashboard.php
// Instructor Dashboard - Presentation Layer (QM-11)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('admin');

$adminId   = (int) $_SESSION['user_id'];
$adminName = htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8');

// -------------------------------------------------------------------------
// Data Retrieval - Business Logic Layer Queries
// -------------------------------------------------------------------------

// Fetch all quizzes owned by this admin with question count
$quizStmt = $pdo->prepare(
    'SELECT q.id, q.title, q.description, q.access_code, q.status, q.created_at,
            COUNT(qu.id) AS question_count
     FROM quizzes q
     LEFT JOIN questions qu ON qu.quiz_id = q.id
     WHERE q.admin_id = :admin_id
     GROUP BY q.id
     ORDER BY q.created_at DESC'
);
$quizStmt->execute(['admin_id' => $adminId]);
$quizzes = $quizStmt->fetchAll();

// KPI: total quizzes and active count derived from fetched data
$totalQuizzes      = count($quizzes);
$activeAssessments = 0;
foreach ($quizzes as $q) {
    if ($q['status'] === 'active') {
        $activeAssessments++;
    }
}

// KPI: total registered students
$studentStmt = $pdo->prepare(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'student'"
);
$studentStmt->execute();
$enrolledStudents = (int) $studentStmt->fetchColumn();

// -------------------------------------------------------------------------
// Feedback message resolution
// -------------------------------------------------------------------------
$errorParam   = $_GET['error']   ?? '';
$successParam = $_GET['success'] ?? '';
$codeParam    = htmlspecialchars($_GET['code'] ?? '', ENT_QUOTES, 'UTF-8');

$errorMessages = [
    'empty_title'   => 'Quiz title is required.',
    'invalid_quiz'  => 'Invalid quiz identifier.',
    'not_found'     => 'Quiz not found or access denied.',
    'server_error'  => 'A server error occurred. Please try again.',
];

$successMessages = [
    'quiz_created'    => 'Quiz created successfully!' . ($codeParam !== '' ? ' Access Code: <strong>' . $codeParam . '</strong>' : ''),
    'status_updated'  => 'Quiz status updated successfully.',
    'quiz_deleted'    => 'Quiz deleted successfully.',
];

$errorMsg   = $errorMessages[$errorParam]     ?? '';
$successMsg = $successMessages[$successParam] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instructor Dashboard — QuizMaster</title>
    <link rel="stylesheet" href="/QuizMaster_Test/assets/css/style.css">
</head>
<body>

<!-- ======================================================================
     TOP NAVIGATION BAR
     ====================================================================== -->
<header class="topbar">
    <div class="topbar-inner">
        <div class="brand-logo">
            <span class="brand-badge">QM</span>
            QuizMaster
        </div>
        <div class="topbar-actions">
            <a href="/QuizMaster_Test/admin/results.php" class="btn-outline">Results &amp; Analytics</a>
            <span class="user-greeting">
                <span class="role-tag">Instructor</span>
                <?= $adminName ?>
            </span>
            <a href="/QuizMaster_Test/actions/auth_action.php?action=logout" class="btn-outline">
                Sign Out
            </a>
        </div>
    </div>
</header>

<!-- ======================================================================
     MAIN CONTENT
     ====================================================================== -->
<main class="container">

    <!-- Page Header -->
    <div class="page-header">
        <div>
            <h1>Instructor Dashboard</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem;">
                Manage your assessments and monitor student activity.
            </p>
        </div>
    </div>

    <!-- Feedback Alerts -->
    <?php if ($errorMsg !== ''): ?>
        <div class="alert alert-error" role="alert">
            <?= $errorMsg ?>
        </div>
    <?php endif; ?>

    <?php if ($successMsg !== ''): ?>
        <div class="alert alert-success" role="alert">
            <?= $successMsg ?>
        </div>
    <?php endif; ?>

    <!-- ----------------------------------------------------------------
         KPI STAT CARDS
         ---------------------------------------------------------------- -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Quizzes</div>
            <div class="stat-value"><?= $totalQuizzes ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Assessments</div>
            <div class="stat-value"><?= $activeAssessments ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Enrolled Students</div>
            <div class="stat-value"><?= $enrolledStudents ?></div>
        </div>
    </div>

    <!-- ----------------------------------------------------------------
         TWO-COLUMN DASHBOARD GRID
         ---------------------------------------------------------------- -->
    <div class="dashboard-grid">

        <!-- LEFT: Create New Quiz Form -->
        <div class="card">
            <div class="card-header">
                <h2>Create New Quiz</h2>
            </div>

            <form action="/QuizMaster_Test/actions/quiz_action.php" method="POST" novalidate>
                <input type="hidden" name="action" value="create_quiz">

                <div class="form-group">
                    <label for="quiz-title">Quiz Title <span style="color:#DC2626">*</span></label>
                    <input
                        type="text"
                        id="quiz-title"
                        name="title"
                        class="form-control"
                        placeholder="e.g., Midterm Exam — Data Structures"
                        maxlength="200"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="quiz-description">Description <span style="color:var(--text-muted); font-weight:400">(optional)</span></label>
                    <textarea
                        id="quiz-description"
                        name="description"
                        class="form-control"
                        rows="3"
                        placeholder="Brief overview of this assessment…"
                        style="resize: vertical;"
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="quiz-status">Initial Status</label>
                    <select id="quiz-status" name="status" class="form-control">
                        <option value="active">Active — Students can join immediately</option>
                        <option value="closed">Closed — Hidden until activated</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 0.5rem;">
                    Create Quiz &amp; Generate Code
                </button>
            </form>
        </div>

        <!-- RIGHT: Quiz Table -->
        <div class="card">
            <div class="card-header">
                <h2>Your Quizzes</h2>
                <span style="color: var(--text-muted); font-size: 0.85rem;">
                    <?= $totalQuizzes ?> total
                </span>
            </div>

            <?php if ($totalQuizzes === 0): ?>
                <div class="empty-state">
                    <p>No quizzes yet. Create your first assessment on the left.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Quiz</th>
                                <th>Code</th>
                                <th>Qs</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($quizzes as $quiz): ?>
                            <?php
                                $quizTitle = htmlspecialchars($quiz['title'],       ENT_QUOTES, 'UTF-8');
                                $quizDesc  = htmlspecialchars($quiz['description'] ?? '', ENT_QUOTES, 'UTF-8');
                                $quizCode  = htmlspecialchars($quiz['access_code'], ENT_QUOTES, 'UTF-8');
                                $quizId    = (int) $quiz['id'];
                                $isActive  = $quiz['status'] === 'active';
                            ?>
                            <tr>
                                <td>
                                    <strong><?= $quizTitle ?></strong>
                                    <?php if ($quizDesc !== ''): ?>
                                        <div class="table-subtext"><?= $quizDesc ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="code-badge"><?= $quizCode ?></span>
                                </td>
                                <td style="text-align: center;">
                                    <?= (int) $quiz['question_count'] ?>
                                </td>
                                <td>
                                    <span class="status-pill <?= $isActive ? 'status-active' : 'status-closed' ?>">
                                        <?= $isActive ? 'Active' : 'Closed' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <!-- Questions link -->
                                        <a
                                            href="/QuizMaster_Test/admin/manage_questions.php?quiz_id=<?= $quizId ?>"
                                            class="btn-sm btn-secondary-sm"
                                        >Questions</a>

                                        <!-- Toggle status -->
                                        <form action="/QuizMaster_Test/actions/quiz_action.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="action"  value="toggle_status">
                                            <input type="hidden" name="quiz_id" value="<?= $quizId ?>">
                                            <button type="submit" class="btn-sm <?= $isActive ? 'btn-secondary-sm' : 'btn-primary-sm' ?>">
                                                <?= $isActive ? 'Close' : 'Activate' ?>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form
                                            action="/QuizMaster_Test/actions/quiz_action.php"
                                            method="POST"
                                            style="display:inline;"
                                            onsubmit="return confirm('Permanently delete &quot;<?= addslashes($quizTitle) ?>&quot;? This cannot be undone.');"
                                        >
                                            <input type="hidden" name="action"  value="delete_quiz">
                                            <input type="hidden" name="quiz_id" value="<?= $quizId ?>">
                                            <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div><!-- /.dashboard-grid -->
</main>

</body>
</html>

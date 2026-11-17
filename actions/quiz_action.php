<?php
// actions/quiz_action.php
// Quiz Management Business Logic (Create, Toggle Status, Delete)
// Business Logic Layer - QM-11

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('admin');

// Enforce POST-only access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuizMaster_Test/admin/dashboard.php');
    exit;
}

/**
 * Generates a cryptographically secure, database-unique access code.
 * Format: QM-XXXXXX (e.g., QM-3A7F2B)
 */
function generateUniqueAccessCode(PDO $pdo): string
{
    $stmt = $pdo->prepare('SELECT id FROM quizzes WHERE access_code = :code LIMIT 1');

    do {
        $code = 'QM-' . strtoupper(bin2hex(random_bytes(3)));
        $stmt->execute(['code' => $code]);
    } while ($stmt->fetch());

    return $code;
}

$action  = $_POST['action'] ?? '';
$adminId = (int) $_SESSION['user_id'];

// -------------------------------------------------------------------------
// Action: create_quiz
// -------------------------------------------------------------------------
if ($action === 'create_quiz') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    // Validate title
    if ($title === '') {
        header('Location: /QuizMaster_Test/admin/dashboard.php?error=empty_title');
        exit;
    }

    // Sanitize status to only accepted enum values
    if (!in_array($status, ['active', 'closed'], true)) {
        $status = 'active';
    }

    try {
        $accessCode = generateUniqueAccessCode($pdo);

        $stmt = $pdo->prepare(
            'INSERT INTO quizzes (admin_id, title, description, access_code, status)
             VALUES (:admin_id, :title, :description, :access_code, :status)'
        );
        $stmt->execute([
            'admin_id'    => $adminId,
            'title'       => $title,
            'description' => $description !== '' ? $description : null,
            'access_code' => $accessCode,
            'status'      => $status,
        ]);

        header(
            'Location: /QuizMaster_Test/admin/dashboard.php?success=quiz_created&code='
            . urlencode($accessCode)
        );
        exit;
    } catch (PDOException $e) {
        error_log('Create Quiz Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/admin/dashboard.php?error=server_error');
        exit;
    }
}

// -------------------------------------------------------------------------
// Action: toggle_status
// -------------------------------------------------------------------------
if ($action === 'toggle_status') {
    $quizId = (int) ($_POST['quiz_id'] ?? 0);

    if ($quizId <= 0) {
        header('Location: /QuizMaster_Test/admin/dashboard.php?error=invalid_quiz');
        exit;
    }

    try {
        // Fetch current status, scoped to this admin only
        $stmt = $pdo->prepare(
            'SELECT status FROM quizzes WHERE id = :id AND admin_id = :admin_id LIMIT 1'
        );
        $stmt->execute(['id' => $quizId, 'admin_id' => $adminId]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            header('Location: /QuizMaster_Test/admin/dashboard.php?error=not_found');
            exit;
        }

        $newStatus = $quiz['status'] === 'active' ? 'closed' : 'active';

        $updateStmt = $pdo->prepare(
            'UPDATE quizzes SET status = :status WHERE id = :id AND admin_id = :admin_id'
        );
        $updateStmt->execute([
            'status'   => $newStatus,
            'id'       => $quizId,
            'admin_id' => $adminId,
        ]);

        header('Location: /QuizMaster_Test/admin/dashboard.php?success=status_updated');
        exit;
    } catch (PDOException $e) {
        error_log('Toggle Status Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/admin/dashboard.php?error=server_error');
        exit;
    }
}

// -------------------------------------------------------------------------
// Action: delete_quiz
// -------------------------------------------------------------------------
if ($action === 'delete_quiz') {
    $quizId = (int) ($_POST['quiz_id'] ?? 0);

    if ($quizId <= 0) {
        header('Location: /QuizMaster_Test/admin/dashboard.php?error=invalid_quiz');
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'DELETE FROM quizzes WHERE id = :id AND admin_id = :admin_id'
        );
        $stmt->execute(['id' => $quizId, 'admin_id' => $adminId]);

        if ($stmt->rowCount() === 0) {
            header('Location: /QuizMaster_Test/admin/dashboard.php?error=not_found');
            exit;
        }

        header('Location: /QuizMaster_Test/admin/dashboard.php?success=quiz_deleted');
        exit;
    } catch (PDOException $e) {
        error_log('Delete Quiz Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/admin/dashboard.php?error=server_error');
        exit;
    }
}

// Fallback: unknown action
header('Location: /QuizMaster_Test/admin/dashboard.php');
exit;

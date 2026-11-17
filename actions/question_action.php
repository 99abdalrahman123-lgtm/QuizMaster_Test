<?php
// actions/question_action.php
// Question Bank Business Logic — Add & Delete MCQ Questions (QM-12)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuizMaster_Test/admin/dashboard.php');
    exit;
}

$action  = $_POST['action'] ?? '';
$adminId = (int) $_SESSION['user_id'];
$quizId  = (int) ($_POST['quiz_id'] ?? 0);

if ($quizId <= 0) {
    header('Location: /QuizMaster_Test/admin/dashboard.php?error=invalid_quiz');
    exit;
}

// Verify quiz ownership — prevents cross-admin tampering
$ownerStmt = $pdo->prepare(
    'SELECT id FROM quizzes WHERE id = :id AND admin_id = :admin_id LIMIT 1'
);
$ownerStmt->execute(['id' => $quizId, 'admin_id' => $adminId]);

if (!$ownerStmt->fetch()) {
    header('Location: /QuizMaster_Test/admin/dashboard.php?error=not_found');
    exit;
}

// -------------------------------------------------------------------------
// Action: add_question
// -------------------------------------------------------------------------
if ($action === 'add_question') {
    $questionText   = trim($_POST['question_text'] ?? '');
    $optionA        = trim($_POST['option_a'] ?? '');
    $optionB        = trim($_POST['option_b'] ?? '');
    $optionC        = trim($_POST['option_c'] ?? '');
    $optionD        = trim($_POST['option_d'] ?? '');
    $correctOption  = strtoupper(trim($_POST['correct_option'] ?? ''));
    $points         = max(1, (int) ($_POST['points'] ?? 1));

    if ($questionText === '' || $optionA === '' || $optionB === '' || $optionC === '' || $optionD === '') {
        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&error=empty_fields");
        exit;
    }

    if (!in_array($correctOption, ['A', 'B', 'C', 'D'], true)) {
        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&error=invalid_option");
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option, points)
             VALUES (:quiz_id, :question_text, :option_a, :option_b, :option_c, :option_d, :correct_option, :points)'
        );
        $stmt->execute([
            'quiz_id'        => $quizId,
            'question_text'  => $questionText,
            'option_a'       => $optionA,
            'option_b'       => $optionB,
            'option_c'       => $optionC,
            'option_d'       => $optionD,
            'correct_option' => $correctOption,
            'points'         => $points,
        ]);

        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&success=question_added");
        exit;
    } catch (PDOException $e) {
        error_log('Add Question Error: ' . $e->getMessage());
        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&error=server_error");
        exit;
    }
}

// -------------------------------------------------------------------------
// Action: delete_question
// -------------------------------------------------------------------------
if ($action === 'delete_question') {
    $questionId = (int) ($_POST['question_id'] ?? 0);

    if ($questionId <= 0) {
        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&error=invalid_question");
        exit;
    }

    try {
        // Delete only if the question belongs to this admin's quiz (via FK)
        $stmt = $pdo->prepare(
            'DELETE FROM questions WHERE id = :id AND quiz_id = :quiz_id'
        );
        $stmt->execute(['id' => $questionId, 'quiz_id' => $quizId]);

        if ($stmt->rowCount() === 0) {
            header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&error=not_found");
            exit;
        }

        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&success=question_deleted");
        exit;
    } catch (PDOException $e) {
        error_log('Delete Question Error: ' . $e->getMessage());
        header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}&error=server_error");
        exit;
    }
}

// Fallback
header("Location: /QuizMaster_Test/admin/manage_questions.php?quiz_id={$quizId}");
exit;

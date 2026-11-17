<?php
// actions/exam_action.php
// Exam Join & Submission Business Logic — Server-Side Grading (QM-14, QM-15)

declare(strict_types=1);

require_once __DIR__ . '/../includes/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

requireAuth('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /QuizMaster_Test/student/portal.php');
    exit;
}

$action    = $_POST['action'] ?? '';
$studentId = (int) $_SESSION['user_id'];

// -------------------------------------------------------------------------
// Action: join_quiz
// -------------------------------------------------------------------------
if ($action === 'join_quiz') {
    $accessCode = strtoupper(trim($_POST['access_code'] ?? ''));

    if ($accessCode === '') {
        header('Location: /QuizMaster_Test/student/portal.php?error=invalid_code');
        exit;
    }

    try {
        // Look up quiz by access code
        $stmt = $pdo->prepare(
            'SELECT id, status FROM quizzes WHERE access_code = :code LIMIT 1'
        );
        $stmt->execute(['code' => $accessCode]);
        $quiz = $stmt->fetch();

        if (!$quiz) {
            header('Location: /QuizMaster_Test/student/portal.php?error=invalid_code');
            exit;
        }

        if ($quiz['status'] !== 'active') {
            header('Location: /QuizMaster_Test/student/portal.php?error=quiz_closed');
            exit;
        }

        $quizId = (int) $quiz['id'];

        // Verify quiz has at least one question
        $countStmt = $pdo->prepare(
            'SELECT COUNT(*) FROM questions WHERE quiz_id = :quiz_id'
        );
        $countStmt->execute(['quiz_id' => $quizId]);
        $questionCount = (int) $countStmt->fetchColumn();

        if ($questionCount === 0) {
            header('Location: /QuizMaster_Test/student/portal.php?error=empty_quiz');
            exit;
        }

        // Check for existing attempt (UNIQUE KEY enforces this at DB level too)
        $attemptStmt = $pdo->prepare(
            'SELECT id FROM attempts WHERE student_id = :student_id AND quiz_id = :quiz_id LIMIT 1'
        );
        $attemptStmt->execute(['student_id' => $studentId, 'quiz_id' => $quizId]);

        if ($attemptStmt->fetch()) {
            header('Location: /QuizMaster_Test/student/portal.php?error=already_attempted');
            exit;
        }

        header("Location: /QuizMaster_Test/student/exam.php?quiz_id={$quizId}");
        exit;
    } catch (PDOException $e) {
        error_log('Join Quiz Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/student/portal.php?error=server_error');
        exit;
    }
}

// -------------------------------------------------------------------------
// Action: submit_exam — Server-Side Auto-Grading
// -------------------------------------------------------------------------
if ($action === 'submit_exam') {
    $quizId  = (int) ($_POST['quiz_id'] ?? 0);
    $answers = $_POST['answers'] ?? [];

    if ($quizId <= 0) {
        header('Location: /QuizMaster_Test/student/portal.php?error=invalid_quiz');
        exit;
    }

    try {
        // Verify quiz is still active
        $quizStmt = $pdo->prepare(
            'SELECT id, status FROM quizzes WHERE id = :id LIMIT 1'
        );
        $quizStmt->execute(['id' => $quizId]);
        $quiz = $quizStmt->fetch();

        if (!$quiz || $quiz['status'] !== 'active') {
            header('Location: /QuizMaster_Test/student/portal.php?error=quiz_closed');
            exit;
        }

        // Guard: prevent duplicate submission (race condition / double submit)
        $dupStmt = $pdo->prepare(
            'SELECT id FROM attempts WHERE student_id = :student_id AND quiz_id = :quiz_id LIMIT 1'
        );
        $dupStmt->execute(['student_id' => $studentId, 'quiz_id' => $quizId]);
        if ($dupStmt->fetch()) {
            header('Location: /QuizMaster_Test/student/portal.php?error=already_attempted');
            exit;
        }

        // Fetch all questions with correct_option and points (server-side only)
        $qStmt = $pdo->prepare(
            'SELECT id, correct_option, points FROM questions WHERE quiz_id = :quiz_id'
        );
        $qStmt->execute(['quiz_id' => $quizId]);
        $questions = $qStmt->fetchAll();

        // Server-side grading
        $earnedScore  = 0.0;
        $totalPoints  = 0.0;

        foreach ($questions as $question) {
            $qid   = (int) $question['id'];
            $pts   = (float) $question['points'];
            $totalPoints += $pts;

            $submitted = strtoupper(trim((string) ($answers[$qid] ?? '')));
            if ($submitted === $question['correct_option']) {
                $earnedScore += $pts;
            }
        }

        // Insert attempt record
        $insertStmt = $pdo->prepare(
            'INSERT INTO attempts (student_id, quiz_id, score, total_points)
             VALUES (:student_id, :quiz_id, :score, :total_points)'
        );
        $insertStmt->execute([
            'student_id'   => $studentId,
            'quiz_id'      => $quizId,
            'score'        => $earnedScore,
            'total_points' => $totalPoints,
        ]);

        $attemptId = (int) $pdo->lastInsertId();
        header("Location: /QuizMaster_Test/student/result.php?attempt_id={$attemptId}");
        exit;
    } catch (PDOException $e) {
        error_log('Submit Exam Error: ' . $e->getMessage());
        header('Location: /QuizMaster_Test/student/portal.php?error=server_error');
        exit;
    }
}

// Fallback
header('Location: /QuizMaster_Test/student/portal.php');
exit;

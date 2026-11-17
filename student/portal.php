<?php
require_once __DIR__ . '/../includes/auth_guard.php';
requireAuth('student');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuizMaster | Student Portal</title>
    <link rel="stylesheet" href="/QuizMaster_Test/assets/css/style.css">
</head>
<body style="padding: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>Student Portal - Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
        <a href="/QuizMaster_Test/actions/auth_action.php?action=logout" class="btn-primary" style="width: auto; text-decoration: none;">Sign Out</a>
    </div>

    <?php include __DIR__ . '/../includes/calculator.php'; ?>

    <script src="/QuizMaster_Test/assets/js/calculator.js"></script>
</body>
</html>
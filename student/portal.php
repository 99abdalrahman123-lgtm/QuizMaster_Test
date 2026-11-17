<?php
require_once __DIR__ . '/../includes/auth_guard.php';
requireAuth('student');
?>
<h1>Student Portal - Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></h1>
<a href="/QuizMaster_Test/actions/auth_action.php?action=logout">Sign Out</a>
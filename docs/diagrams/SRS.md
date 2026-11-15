# Software Requirements Specification (SRS)
## Project: QuizMaster — Automated Assessment & Analytics System
**Version:** 1.0  
**Author:** Abdalrahman  
**Jira Issue:** QM-5  
**Date:** November 2026  

---

## 1. Introduction

### 1.1 Purpose
This document defines the complete functional and non-functional requirements for **QuizMaster**, a web-based automated quiz and survey management platform. It acts as the baseline technical specification for system modeling, database design, implementation, and quality assurance testing.

### 1.2 Scope
QuizMaster delivers a dual-role web application:
- **Administrators (Instructors):** Create assessments with unique access codes, author multiple-choice questions (MCQ), control quiz availability (`active` / `closed`), and view statistical performance analytics.
- **Students:** Register and log in, join active quizzes via access codes, utilize a persistent built-in calculator during exams, submit responses, and receive instant server-graded scores.
- **Out of Scope (v1.0):** Essay/open-ended manual grading, live video proctoring, and external LMS integrations.

### 1.3 Definitions & Acronyms
- **RBAC:** Role-Based Access Control (`admin` vs. `student`).
- **MCQ:** Multiple-Choice Question (4 options, 1 valid answer).
- **PDO:** PHP Data Objects (secure database abstraction layer).
- **SRS:** Software Requirements Specification.

---

## 2. Overall Description

### 2.1 User Roles & Characteristics
1. **Administrator (`admin`):** Authenticated instructor responsible for creating quizzes, managing question banks, and monitoring student results.
2. **Student (`student`):** Authenticated examinee who accesses quizzes using a valid code and views personal evaluation results.

### 2.2 Operating Environment
- **Client-Side:** Modern web browsers (Chrome, Edge, Firefox) supporting HTML5, CSS3, and ES6+ JavaScript.
- **Server-Side:** Apache Web Server (XAMPP) running PHP 8.0+.
- **Database:** MySQL 8.0+ / MariaDB.

### 2.3 Design & Implementation Constraints
- All answer validation and score calculations must execute strictly on the server (PHP) to prevent client-side tampering.
- All database interactions must utilize PDO Prepared Statements to eliminate SQL Injection vulnerabilities.
- User passwords must be hashed using PHP's `password_hash()` (`PASSWORD_DEFAULT`).

---

## 3. Functional Requirements (FR)

### Module 1: Authentication & Authorization
- **FR-01 (User Registration):** The system shall allow new users to create an account using a full name, unique email address, password, and role selection (`admin` or `student`).
- **FR-02 (User Login):** The system shall verify user credentials and initialize an authenticated PHP Session (`$_SESSION`).
- **FR-03 (Role-Based Routing & Guard):** The system shall redirect `admin` users to `/admin/dashboard.php` and `student` users to `/student/dashboard.php`, denying unauthorized cross-role access.
- **FR-04 (Logout):** The system shall destroy active session data and redirect users to the login page upon logout.

### Module 2: Built-in Web Calculator
- **FR-05 (Persistent Calculator Widget):** The system shall render an interactive JavaScript calculator accessible across interfaces without refreshing the page or triggering form submissions.
- **FR-06 (Arithmetic Operations):** The calculator shall execute addition (`+`), subtraction (`-`), multiplication (`*`), division (`/`), decimal input, clear (`C`), and backspace, handling division by zero safely.

### Module 3: Admin Quiz & Question Management
- **FR-07 (Create Quiz):** Administrators shall create quizzes by specifying a title, description, and a unique alphanumeric access code.
- **FR-08 (Quiz Status Management):** Administrators shall toggle quiz availability between `active` and `closed`.
- **FR-09 (Add MCQ Questions):** Administrators shall add multiple-choice questions to any owned quiz, defining question text, four options (`A`, `B`, `C`, `D`), the correct option key, and point weight.
- **FR-10 (Results & Analytics Dashboard):** Administrators shall view aggregate quiz metrics (total attempts, average score, highest score, pass rate) and a breakdown of individual student scores.

### Module 4: Student Exam & Auto-Grading
- **FR-11 (Join Quiz via Code):** Students shall submit a quiz code to verify that the quiz exists and is currently `active`.
- **FR-12 (Single-Attempt Enforcement):** The system shall prevent a student from submitting the same quiz more than once.
- **FR-13 (Secure Exam Rendering):** The system shall display all questions for the active quiz without exposing correct answer keys in the HTML DOM or JavaScript variables.
- **FR-14 (Server-Side Auto-Grading):** Upon form submission, the PHP backend shall evaluate student answers against stored keys, compute the total score, persist the attempt in MySQL, and display instant results.

---

## 4. Non-Functional Requirements (NFR)
- **NFR-01 (Security):** Zero exposure of answer keys on the client side; full mitigation against SQL Injection (PDO Prepared Statements) and Cross-Site Scripting / XSS (`htmlspecialchars()`).
- **NFR-02 (Performance):** Page rendering and auto-grading transactions must execute within `< 1.5 seconds` under standard local server load.
- **NFR-03 (Usability):** Responsive, intuitive interface supporting desktop and mobile viewports with clear error and success feedback.
- **NFR-04 (Maintainability):** Modular directory architecture separating presentation views (`/admin`, `/student`), shared UI partials (`/includes`), and backend controllers (`/actions`).
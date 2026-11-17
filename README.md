# QuizMaster — Automated Assessment & Analytics Platform

> **Stack:** PHP 8.x · MySQL 8.0 · PDO · Vanilla JS · CSS3  
> **Architecture:** Strict 3-Tier (Presentation / Business Logic / Data Access)  
> **Project Management:** Agile/Scrum · Jira (`QM-7` → `QM-16`)

---

## Overview

QuizMaster is a full-stack web application for academic institutions that enables instructors to create multiple-choice assessments with unique access codes, and students to join, sit, and receive instant auto-graded results — all without exposing correct answers to the client.

---

## Features

| Module | Description |
|---|---|
| **Authentication (QM-9/10)** | Tabbed Login/Register with bcrypt hashing, session fixation protection, RBAC redirect |
| **Quiz Management (QM-11)** | Create quizzes with cryptographically unique `QM-XXXXXX` access codes; toggle Active/Closed; delete |
| **Question Bank (QM-12)** | Add/delete 4-option MCQ questions with configurable point values; correct answer highlighted visually for instructor |
| **Results & Analytics (QM-13)** | Instructor dashboard: total submissions, average score %, pass rate; filterable per-quiz results table |
| **Student Portal (QM-14)** | Join assessment by access code with full validation (closed/empty/duplicate guards); attempt history table |
| **Exam Interface (QM-14/15)** | 2-column workspace: numbered MCQ cards + docked scientific calculator; correct answers never sent to browser |
| **Auto-Grading (QM-15)** | 100% server-side: PHP fetches `correct_option` and computes `score / total_points`; result stored in `attempts` |
| **Result Page (QM-15)** | Score summary hero card with Pass/Fail badge, percentage, submission timestamp |
| **Scientific Calculator** | Docked throughout exam: standard + scientific (√, x², %) operations, non-interfering with form submission |

---

## 3-Tier Architecture

```
┌─────────────────────────────────────────────────────┐
│          PRESENTATION LAYER (Tier 1)                │
│  admin/dashboard.php   admin/manage_questions.php   │
│  admin/results.php     student/portal.php           │
│  student/exam.php      student/result.php           │
│  index.php             includes/calculator.php      │
└───────────────┬─────────────────────────────────────┘
                │ POST/redirect
┌───────────────▼─────────────────────────────────────┐
│          BUSINESS LOGIC LAYER (Tier 2)              │
│  actions/auth_action.php    actions/quiz_action.php │
│  actions/question_action.php  actions/exam_action.php│
└───────────────┬─────────────────────────────────────┘
                │ PDO prepared statements
┌───────────────▼─────────────────────────────────────┐
│          DATA ACCESS LAYER (Tier 3)                 │
│  config/db.php  →  MySQL quizmaster_db              │
└─────────────────────────────────────────────────────┘
```

---

## Database Schema

```sql
users        (id PK, full_name, email UNIQUE, password_hash, role ENUM, created_at)
quizzes      (id PK, admin_id FK, title, description, access_code UNIQUE, status ENUM, created_at)
questions    (id PK, quiz_id FK, question_text, option_a/b/c/d, correct_option ENUM(A-D), points)
attempts     (id PK, student_id FK, quiz_id FK, score DECIMAL, total_points DECIMAL, submitted_at)
              └── UNIQUE(student_id, quiz_id)  — enforces one attempt per student per quiz
```

---

## Local XAMPP Setup

### Prerequisites
- XAMPP ≥ 8.0 (PHP 8.x + Apache + MySQL)
- Composer (optional, no external dependencies)

### Steps

```bash
# 1. Place project in XAMPP web root
cp -r QuizMaster_Test/ C:/xampp/htdocs/

# 2. Start Apache & MySQL in XAMPP Control Panel

# 3. Import database schema
#    Open phpMyAdmin → http://localhost/phpmyadmin
#    Create database: quizmaster_db
#    Import: docs/database.sql

# 4. Open in browser
http://localhost/QuizMaster_Test/
```

### Configuration

Edit `config/db.php` if your MySQL credentials differ from the defaults:

```php
$host = '127.0.0.1';
$db   = 'quizmaster_db';
$user = 'root';
$pass = '';          // Change if you set a MySQL root password
```

---

## Default Credentials (Seed Data)

| Role | Email | Password |
|---|---|---|
| **Admin / Instructor** | `admin@quizmaster.local` | `password` |
| **Student** | `student@quizmaster.local` | `password` |

> ⚠️ Change these credentials before any production or shared deployment.

---

## Project Structure

```
QuizMaster_Test/
├── actions/                   # Business Logic Layer (POST handlers)
│   ├── auth_action.php        # Login, Register, Logout
│   ├── quiz_action.php        # Create, Toggle, Delete quiz
│   ├── question_action.php    # Add, Delete question
│   └── exam_action.php        # Join quiz, Submit & auto-grade exam
├── admin/                     # Admin Presentation Layer
│   ├── dashboard.php          # Quiz management + KPI stats
│   ├── manage_questions.php   # MCQ question bank editor
│   └── results.php            # Student analytics dashboard
├── student/                   # Student Presentation Layer
│   ├── portal.php             # Join assessment + attempt history
│   ├── exam.php               # Exam interface + docked calculator
│   └── result.php             # Score summary & pass/fail result
├── includes/                  # Reusable server-side components
│   ├── auth_guard.php         # Session management & RBAC
│   └── calculator.php         # Docked calculator HTML component
├── assets/
│   ├── css/style.css          # Academic Precision design system
│   └── js/calculator.js       # Calculator interaction logic
├── config/
│   └── db.php                 # PDO database connection (Data Access Layer)
├── docs/
│   ├── database.sql           # Full schema + seed data
│   ├── SRS.md                 # Software Requirements Specification
│   ├── TEST_PLAN.md           # QA Test Plan & Verification Matrix
│   └── diagrams/              # ERD, DFD, Use Case, Architecture diagrams
├── index.php                  # Entry point (Auth page)
└── README.md                  # This file
```

---

## Security Implementation

| Threat | Mitigation |
|---|---|
| SQL Injection | PDO prepared statements (`ATTR_EMULATE_PREPARES => false`) throughout |
| XSS | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` on every dynamic output |
| Session Fixation | `session_regenerate_id(true)` after successful login |
| CSRF (basic) | POST-only action handlers; GET requests redirect away |
| Answer Exposure | `correct_option` never included in `SELECT` for exam page |
| Duplicate Attempts | `UNIQUE(student_id, quiz_id)` DB constraint + PHP pre-check |
| Cross-Admin Access | All quiz/question operations filter by `admin_id = session user_id` |
| RBAC | `requireAuth('admin')` / `requireAuth('student')` on every protected page |

---

## Testing

See [`docs/TEST_PLAN.md`](docs/TEST_PLAN.md) for the full QA Test Plan & Verification Matrix.

---

## Tech Stack

- **Back-End:** PHP 8.x with `declare(strict_types=1)`, PDO
- **Database:** MySQL 8.0 / MariaDB (`utf8mb4_unicode_ci`)
- **Front-End:** HTML5, Vanilla CSS3 (no frameworks), Vanilla JS ES6+
- **Architecture:** 3-Tier, PRG (Post-Redirect-Get) pattern
- **Dev Tools:** XAMPP, phpMyAdmin, VS Code
- **Project Mgmt:** Agile/Scrum, Jira (`QM-7` through `QM-16`)

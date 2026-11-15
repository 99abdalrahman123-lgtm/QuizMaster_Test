# QuizMaster — Automated Assessment & Analytics System

A web-based automated quiz and survey management system built with PHP, MySQL, JavaScript, HTML5, and CSS3. Designed and developed following formal Software Engineering, Systems Analysis & Design, and Agile/Scrum workflows.

---

## Key Features

- **Role-Based Access Control (RBAC):** Separate portals and permissions for Administrators (Instructors) and Students.
- **Quiz Management (Admin):** Create quizzes with unique access codes, toggle quiz availability (`active` / `closed`), and manage multiple-choice questions (MCQ).
- **Secure Server-Side Auto-Grading:** Student submissions are validated and graded exclusively on the PHP backend to prevent client-side tampering or answer exposure.
- **Analytics & Results Dashboard:** Real-time performance metrics including total participants, average score, highest score, pass rate, and individual student breakdowns.
- **Fixed Built-in Web Calculator:** An interactive JavaScript calculator embedded across the interface for instant calculations without page reloads or form interference.

---

## Tech Stack

- **Front-End:** HTML5, CSS3, Vanilla JavaScript (ES6+)
- **Back-End:** PHP 8+ (PDO with Prepared Statements)
- **Database:** MySQL
- **Project Management:** Agile / Scrum (Jira)
- **Version Control & Modeling:** Git, GitHub, Draw.io / Mermaid (UML, DFD, ERD)

---

## Project Structure

```text
QuizMaster_Test/
├── docs/                  # System documentation (SRS, Diagrams, ERD, Test Cases)
│   └── diagrams/          # Visual architecture and analysis diagrams
├── assets/                # Static front-end resources
│   ├── css/               # Stylesheets
│   └── js/                # Client-side scripts (calculator, validation)
├── config/                # Database connection settings (PDO)
├── includes/              # Reusable UI components (header, footer, calculator)
├── admin/                 # Administrator dashboard and quiz management views
├── student/               # Student dashboard, exam interface, and results views
├── actions/               # Pure PHP backend form handlers and business logic
├── index.php              # Entry point (Authentication: Login & Registration)
├── .gitignore             # Git exclusion rules
└── README.md              # Project overview and setup instructions
# QuizMaster — QA Test Plan & Verification Matrix

**Project:** QuizMaster Automated Assessment Platform  
**Version:** 1.0  
**Scope:** Functional, Security, and Integration Testing

---

## 1. Authentication & Session Management

| ID | Test Case | Steps | Expected Result | Pass Criteria |
|---|---|---|---|---|
| AUTH-01 | Valid admin login | Submit correct admin credentials | Redirect to `/admin/dashboard.php` | 302 to dashboard |
| AUTH-02 | Valid student login | Submit correct student credentials | Redirect to `/student/portal.php` | 302 to portal |
| AUTH-03 | Invalid credentials | Submit wrong password | Redirect with `?error=invalid_credentials` | No session created |
| AUTH-04 | Empty login fields | Submit empty form | Redirect with `?error=empty_fields` | No session created |
| AUTH-05 | Student register | Submit valid registration form | Redirect with `?success=registered` | User row created with hashed password |
| AUTH-06 | Duplicate email register | Register with existing email | `?error=email_exists` | No duplicate row |
| AUTH-07 | Logout | Click Sign Out | Session destroyed, redirect to index | `$_SESSION` cleared |
| AUTH-08 | Session fixation | Check session ID before/after login | New session ID after `session_regenerate_id()` | Different IDs |

---

## 2. Role-Based Access Control (RBAC)

| ID | Test Case | Steps | Expected Result |
|---|---|---|---|
| RBAC-01 | Student accesses admin dashboard | Navigate to `/admin/dashboard.php` as student | Redirect to `/student/portal.php` |
| RBAC-02 | Admin accesses student portal | Navigate to `/student/portal.php` as admin | Redirect to `/admin/dashboard.php` |
| RBAC-03 | Unauthenticated access | Navigate to any protected page without session | Redirect to `/index.php?error=unauthorized` |
| RBAC-04 | Direct POST to quiz_action.php as student | POST with action=create_quiz as student | Redirect away (requireAuth fails) |
| RBAC-05 | Cross-admin quiz manipulation | Admin B posts toggle_status for Admin A's quiz | `?error=not_found` — ownership check blocks |

---

## 3. SQL Injection & XSS Security

| ID | Test Case | Input | Expected Result |
|---|---|---|---|
| SEC-01 | SQLi in login email | `' OR '1'='1` | No authentication bypass; PDO parameterization prevents it |
| SEC-02 | SQLi in access code | `' OR 1=1 --` | No data leaked; parameterized query |
| SEC-03 | XSS in quiz title | `<script>alert(1)</script>` | Rendered as `&lt;script&gt;...` via `htmlspecialchars` |
| SEC-04 | XSS in question text | `"><img src=x onerror=alert(1)>` | Escaped safely in HTML output |
| SEC-05 | XSS in student name | `<b>Bold Name</b>` | Rendered as literal text, not HTML |
| SEC-06 | Correct answer exposure | View exam page source | `correct_option` absent from HTML/JS entirely |

---

## 4. Quiz Management (QM-11)

| ID | Test Case | Expected Result |
|---|---|---|
| QM11-01 | Create quiz with valid title | Quiz row inserted; redirect with `?success=quiz_created&code=QM-XXXXXX` |
| QM11-02 | Create quiz with empty title | `?error=empty_title` — no row inserted |
| QM11-03 | Access code uniqueness | Each quiz creation generates a distinct `QM-XXXXXX` code |
| QM11-04 | Toggle active → closed | `status` field updated to `closed` |
| QM11-05 | Toggle closed → active | `status` field updated to `active` |
| QM11-06 | Delete quiz | Row removed from `quizzes`; cascades to `questions` and `attempts` |
| QM11-07 | Delete another admin's quiz | `?error=not_found` — ownership check enforced |

---

## 5. Question Bank (QM-12)

| ID | Test Case | Expected Result |
|---|---|---|
| QM12-01 | Add valid 4-option MCQ | Row inserted into `questions`; redirect with `?success=question_added` |
| QM12-02 | Add question with blank fields | `?error=empty_fields` |
| QM12-03 | Add question with invalid correct_option | `?error=invalid_option` (must be A/B/C/D) |
| QM12-04 | Correct option highlighted | In manage_questions.php, correct answer has `.option-correct` class |
| QM12-05 | Delete question | Row removed; redirect with `?success=question_deleted` |
| QM12-06 | Add question to another admin's quiz | `?error=not_found` — ownership verified via quiz join |
| QM12-07 | Question count on dashboard | `LEFT JOIN COUNT` reflects current question total per quiz |

---

## 6. Exam Execution & Auto-Grading (QM-14, QM-15)

| ID | Test Case | Expected Result |
|---|---|---|
| QM14-01 | Join quiz with valid active code | Redirect to `exam.php?quiz_id=N` |
| QM14-02 | Join with invalid code | `?error=invalid_code` |
| QM14-03 | Join closed quiz | `?error=quiz_closed` |
| QM14-04 | Join quiz with 0 questions | `?error=empty_quiz` |
| QM14-05 | Re-join after attempt | `?error=already_attempted` |
| QM14-06 | Correct answers absent from HTML | Inspect page source of exam.php — no `correct_option` values present |
| QM15-01 | Submit all correct answers | `score = total_points`; Passed on result page |
| QM15-02 | Submit all wrong answers | `score = 0`; Failed on result page |
| QM15-03 | Submit partial answers | Score = sum of matching `points` values |
| QM15-04 | Submit blank (unanswered) questions | Blanks counted as wrong; score unchanged |
| QM15-05 | Duplicate submission attempt | UNIQUE constraint + PHP guard prevents second INSERT |
| QM15-06 | Result page loads own attempt | `attempt.student_id = session user_id` verified |
| QM15-07 | Student views another's result | Redirect to portal (ownership check fails) |
| QM15-08 | Pass threshold | Score ≥ 50% of `total_points` = Passed |

---

## 7. Calculator (QM-14)

| ID | Test Case | Input | Expected Result |
|---|---|---|---|
| CALC-01 | Basic arithmetic | 7 + 3 = | 10 |
| CALC-02 | Division | 10 ÷ 4 = | 2.5 |
| CALC-03 | Division by zero | 5 ÷ 0 = | Error / Infinity handled gracefully |
| CALC-04 | Square root | √9 | 3 |
| CALC-05 | Square | 5 x² | 25 |
| CALC-06 | Percentage | 200 % | 2 |
| CALC-07 | All Clear | AC | Display resets to 0 |
| CALC-08 | Form independence | Use calculator, then submit exam | Exam answers unaffected by calculator state |

---

## 8. Analytics Dashboard (QM-13)

| ID | Test Case | Expected Result |
|---|---|---|
| QM13-01 | Load results page | All attempts for admin's quizzes listed |
| QM13-02 | Filter by quiz | Only attempts for selected quiz shown |
| QM13-03 | Clear filter | All attempts restored |
| QM13-04 | Average Score KPI | Correct arithmetic across all filtered results |
| QM13-05 | Pass Rate KPI | Correct count of attempts ≥ 50% |
| QM13-06 | Admin sees only own quiz results | JOIN on `quizzes.admin_id` prevents cross-admin data leakage |

---

## Verification Checklist

- [ ] All PHP files pass `php -l` syntax check with no errors
- [ ] No Arabic comments anywhere in the codebase
- [ ] No `correct_option` exposed in exam page HTML source
- [ ] All dynamic outputs wrapped in `htmlspecialchars()`
- [ ] All DB queries use PDO prepared statements
- [ ] `ATTR_EMULATE_PREPARES => false` confirmed in `config/db.php`
- [ ] `session_regenerate_id(true)` called on login
- [ ] All action files enforce POST-only access
- [ ] All admin pages enforce `requireAuth('admin')`
- [ ] All student pages enforce `requireAuth('student')`
- [ ] `UNIQUE(student_id, quiz_id)` prevents duplicate attempts at DB level

# System Analysis & Modeling Diagrams — QuizMaster
**Project:** QuizMaster — Automated Assessment & Analytics System  
**Jira Issue:** QM-6  
**Epic:** Epic 1 — System Analysis, Architecture & Database Design  
**Author:** Abdalrahman  

---

## 1. UML Use Case Diagram
Illustrates the system boundary, actors (`Administrator` and `Student`), functional use cases, and `<<include>>` / `<<extend>>` dependencies.

![UML Use Case Diagram](./use-case-diagram.png)

---

## 2. Level 0 Data Flow Diagram (Context Diagram)
Models the entire system as a single central process (`Process 0`), defining external entities and high-level input/output data flows.

![Level 0 Data Flow Diagram](./level-0-dfd.png)

---

## 3. Level 1 Data Flow Diagram (Process Decomposition)
Decomposes `Process 0` into four core functional processes (`1.0` to `4.0`) interacting with four persistent data stores (`D1` to `D4`).

![Level 1 Data Flow Diagram](./lev![alt text](./level-1-dfd.png)el-1-dfd.png)

---

## 4. Level 2 Data Flow Diagram (Process 3.0 Decomposition)
Provides a granular breakdown of `Process 3.0 (Deliver Exam & Auto-Grade)` into sub-processes `3.1` through `3.4` to enforce single-attempt validation and server-side grading.

![Level 2 Data Flow Diagram](./level-2-dfd.png)

## 5. MySQL Entity-Relationship Diagram (ERD)
![MySQL ERD](./erd-diagram.png)

---

## 6. 3-Tier System Architecture Diagram
![3-Tier System Architecture](./system-architecture.png)

---

## 7. High-Fidelity UI Designs

### 7.1 Unified Authentication (Login & Register)
![Authentication UI](./ui-auth.png)

### 7.2 Instructor (Admin) Dashboard
![Admin Dashboard UI](./ui-admin-dashboard.png)

### 7.3 Question Bank Authoring
![Question Bank UI](./ui-question-bank.png)

### 7.4 Instructor Results & Analytics
![Admin Results UI](./ui-admin-results.png)

### 7.5 Student Portal & Join Quiz
![Student Portal UI](./ui-student-portal.png)

### 7.6 Student Exam Interface & Built-in Calculator
![Student Exam UI](./ui-student-exam.png)
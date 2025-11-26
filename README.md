<h1 align="center">Ashesi Web Technologies 2025 - Course Management System</h1>

<p align="center">
  A comprehensive web application for managing courses, attendance tracking, and student enrollment at Ashesi University.<br>
  Designed for Web Technologies 2025.
  <br />
  <a href="#features"><strong>Explore the Features »</strong></a>
  <br /><br />
  <img src="https://img.shields.io/badge/Status-Complete-green?style=for-the-badge">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge">
  <img src="https://img.shields.io/badge/PHP-8.x-blue?style=for-the-badge">
</p>


## Table of Contents

1. [Overview](#overview)  
2. [Features](#features)  
   - [Students](#students)  
   - [Faculty](#faculty)  
3. [Core Web Pages](#core-web-pages)  
4. [Database Design](#database-design)  
5. [Tech Stack](#tech-stack)  
6. [Learning Objectives](#learning-objectives)  
7. [Installation Guide](#installation-guide)  
8. [Project Structure](#project-structure)  
9. [Contributors](#contributors)  
10. [License](#license)



## Overview

The **Ashesi Course Management System** is a full-stack web application that streamlines course registration, attendance tracking, and request approval workflows for students and faculty.  

The system centralizes academic resource management and provides:
- Role-based access control (Student/Faculty)  
- Course request and approval workflow  
- Attendance tracking and statistics  
- Secure authentication with bcrypt password hashing  
- CSRF protection and SQL injection prevention  
- Responsive UI with Ashesi branding  

This project demonstrates hands-on experience with PHP-MySQL integration, session management, security best practices, and modern web design patterns.


## Features

### Students
- Browse available courses with detailed information  
- Request to join courses with approval workflow  
- View enrolled courses and attendance statistics  
- Track attendance records across all sessions  
- Report attendance issues to faculty  
- Secure registration and login  

### Faculty
- Create and manage courses  
- Review and approve/reject student enrollment requests  
- Mark student attendance for class sessions  
- View course enrollment statistics  
- Manage session schedules and notes  


## Core Web Pages

| Page | Description |
|------|-------------|
| **Home Page** | Landing page with login and registration options |
| **Login / Logout** | Secure authentication with session management |
| **Registration** | New user account creation with validation |
| **Student Dashboard** | Course enrollment, attendance stats, and request status |
| **Faculty Dashboard** | Course management and student approval interface |
| **Browse Courses** | Search and filter available courses |
| **Request Management** | Faculty interface for approving enrollment requests |
| **Create Course** | Faculty form for adding new courses |

---

## Database Design

| Table | Fields |
|-------|--------|
| **users** | user_id, name, email, password, role, ashesi_id, created_at, last_login |
| **courses** | course_id, course_code, course_name, faculty_id, semester, year, description |
| **course_requests** | request_id, student_id, course_id, status, requested_at, reviewed_at, reviewed_by |
| **enrollments** | enrollment_id, student_id, course_id, enrollment_date, status |
| **sessions** | session_id, course_id, session_date, session_time, session_type, notes |
| **attendance** | attendance_id, student_id, session_id, status, marked_at, marked_by |
| **issues** | issue_id, student_id, session_id, description, status, resolved_by |

This schema supports full academic workflow management with referential integrity and optimized indexing.

\\## 🚀 Tech Stack

<p align="left">
  <img src="https://skillicons.dev/icons?i=html,css,js,ts,react,angular,nodejs,nestjs,graphql,mongodb,php,mysql,bootstrap,tailwind,docker,git,vscode,xd,photoshop,webpack,d3" />
  <img src="https://img.shields.io/badge/Oracle%20APEX-F80000?style=flat-square&logo=oracle&logoColor=white" />
</p>

| Technology | Purpose |
|-----------|---------|
| **HTML5 / CSS3 / TailwindCSS** | Frontend structure and Ashesi-branded styling |
| **JavaScript** | Client-side form validation and interactivity |
| **PHP 8.x** | Backend logic, authentication, and database operations |
| **MySQL** | Relational database with normalized schema |
| **PDO** | Secure database queries with prepared statements |
| **Git & GitHub** | Version control and project management |



## Learning Objectives

Through building this project, practical experience was gained in:
- Implementing secure authentication with password hashing and CSRF protection  
- Designing and normalizing relational database schemas  
- Performing complex SQL queries with JOINs and aggregate functions  
- Building role-based access control systems  
- Managing PHP sessions and state  
- Creating RESTful-style request handlers  
- Implementing approval workflows and status management  
- Designing responsive layouts with Tailwind CSS  
- Following secure coding practices (input sanitization, prepared statements)  
- Organizing multi-page PHP applications with modular architecture  


## Installation Guide

1. **Clone the repository**
   ```bash
   git clone https://github.com/trpbtl/ashesi-webtech-2025--peercoding-eyramawoye.git
   ```

2. **Set up the database**
    - Open phpMyAdmin
    - Create a database: `webtech_2025A_eyram_awoye`
    - Import `activity_03/individual/schema.sql` for Activity 3
    - Import `activity_04/individual/schema.sql` for Activity 4

3. **Configure database connection**
    - Update credentials in `config.php`:
    ```php
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'webtech_2025A_eyram_awoye');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    ```

4. **Run the project**
    - Start Apache and MySQL in XAMPP
    - Visit Activity 3:
    ```
    http://localhost/activity_03/individual/
    ```
    - Visit Activity 4:
    ```
    http://localhost/activity_04/individual/
    ```

5. **Test Accounts**
    - Register a new account through the registration page
    - Or use sample accounts (if seeded):
      - Student: john.doe@ashesi.edu.gh / Password123!
      - Faculty: jane.smith@ashesi.edu.gh / Password123!


## Project Structure

```
ashesi-webtech-2025--peercoding-eyramawoye/
├── activity_03/individual/          # Attendance Management System
│   ├── config.php                   # Database configuration
│   ├── helpers.php                  # Utility functions
│   ├── index.php                    # Login page
│   ├── register.php                 # Registration page
│   ├── dashboard.php                # Main dashboard
│   ├── student_dashboard.php        # Student view
│   ├── schema.sql                   # Database schema
│   └── ...
├── activity_04/individual/          # Course Management System
│   ├── config.php                   # Database configuration
│   ├── helpers.php                  # Utility functions
│   ├── index.php                    # Login page
│   ├── register.php                 # Registration page
│   ├── dashboard.php                # Main dashboard
│   ├── browse_courses.php           # Course catalog
│   ├── create_course.php            # Faculty course creation
│   ├── manage_requests.php          # Faculty approval interface
│   ├── schema.sql                   # Database schema
│   └── ...
└── README.md
```


## Contributors

<table>
  <tr>
    <td align="center">
      <a href="https://github.com/trpbtl">
        <img src="https://avatars.githubusercontent.com/u/161168875?v=4" width="90" height="90" style="border-radius:50%;">
        <br />
        <b>Eyram Awoye</b>
        <br />
        <sub>trpbtl</sub>
      </a>
    </td>
  </tr>
</table>


## License
Distributed under the MIT License.


## Repository Information
- **GitHub Repository:** https://github.com/trpbtl/ashesi-webtech-2025--peercoding-eyramawoye
- **Developer:** Eyram Awoye
- **Course:** Web Technologies 2025
- **Institution:** Ashesi University

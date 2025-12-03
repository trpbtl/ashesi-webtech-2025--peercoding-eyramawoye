# Activity 05 - Course Management & Attendance System

## Overview
Complete course management system (from Activity 4) integrated with live attendance session tracking. Two distinct workflows: course enrollment and session attendance.

## Features

### Course Management Workflow (Activity 4)

#### Student Features
- **Browse Courses** - View all available courses to join
- **Request Enrollment** - Submit requests to join courses
- **Track Requests** - See pending/approved/rejected enrollment requests
- **View Enrolled Courses** - See all active course enrollments

#### Faculty Features
- **Create Courses** - Set up new courses with details
- **Manage Requests** - Approve or reject student enrollment requests
- **View Course Roster** - See all enrolled students per course

### Attendance Session Workflow (NEW)

#### Faculty Features
- **Start Live Sessions** - Create attendance sessions with auto-generated codes
- **Real-time Monitoring** - See live attendance count as students mark
- **Session Dashboard** - View all active sessions with countdown timers
- **End Sessions** - Manually close sessions before code expiration
- **Session History** - Review completed sessions from today

#### Student Features
- **Join Live Sessions** - View all active sessions for enrolled courses
- **Mark Attendance** - Enter 6-digit code to mark attendance
- **Real-time Status** - See if you've already marked attendance
- **Session Timer** - View remaining time to mark attendance
- **Attendance History** - See today's previous sessions and your status

## Database Schema

### Tables
- **users** - User accounts (students, faculty)
- **courses** - Course information
- **enrollments** - Student-course relationships
- **attendance_sessions** - Session details with attendance codes
- **attendance_records** - Individual attendance records

### Key Features
- Unique attendance codes (6 characters, alphanumeric)
- Code expiration tracking
- Status tracking (present, late, absent)
- Method tracking (code-based vs manual)
- Timestamp tracking for all records

## Installation

1. **Import Database**
   ```sql
   mysql -u root -p < schema.sql
   ```

2. **Configure Database**
   Update `config.php` with your credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'webtech_2025A_eyram_awoye');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Access Application**
   - Faculty: http://localhost/activity_05/individual/
   - Use test accounts from schema.sql
   - Default password: Password123!

## Usage Flow

### Complete Workflow

**Course Enrollment (Activity 4):**
1. **Student:** Browse available courses → Request to join
2. **Faculty:** Review requests → Approve/Reject
3. **Student:** Get enrolled in course

**Live Attendance Session (NEW):**
1. **Faculty:** Go to "Start Live Session" → Select course → Create session
2. **Faculty:** Share 6-digit code with class
3. **Student:** Go to "Join Session" → See active sessions for enrolled courses
4. **Student:** Enter code → Mark attendance (present/late based on timing)
5. **Faculty:** Monitor live attendance count → End session when done
6. **Both:** View attendance records and reports

### Faculty Workflow
1. Login as faculty member
## File Structure

```
activity_05/individual/
├── config.php                    # Database configuration
├── helpers.php                   # Utility functions
├── index.php                     # Login page
├── register.php                  # Registration
├── login_handler.php             # Login processing
├── register_handler.php          # Registration processing
├── logout.php                    # Logout handler
├── dashboard.php                 # Role-based dashboard router
│
├── COURSE MANAGEMENT (Activity 4):
├── browse_courses.php            # Student: Browse available courses
├── request_join_handler.php      # Student: Submit enrollment request
├── manage_requests.php           # Faculty: View/manage enrollment requests
├── process_request.php           # Faculty: Approve/reject requests
├── create_course.php             # Faculty: Course creation form
├── create_course_handler.php     # Faculty: Course creation processing
│
├── ATTENDANCE SESSIONS (NEW):
├── active_sessions.php           # Faculty: Start & monitor live sessions
├── join_session.php              # Student: View & join active sessions
├── end_session.php               # Faculty: End session handler
├── create_session_handler.php    # Faculty: Create session processing
├── mark_attendance_handler.php   # Student: Mark attendance with code
│
├── DASHBOARDS & REPORTS:
├── faculty_dashboard.php         # Faculty main dashboard
├── student_dashboard.php         # Student main dashboard
├── view_sessions.php             # List all sessions for a course
├── mark_attendance.php           # Manual attendance marking
├── mark_attendance_save.php      # Save manual attendance
├── view_attendance.php           # Attendance records viewer
│
├── schema.sql                    # Database schema
└── README.md                     # This file
```File Structure

```
activity_05/individual/
├── config.php                    # Database configuration
├── helpers.php                   # Utility functions
├── index.php                     # Login page
├── register.php                  # Registration
├── login_handler.php             # Login processing
├── register_handler.php          # Registration processing
├── logout.php                    # Logout handler
├── dashboard.php                 # Role-based dashboard router
├── faculty_dashboard.php         # Faculty main dashboard
├── student_dashboard.php         # Student main dashboard
├── create_course.php             # Course creation form
├── create_course_handler.php     # Course creation processing
├── create_session.php            # Session creation form
├── create_session_handler.php    # Session creation processing
├── view_sessions.php             # List all sessions for a course
├── mark_attendance.php           # Manual attendance marking
├── mark_attendance_save.php      # Save manual attendance
├── mark_attendance_handler.php   # Student code-based marking
├── view_attendance.php           # Attendance records viewer
├── schema.sql                    # Database schema
└── README.md                     # This file
```

## Security Features

- ✅ CSRF token protection on all forms
- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Role-based access control
- ✅ Session management and regeneration
## Key Improvements from Activity 4

1. **Separate Workflows** - Clear distinction between course enrollment and attendance sessions
2. **Live Sessions** - Real-time attendance tracking with countdown timers
3. **Student Session View** - Students only see active sessions for enrolled courses
4. **Auto-refresh** - Session pages auto-reload to show live updates
5. **Visual Indicators** - Color-coded status badges and progress indicators
6. **Quick Actions** - Prominent action cards on dashboards for both workflows
7. **Session History** - Today's previous sessions visible to both roles
8. **Copy Code Feature** - Faculty can quickly copy attendance codes
9. **Expiration Alerts** - Visual warnings when codes are about to expire
10. **Already Marked Detection** - Students see if they've already marked attendance
**Faculty:**
- jane.smith@ashesi.edu.gh / Password123!
- michael.brown@ashesi.edu.gh / Password123!

## Key Improvements from Previous Activities

1. **Automated Attendance Codes** - No more manual marking only
2. **Code Expiration** - Time-limited codes for security
3. **Late Detection** - Automatic late marking based on session start time
4. **Dual Marking Methods** - Both code-based and manual options
5. **Real-time Statistics** - Live attendance percentages
6. **Comprehensive Reporting** - Detailed attendance records
7. **Better UX** - Intuitive dashboards for both roles

## Technologies Used

- **Backend:** PHP 8.x with PDO
- **Database:** MySQL 8.x
- **Frontend:** HTML5, TailwindCSS, JavaScript
- **Icons:** Font Awesome 6.4
- **Security:** bcrypt, CSRF tokens, prepared statements

## Author

Eyram Awoye - Web Technologies 2025 - Ashesi University

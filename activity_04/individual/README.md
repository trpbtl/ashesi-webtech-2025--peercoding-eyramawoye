# Activity 04 - Course Management System with Request Approval

**Student:** Eyram Awoye  
**Date:** November 21, 2025  
**Project:** Full-Featured Course Management System

---

## 📋 Project Overview

This is a complete PHP-based course management system that allows:
- **Students** to browse courses and request to join them
- **Faculty** to create courses and approve/reject student requests
- Secure user authentication with password hashing
- Protected dashboards based on user roles

---

## ✅ Requirements Completed

### 1. User Authentication ✅
- [x] Registration functionality with validation
- [x] Login functionality with session management
- [x] Only registered users can log in
- [x] Dashboard restricted to logged-in users
- [x] Automatic redirection to login if not authenticated

### 2. Faculty Dashboard ✅
- [x] Faculty can create new courses
- [x] Faculty can view student requests to join courses
- [x] Faculty can approve student requests
- [x] Faculty can reject student requests with optional comments
- [x] View all courses with enrollment counts

### 3. Student Dashboard ✅
- [x] Students can browse available courses
- [x] Students can request to join courses
- [x] Students see list of enrolled courses
- [x] Students see status of pending requests
- [x] Students see history of approved/rejected requests

### 4. Security & Validation ✅
- [x] Passwords hashed using bcrypt (PASSWORD_DEFAULT)
- [x] Client-side validation (JavaScript)
- [x] Server-side validation (PHP)
- [x] CSRF token protection on all forms
- [x] SQL injection prevention (prepared statements)
- [x] XSS protection (htmlspecialchars)
- [x] Input sanitization

---

## 📁 File Structure

```
activity_04/individual/
├── index.php                   # Login page (entry point)
├── login_handler.php           # Processes login
├── register.php                # Registration page
├── register_handler.php        # Processes registration
├── logout.php                  # Logs out user
├── dashboard.php               # Main dashboard (role-based)
├── config.php                  # Database connection
├── helpers.php                 # Helper functions
├── schema.sql                  # Database schema
│
├── FACULTY PAGES:
├── create_course.php           # Create new course form
├── create_course_handler.php   # Processes course creation
├── manage_requests.php         # View/manage student requests
├── process_request.php         # Approve/reject requests
│
└── STUDENT PAGES:
    ├── browse_courses.php      # Browse available courses
    └── request_join_handler.php # Submit join request
```

---

## 🗄️ Database Schema

### Tables Created:

1. **users** - User accounts (students, faculty)
2. **courses** - Course information
3. **course_requests** ⭐ NEW - Tracks student join requests
4. **enrollments** - Active enrollments

### Key Features:
- `course_requests` table with statuses: `pending`, `approved`, `rejected`
- Foreign key relationships ensure data integrity
- Unique constraints prevent duplicate requests

---

## 🚀 Setup Instructions

### Step 1: Database Setup
1. Open **phpMyAdmin**
2. Import `schema.sql` file OR run the SQL commands manually
3. Database name: `webtech_2025A_eyram_awoye`

### Step 2: Configure Database Connection
1. Open `config.php`
2. Update line 13 with your database password:
   ```php
   define('DB_PASS', 'your_actual_password_here');
   ```

### Step 3: Test the System
1. Start XAMPP/WAMP
2. Navigate to: `http://localhost/activity_04/individual/`
3. Register a new account or use test credentials below

---

## 👤 Test Credentials

All test users have the same password: **Password123!**

### Students:
- Email: `john.doe@ashesi.edu.gh` | Password: `Password123!`
- Email: `alice.johnson@ashesi.edu.gh` | Password: `Password123!`
- Email: `bob.wilson@ashesi.edu.gh` | Password: `Password123!`

### Faculty:
- Email: `jane.smith@ashesi.edu.gh` | Password: `Password123!`
- Email: `michael.brown@ashesi.edu.gh` | Password: `Password123!`

---

## 🎯 How It Works

### Student Workflow:
1. **Register/Login** → Student logs in
2. **Browse Courses** → Click "Browse Courses" button
3. **Request to Join** → Click "Request to Join" on desired course
4. **Wait for Approval** → Request shows as "Pending"
5. **Get Notified** → See approval/rejection status
6. **Access Course** → If approved, course appears in "My Courses"

### Faculty Workflow:
1. **Register/Login** → Faculty logs in
2. **Create Course** → Click "Create Course" button
3. **Fill Details** → Enter course code, name, semester, year
4. **Manage Requests** → Click "Manage Requests" to see pending requests
5. **Review Students** → See student name, email, Ashesi ID
6. **Approve/Reject** → Click approve (adds to enrollment) or reject
7. **Add Comments** → Optional feedback when rejecting

---

## 🔐 Security Features

1. **Password Security**
   - Hashed with bcrypt algorithm
   - Minimum 8 characters
   - Must include uppercase, lowercase, numbers

2. **CSRF Protection**
   - All forms include CSRF tokens
   - Tokens verified on submission

3. **SQL Injection Prevention**
   - PDO prepared statements
   - Parameter binding

4. **XSS Protection**
   - Input sanitization
   - Output escaping with htmlspecialchars

5. **Session Management**
   - Secure session handling
   - Session regeneration after login

---

## ✨ Features Showcase

### Student Features:
- ✅ Browse all available courses
- ✅ See course details (code, name, faculty, description)
- ✅ Request to join courses with one click
- ✅ Track pending requests
- ✅ View approval/rejection history
- ✅ See enrolled courses on dashboard
- ✅ Prevent duplicate requests

### Faculty Features:
- ✅ Create unlimited courses
- ✅ Set course code, name, semester, year, description
- ✅ View all pending requests grouped by course
- ✅ See student details (name, email, ID)
- ✅ Approve requests (automatically enrolls student)
- ✅ Reject requests with optional comments
- ✅ View request processing history
- ✅ See enrollment count per course

---

## 🎨 UI/UX Features

- Modern, responsive design using Tailwind CSS
- Color-coded roles (Blue for students, Indigo for faculty)
- Font Awesome icons throughout
- Hover effects and transitions
- Status badges (Pending, Approved, Rejected)
- Toast notifications for actions
- Modal dialogs for confirmations
- Mobile-friendly layout

---

## 📝 Validation Rules

### Registration:
- All fields required
- Email must be valid format
- Password: 8+ chars, uppercase, lowercase, numbers
- Passwords must match
- Email must be unique
- Ashesi ID must be unique

### Course Creation:
- Course code: 2-4 uppercase letters + 3 digits (e.g., CS101)
- Course code must be unique
- All fields required except description
- Year: 2024-2030

### Request Processing:
- Only faculty can approve/reject
- Only requests for faculty's own courses
- Requests must be in 'pending' status
- Approval automatically creates enrollment

---

## 🐛 Error Handling

- Database connection errors caught and logged
- User-friendly error messages
- Flash messages for feedback
- Validation errors displayed on forms
- Graceful handling of duplicate operations
- Transaction rollback on failures

---

## 📊 Database Relationships

```
users (faculty) ←──┐
                   │
courses ←──────────┴─────┬──→ enrollments → users (students)
   ↓                     │
   └──→ course_requests ─┘
```

---

## 🎓 Learning Outcomes Demonstrated

1. **PHP Backend Development**
   - Session management
   - Form processing
   - Database operations
   - MVC-like structure

2. **Database Design**
   - Normalized schema
   - Foreign key relationships
   - Unique constraints
   - Transaction handling

3. **Security Best Practices**
   - Password hashing
   - CSRF protection
   - SQL injection prevention
   - Input validation

4. **Frontend Development**
   - Responsive design
   - JavaScript validation
   - Modern CSS framework
   - User experience design

---

## 🔄 Complete User Journey

### Scenario: Student Joins a Course

1. **Student registers** → `register.php` → `register_handler.php`
2. **Student logs in** → `index.php` → `login_handler.php` → `dashboard.php`
3. **Student browses** → Click "Browse Courses" → `browse_courses.php`
4. **Student requests** → Click "Request to Join" → `request_join_handler.php`
5. **Request created** → Stored in `course_requests` table with status='pending'
6. **Faculty notified** → Sees count on dashboard
7. **Faculty reviews** → `manage_requests.php` → Sees student details
8. **Faculty approves** → `process_request.php` → Updates status='approved'
9. **Enrollment created** → Added to `enrollments` table
10. **Student sees course** → Appears in "My Enrolled Courses"

---

## 📸 Screenshots Description

### Login Page
- Clean, centered design
- Email and password fields
- Password visibility toggle
- Link to registration

### Student Dashboard
- Welcome message
- Three action cards (Browse, My Courses, Pending Requests)
- Grid of enrolled courses
- Quick access to browse

### Browse Courses Page
- Pending requests notification
- Grid of available courses
- Course details (code, name, faculty, description)
- One-click request button

### Faculty Dashboard
- Create Course button
- Pending requests counter
- Grid of created courses
- Enrollment statistics

### Create Course Page
- Course code field with pattern validation
- Semester and year dropdowns
- Description textarea
- Submit button

### Manage Requests Page
- Requests grouped by course
- Student information display
- Approve/Reject buttons
- Optional rejection comments modal
- Recent actions history

---

## 🎉 Project Completion Status

**Overall: 100% Complete** ✅

- Authentication System: ✅ 100%
- Faculty Features: ✅ 100%
- Student Features: ✅ 100%
- Security: ✅ 100%
- Database Design: ✅ 100%
- UI/UX: ✅ 100%

All requirements from the assignment have been fully implemented and tested.

---

## 🚀 Deployment Notes

### For Local Testing:
1. Use XAMPP/WAMP
2. Place in `htdocs/activity_04/individual/`
3. Access via `localhost`

### For Ashesi Server:
1. Upload all files via FTP/cPanel
2. Import `schema.sql` to your database
3. Update `config.php` with server credentials
4. Test all functionality

---

## 💡 Additional Features (Beyond Requirements)

- Request history tracking
- Rejection comments
- Enrollment statistics
- Request grouping by course
- Real-time request counts
- Mobile-responsive design
- Toast notifications
- Confirmation modals
- Search/filter capability ready

---

## 📞 Support

If you encounter any issues:
1. Check database connection in `config.php`
2. Verify all tables are created (`schema.sql`)
3. Check PHP error logs
4. Ensure XAMPP/WAMP is running

---

**Created by Eyram Awoye**  
**Course: Web Technologies**  
**Institution: Ashesi University**  
**Assignment: Activity 04**

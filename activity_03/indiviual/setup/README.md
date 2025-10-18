# Student Attendance Management System

## 📋 Project Overview

This is a full-stack web application designed to manage student attendance at Ashesi University. The system allows students to view their attendance history, distinguish between lecture and lab sessions, and report attendance issues. Faculty members can flag sessions as labs/practicals and add notes for students.

## 🎯 Project Objectives

This project addresses two key stakeholder requirements:

### Student Association President Requirements:
- ✅ View attendance history in an intuitive dashboard
- ✅ Distinguish between regular lectures and lab/practical sessions
- ✅ Faculty ability to flag sessions and add preparatory notes
- ✅ Mobile-friendly responsive design
- ✅ Attendance issue reporting system

### Web Hosting Consultant Requirements:
- ✅ Backend implementation using PHP
- ✅ Full CRUD operations (Create, Read, Update, Delete)
- ✅ User authentication and login system
- ✅ MySQL database for data persistence
- ✅ PHP sessions for user tracking
- ✅ Complete documentation with ERD and wireframes

## 🛠️ Tech Stack

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Responsive styling with media queries
- **JavaScript** - Interactive functionality
- **Tailwind CSS** - Utility-first CSS framework (optional)

### Backend
- **PHP** - Server-side programming
- **MySQL** - Relational database management
- **PHP Sessions** - User authentication and state management

### Development Environment
- **Ashesi Hosting Platform** - Production server
- **Git/GitHub** - Version control
- **VS Code** - Development IDE

## 📁 Project Structure

```
activity_03/
├── individual/
│   ├── index.php                    # Login/Landing page
│   ├── student_dashboard.php        # Student attendance view
│   ├── faculty_dashboard.php        # Faculty session management
│   ├── report_issue.php             # Attendance issue reporting
│   ├── config.php                   # Database configuration
│   ├── login_handler.php            # Authentication logic
│   ├── logout.php                   # Session termination
│   ├── css/
│   │   └── styles.css              # Custom styles
│   └── js/
│       └── scripts.js              # Client-side scripts
├── group/
│   └── (Group collaboration work)
└── documents/ (GitHub ONLY)
    ├── database_design.md          # Database schema documentation
    ├── requirements_document.md    # Feature requirements
    ├── wireframes/                 # UI/UX designs
    └── peer_grading.pdf           # Peer evaluation
```

## 🗄️ Database Schema

### Tables

1. **users**
   - user_id (Primary Key)
   - name
   - email
   - password (hashed)
   - role (student/faculty)

2. **courses**
   - course_id (Primary Key)
   - course_name
   - course_code
   - faculty_id (Foreign Key)

3. **sessions**
   - session_id (Primary Key)
   - course_id (Foreign Key)
   - date
   - session_type (lecture/lab)
   - notes

4. **attendance**
   - attendance_id (Primary Key)
   - student_id (Foreign Key)
   - session_id (Foreign Key)
   - status (present/absent/late)

5. **enrollments**
   - enrollment_id (Primary Key)
   - student_id (Foreign Key)
   - course_id (Foreign Key)

See `documents/database_design.md` for detailed ERD and relationships.

## 🚀 Setup Instructions

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Access to Ashesi hosting platform
- Git installed locally

### Local Development Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/trpbtl/ashesi-webtech-2025--peercoding-eyramawoye.git
   cd ashesi-webtech-2025--peercoding-eyramawoye/activity_03/individual
   ```

2. **Configure Database**
   - Your database is already created by the school (e.g., `webtech_2025A_eyram_awoye`)
   - Open phpMyAdmin and find your database
   - Import the schema: Click "Import" → Select `schema.sql` → Click "Go"
   - Update `config.php` with your database name and credentials

3. **Update config.php**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'webtech_2025A_eyram_awoye');  // Your actual database name
   ```

4. **Start Local Server**
   ```bash
   php -S localhost:8000
   ```

5. **Access Application**
   - Open browser: `http://localhost:8000/index.php`

### Ashesi Server Deployment

1. **Upload Files**
   - Connect via FTP/SFTP to Ashesi server
   - Upload all files in `individual/` folder
   - DO NOT upload `documents/` folder to server

2. **Configure Database**
   - Access phpMyAdmin on Ashesi platform
   - Find your existing database (already created by school)
   - Import schema.sql file
   - Update `config.php` with your database name and Ashesi credentials

3. **Test Deployment**
   - Access via: `https://your-ashesi-url/activity_03/individual/`

## 👤 User Roles & Features

### Student Features
- 🔐 Secure login
- 📊 View attendance dashboard
- 📚 See all enrolled courses
- 🔬 Distinguish between lectures and labs (visual indicators)
- 📱 Mobile-responsive interface
- 📧 Report attendance issues

### Faculty Features
- 🔐 Secure login
- 📝 View all sessions for their courses
- 🏷️ Flag sessions as "Lab/Practical"
- 📌 Add notes about required materials
- ✏️ Update session information
- 👥 View student attendance records

## 🔒 Security Features

- ✅ Password hashing (PHP `password_hash()`)
- ✅ PHP session management
- ✅ SQL injection prevention (prepared statements)
- ✅ XSS protection (input sanitization)
- ✅ Session timeout handling
- ✅ Role-based access control

## 📱 Mobile Responsiveness

The application is fully responsive with:
- Flexible grid layouts
- Mobile-first CSS approach
- Touch-friendly buttons
- Readable text sizes
- Collapsible navigation
- Optimized for screens 320px and above

## 🧪 Testing

### Test Users (Sample Data)

**Student Account:**
- Email: student@ashesi.edu.gh
- Password: student123

**Faculty Account:**
- Email: faculty@ashesi.edu.gh
- Password: faculty123

### Test Scenarios
1. Login as student and view dashboard
2. Check attendance records display correctly
3. Test mobile responsiveness (resize browser)
4. Submit attendance issue report
5. Login as faculty and update session notes
6. Verify CRUD operations work correctly

## 📊 CRUD Operations Implementation

| Operation | Implementation |
|-----------|----------------|
| **CREATE** | Add new sessions, attendance records, issue reports |
| **READ** | View attendance history, course lists, session details |
| **UPDATE** | Faculty updating session type and notes |
| **DELETE** | Remove incorrect records (admin/faculty only) |

## 🎨 UI/UX Features

- **Visual Distinction**: 
  - 📚 Blue color scheme for lectures
  - 🔬 Orange color scheme for lab sessions
  - Icons for quick identification

- **User Feedback**:
  - Success/error messages
  - Loading indicators
  - Form validation

- **Accessibility**:
  - Semantic HTML
  - ARIA labels
  - Keyboard navigation support

## 📝 Documentation

All documentation is available in the `documents/` folder:

1. **database_design.md** - Complete database schema and ERD
2. **requirements_document.md** - Detailed feature specifications
3. **wireframes/** - UI/UX mockups
4. **peer_grading.pdf** - Peer evaluation forms

## 🤝 Contributing

This is an academic project for Web Technologies course at Ashesi University.

**Team Members:**
- [Your Name] - Individual Implementation
- [Team Members] - Group Collaboration

## 📞 Support & Contact

For issues or questions:
- Report via the attendance issue form
- Contact: [Your Email]
- GitHub Issues: [Repository Issues Page]

## 📄 License

This project is created for educational purposes as part of Ashesi University coursework.

## 🎓 Acknowledgments

- Student Association President for feature requirements
- Web Hosting Consultant for technical guidance
- Ashesi University Web Technologies Course Team

## 📅 Project Timeline

- **Phase 1 (Planning)**: Database design, wireframes
- **Phase 2 (Setup)**: Folder structure, database creation
- **Phase 3 (Core)**: Login, dashboards, CRUD operations
- **Phase 4 (Polish)**: Mobile responsiveness, visual improvements
- **Phase 5 (Deployment)**: Server upload, testing
- **Phase 6 (Documentation)**: Final documentation and submission

---

**Last Updated**: October 18, 2025  
**Version**: 1.0.0  
**Status**: In Development

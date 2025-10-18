# 📚 COMPLETE PHP BEGINNER'S GUIDE FOR YOUR PROJECT

## 🎯 What You've Built

You now have a complete attendance management system with:
- User registration and login
- Student dashboard to view attendance
- Database integration
- Security features

---

## 📖 HOW EVERYTHING WORKS TOGETHER

### 1. **config.php** - The Foundation
**What it does:** Connects to database and provides helper functions

**Key Concepts:**
```php
// PDO = PHP Data Objects (way to talk to database)
$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

// Sessions = way to remember logged-in users across pages
session_start();

// $_SESSION array stores user data
$_SESSION['user_id'] = 123;  // Store
echo $_SESSION['user_id'];    // Retrieve
```

**Why it's important:**
- Every other file includes this file
- Without it, no database connection
- Without sessions, can't track logged-in users

---

### 2. **schema.sql** - The Database Structure
**What it does:** Creates all the tables to store data

**Tables you created:**
- `users` - stores student/faculty accounts
- `courses` - stores course information
- `sessions` - stores each class session
- `attendance` - stores who was present/absent
- `enrollments` - links students to courses
- `issues` - stores attendance complaints

**How to use it:**
1. Open phpMyAdmin on Ashesi server
2. Find your database (e.g., `webtech_2025A_eyram_awoye`) in left sidebar
3. Click on it to select it
4. Click "Import" tab
5. Choose `schema.sql` file
6. Click "Go"
7. All tables will be created automatically

---

### 3. **index.php** - Login Page
**What it does:** Shows login form

**Flow:**
```
User enters email + password
   ↓
Clicks "Sign In"
   ↓
Form sends data to login_handler.php
   ↓
Handler checks if credentials are correct
   ↓
If correct: redirect to dashboard
If wrong: show error message
```

**Key PHP code:**
```php
// Check if user is already logged in
if (isLoggedIn()) {
    redirectToDashboard();  // Send them to their dashboard
}

// Generate security token
$csrfToken = generateCSRFToken();
```

---

### 4. **login_handler.php** - Processes Login
**What it does:** Checks username/password and logs user in

**Step-by-step:**
```php
// STEP 1: Get form data
$email = $_POST['email'];
$password = $_POST['password'];

// STEP 2: Find user in database
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
$user = $stmt->fetch();

// STEP 3: Check password
if (password_verify($password, $user['password'])) {
    // Password correct!
    
    // STEP 4: Store user info in session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    
    // STEP 5: Redirect to dashboard
    header("Location: student_dashboard.php");
}
```

---

### 5. **register.php** - Registration Form
**What it does:** Shows form to create new account

**Form fields:**
- Name
- Email
- Password
- Confirm Password
- Role (student/faculty)
- Ashesi ID

**JavaScript validation:**
```javascript
// Check if passwords match
if (password !== confirmPassword) {
    alert('Passwords do not match!');
}

// Check password strength
if (password.length < 8) {
    alert('Password too short!');
}
```

---

### 6. **register_handler.php** - Processes Registration
**What it does:** Creates new user account

**Key steps:**
```php
// 1. Validate all fields are filled
if (empty($email) || empty($password)) {
    // Show error
}

// 2. Check email doesn't already exist
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
// If count > 0, email already exists

// 3. Hash password (NEVER store plain text!)
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// 4. Insert new user
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, ashesi_id) 
                       VALUES (:name, :email, :password, :role, :ashesi_id)");
$stmt->execute([
    ':name' => $name,
    ':email' => $email,
    ':password' => $hashedPassword,
    ':role' => $role,
    ':ashesi_id' => $ashesiId
]);
```

---

### 7. **student_dashboard.php** - Main Student Page
**What it does:** Shows student's attendance records

**Three main queries:**

**Query 1: Get student's courses**
```php
SELECT c.course_code, c.course_name
FROM enrollments e
JOIN courses c ON e.course_id = c.course_id
WHERE e.student_id = :student_id
```

**Query 2: Get attendance for each course**
```php
SELECT s.session_date, s.session_type, a.status
FROM sessions s
LEFT JOIN attendance a ON s.session_id = a.session_id
WHERE s.course_id = :course_id AND a.student_id = :student_id
```

**Query 3: Calculate statistics**
```php
$present = 0;
$absent = 0;
$late = 0;

foreach ($records as $record) {
    if ($record['status'] === 'present') $present++;
    if ($record['status'] === 'absent') $absent++;
    if ($record['status'] === 'late') $late++;
}

$percentage = ($present / $total) * 100;
```

---

### 8. **logout.php** - Logs User Out
**What it does:** Ends the session and clears user data

**Three-step logout:**
```php
// 1. Clear session array
$_SESSION = [];

// 2. Delete session cookie
setcookie(session_name(), '', time() - 3600);

// 3. Destroy session file
session_destroy();

// 4. Redirect to login
header("Location: index.php");
```

---

## 🔒 SECURITY FEATURES EXPLAINED

### 1. **CSRF Protection**
**What:** Prevents fake form submissions from other websites

**How it works:**
```php
// Generate token when showing form
$token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $token;

// Verify token when form is submitted
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid token!");
}
```

### 2. **SQL Injection Prevention**
**What:** Prevents hackers from manipulating database queries

**Wrong way (vulnerable):**
```php
$sql = "SELECT * FROM users WHERE email = '$email'";
// If user enters: ' OR '1'='1
// Query becomes: SELECT * FROM users WHERE email = '' OR '1'='1'
// This returns ALL users!
```

**Right way (safe):**
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute([':email' => $email]);
// PDO escapes special characters automatically
```

### 3. **Password Hashing**
**What:** Encrypts passwords so they can't be read

**How it works:**
```php
// When user registers
$hash = password_hash('mypassword', PASSWORD_DEFAULT);
// Result: $2y$10$abcdefg... (cannot be reversed!)

// When user logs in
password_verify('mypassword', $hash);
// Returns true if passwords match
```

### 4. **Input Sanitization**
**What:** Cleans user input to prevent XSS attacks

```php
function sanitizeInput($data) {
    $data = trim($data);              // Remove whitespace
    $data = stripslashes($data);      // Remove backslashes
    $data = htmlspecialchars($data);  // Convert < > to &lt; &gt;
    return $data;
}
```

---

## 🗃️ DATABASE CONCEPTS FOR BEGINNERS

### What is a Database?
- Like an Excel spreadsheet
- Organized in tables with rows and columns
- Can store millions of records
- Fast searching and filtering

### Tables = Spreadsheets
```
users table:
+----------+---------------+----------------------+----------+
| user_id  | name          | email                | role     |
+----------+---------------+----------------------+----------+
| 1        | John Doe      | john@ashesi.edu.gh   | student  |
| 2        | Jane Smith    | jane@ashesi.edu.gh   | faculty  |
+----------+---------------+----------------------+----------+
```

### Relationships Between Tables
```
students ←→ enrollments ←→ courses
  (1)          (many)         (1)

One student can enroll in many courses
One course can have many students
Enrollments table links them together
```

### SQL Queries Explained

**SELECT** - Get data
```sql
SELECT name, email FROM users WHERE role = 'student'
       ↑              ↑           ↑
    what to get   from where   filter
```

**INSERT** - Add new data
```sql
INSERT INTO users (name, email, role) 
VALUES ('John', 'john@email.com', 'student')
```

**UPDATE** - Change existing data
```sql
UPDATE users SET name = 'Johnny' WHERE user_id = 1
```

**DELETE** - Remove data
```sql
DELETE FROM users WHERE user_id = 1
```

**JOIN** - Combine tables
```sql
SELECT u.name, c.course_name
FROM users u
JOIN enrollments e ON u.user_id = e.student_id
JOIN courses c ON e.course_id = c.course_id
```

---

## 📝 PHP BASICS YOU NEED TO KNOW

### Variables
```php
$name = "John";          // String (text)
$age = 20;               // Integer (number)
$gpa = 3.5;              // Float (decimal)
$isStudent = true;       // Boolean (true/false)
$courses = [];           // Array (list)
```

### Arrays
```php
// Indexed array
$fruits = ['apple', 'banana', 'orange'];
echo $fruits[0];  // apple

// Associative array (like a dictionary)
$user = [
    'name' => 'John',
    'age' => 20,
    'email' => 'john@email.com'
];
echo $user['name'];  // John
```

### If Statements
```php
if ($age >= 18) {
    echo "Adult";
} elseif ($age >= 13) {
    echo "Teenager";
} else {
    echo "Child";
}
```

### Loops
```php
// For loop
for ($i = 0; $i < 5; $i++) {
    echo $i;  // 0 1 2 3 4
}

// Foreach loop (for arrays)
foreach ($fruits as $fruit) {
    echo $fruit;
}
```

### Functions
```php
function greet($name) {
    return "Hello, " . $name;
}

echo greet("John");  // Hello, John
```

---

## 🚀 HOW TO RUN YOUR PROJECT

### Step 1: Setup Database
1. Go to phpMyAdmin on Ashesi server
2. Click "New" to create database
3. Name it `attendance_db`
4. Click "Import" tab
5. Choose `schema.sql` file
6. Click "Go"

### Step 2: Update config.php
```php
define('DB_HOST', 'localhost');           // Your server
define('DB_NAME', 'attendance_db');       // Database name
define('DB_USER', 'your_username');       // Your username
define('DB_PASS', 'your_password');       // Your password
```

### Step 3: Upload Files
Upload all PHP files to your server:
- config.php
- index.php
- login_handler.php
- register.php
- register_handler.php
- student_dashboard.php
- logout.php

### Step 4: Test
1. Go to: `http://your-server/index.php`
2. Click "Register here"
3. Create an account
4. Login with your credentials
5. You should see the student dashboard!

---

## 🐛 COMMON ERRORS AND FIXES

### Error: "Connection failed"
**Problem:** Can't connect to database
**Fix:** Check DB credentials in config.php

### Error: "Undefined variable"
**Problem:** Using variable before defining it
**Fix:** Make sure variable exists before using it
```php
if (isset($variable)) {
    echo $variable;
}
```

### Error: "Call to undefined function"
**Problem:** Missing require_once statement
**Fix:** Add at top of file:
```php
require_once 'config.php';
```

### Error: "Headers already sent"
**Problem:** Output before header() function
**Fix:** Don't echo anything before header()
```php
// WRONG
echo "Hello";
header("Location: page.php");

// RIGHT
header("Location: page.php");
exit();
```

---

## 📚 WHAT TO LEARN NEXT

1. **PHP Basics**
   - w3schools.com/php
   - php.net/manual

2. **SQL**
   - w3schools.com/sql
   - sqlzoo.net

3. **Security**
   - OWASP Top 10
   - PHP security best practices

4. **Advanced Features**
   - File uploads
   - Email sending
   - AJAX (updating page without reload)
   - API creation

---

## ✅ CHECKLIST FOR YOUR ASSIGNMENT

- [ ] Database created with all tables
- [ ] Can register new users
- [ ] Can login successfully
- [ ] Student dashboard shows courses
- [ ] Attendance records display correctly
- [ ] Lectures and labs are visually different
- [ ] Can logout successfully
- [ ] Mobile responsive
- [ ] No security vulnerabilities
- [ ] Code has comments
- [ ] Documentation complete

---

**Need Help?** 
- Read error messages carefully
- Use var_dump() to debug variables
- Check browser console for JavaScript errors
- Ask your instructor or classmates

**Good luck with your project! 🎓**

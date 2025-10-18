# 🚀 QUICK START GUIDE (5 MINUTES)

## Follow These Steps EXACTLY:

### STEP 1: Setup Database (2 minutes)

1. **Open phpMyAdmin** on your Ashesi server
   - Login with your school credentials

2. **Find Your Database**
   - Your database is already created by the school
   - Look in the left sidebar for: `webtech_2025A_eyram_awoye` (or similar)
   - Click on it to select it

3. **Import Schema**
   - Click "Import" tab at top
   - Click "Choose File"
   - Select `schema.sql` from your computer
   - Scroll down, click "Go"
   - Wait for "Import has been successfully finished"

4. **Verify Tables Created**
   - You should see 6 NEW tables:
     - users
     - courses
     - sessions
     - attendance
     - enrollments
     - issues

---

### STEP 2: Configure Database Connection (1 minute)

1. **Open `config.php`** in a text editor

2. **Find lines 18-21** and update:

```php
define('DB_HOST', 'localhost');                      // Usually 'localhost'
define('DB_NAME', 'webtech_2025A_eyram_awoye');     // YOUR database name
define('DB_USER', 'your_username_here');             // YOUR Ashesi username
define('DB_PASS', 'your_password_here');             // YOUR Ashesi password
```

3. **Save the file**

---

### STEP 3: Upload Files to Server (1 minute)

**Upload these 9 files** to your server folder:
- ✅ config.php
- ✅ index.php
- ✅ login_handler.php
- ✅ register.php
- ✅ register_handler.php
- ✅ student_dashboard.php
- ✅ report_issue.php
- ✅ logout.php
- ✅ schema.sql (keep for reference)

**Where to upload:**
```
/public_html/activity_03/individual/
or
/www/activity_03/individual/
or wherever your professor told you
```

---

### STEP 4: Test Your Site (1 minute)

1. **Open your website:**
   ```
   http://webtech.ashesi.edu.gh/yourname/activity_03/individual/index.php
   ```

2. **Test with Sample User:**
   - Email: `john.doe@ashesi.edu.gh`
   - Password: `Password123!`
   - Click "Sign In"

3. **You should see:**
   - ✅ Student dashboard
   - ✅ List of courses
   - ✅ Attendance records
   - ✅ Statistics

4. **Test Registration:**
   - Click "Logout"
   - Click "Register here"
   - Create your own account
   - Login with your new account

---

## 🎉 IT WORKS! What Now?

### Customize for Your Needs:

1. **Change Colors:**
   - Open any file
   - Find `bg-red-600` (background color)
   - Replace with `bg-blue-600`, `bg-green-600`, etc.

2. **Change School Name:**
   - Find "Ashesi" in files
   - Replace with your school name

3. **Add Your Logo:**
   - Replace `<i class="fas fa-graduation-cap">` with `<img src="logo.png">`

---

## ⚠️ TROUBLESHOOTING (If Something Goes Wrong)

### Problem 1: Blank White Page
**Cause:** PHP error
**Fix:** 
```php
// Add this to top of config.php (line 2)
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Problem 2: "Connection failed"
**Cause:** Wrong database credentials
**Fix:** 
- Check DB_USER and DB_PASS in config.php
- Make sure database name is exactly `attendance_db`

### Problem 3: "Table doesn't exist"
**Cause:** Schema not imported
**Fix:** 
- Go back to Step 1
- Select your database `webtech_2025A_eyram_awoye` in phpMyAdmin
- Import schema.sql again

### Problem 4: Can't Login
**Cause:** Sample data not inserted
**Fix:**
- Check phpMyAdmin
- Click on "users" table
- Should see 5 users
- If empty, re-import schema.sql

### Problem 5: Page Shows PHP Code
**Cause:** Server not running PHP
**Fix:**
- Make sure file extension is `.php` not `.html`
- Contact server admin

---

## 📋 WHAT EACH FILE DOES (SIMPLE)

| File | Purpose | When It's Used |
|------|---------|----------------|
| **config.php** | Connects to database | Every other file includes this |
| **index.php** | Login page | When user first visits site |
| **login_handler.php** | Checks password | When user clicks "Sign In" |
| **register.php** | Registration form | When user clicks "Register" |
| **register_handler.php** | Creates account | When user submits registration |
| **student_dashboard.php** | Main page after login | After successful login |
| **report_issue.php** | Report attendance problems | When user clicks "Report Issue" |
| **logout.php** | Logs user out | When user clicks "Logout" |
| **schema.sql** | Creates database tables | Import once in phpMyAdmin |

---

## 🎯 TESTING CHECKLIST

Test each feature:

- [ ] **Visit index.php** - Shows login form
- [ ] **Click "Register here"** - Shows registration form
- [ ] **Fill registration form** - Creates new account
- [ ] **Login with test account** - Shows dashboard
- [ ] **Dashboard shows courses** - CS101, CS201, etc.
- [ ] **Attendance records visible** - Dates, times, status
- [ ] **Lectures and labs different colors** - Blue vs Orange
- [ ] **Statistics correct** - Shows %, present, absent, late
- [ ] **Click "Report Issue"** - Shows issue form
- [ ] **Submit issue** - Shows success message
- [ ] **Click "Logout"** - Returns to login page
- [ ] **Try wrong password** - Shows error message
- [ ] **Test on mobile** - Everything responsive

---

## 📱 MOBILE TESTING

**On your phone:**

1. Visit your website URL
2. Should automatically adjust to screen size
3. All buttons should be easy to tap
4. Text should be readable
5. Tables should scroll horizontally if needed

**Test these devices:**
- iPhone (Safari)
- Android (Chrome)
- Tablet (any browser)

---

## 🔐 DEFAULT ACCOUNTS (For Testing)

### Students:
```
Email: john.doe@ashesi.edu.gh
Password: Password123!

Email: alice.johnson@ashesi.edu.gh
Password: Password123!

Email: bob.wilson@ashesi.edu.gh
Password: Password123!
```

### Faculty:
```
Email: jane.smith@ashesi.edu.gh
Password: Password123!

Email: michael.brown@ashesi.edu.gh
Password: Password123!
```

**⚠️ IMPORTANT:** 
- Change these passwords in production!
- These are for TESTING ONLY

---

## 📚 KEY FILES TO UNDERSTAND (As a Beginner)

### Start with these 3 files:

1. **config.php** (Lines 1-50)
   - How to connect to database
   - What is PDO
   - How sessions work

2. **index.php** (Lines 1-100)
   - How to display HTML with PHP
   - How forms work
   - How to show error messages

3. **login_handler.php** (Lines 1-100)
   - How to process form data
   - How to query database
   - How to verify passwords
   - How to create sessions

**DON'T TRY TO UNDERSTAND EVERYTHING AT ONCE!**
Read one file at a time, follow the comments.

---

## 💡 BEGINNER TIPS

### 1. How to Debug:
```php
// Add this to see what's in a variable:
var_dump($variable);
die(); // Stop here

// Example:
var_dump($_POST);
die();
```

### 2. How to See Errors:
```php
// Add to top of config.php:
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 3. How to Test Database Queries:
- Copy SQL query from PHP
- Paste in phpMyAdmin "SQL" tab
- Replace `:placeholders` with actual values
- Click "Go"

### 4. How to Check if Session Works:
```php
// Add anywhere in student_dashboard.php:
echo "User ID: " . $_SESSION['user_id'];
echo "Name: " . $_SESSION['name'];
```

---

## 🎓 UNDERSTANDING THE FLOW

### User Registration Flow:
```
1. User visits register.php
   ↓
2. User fills form
   ↓
3. Form submits to register_handler.php
   ↓
4. Handler validates data
   ↓
5. Handler checks if email exists
   ↓
6. Handler hashes password
   ↓
7. Handler inserts into database
   ↓
8. Redirects to index.php with success message
```

### User Login Flow:
```
1. User visits index.php
   ↓
2. User enters email/password
   ↓
3. Form submits to login_handler.php
   ↓
4. Handler finds user in database
   ↓
5. Handler verifies password
   ↓
6. Handler creates session
   ↓
7. Redirects to student_dashboard.php
```

### Viewing Dashboard Flow:
```
1. Browser loads student_dashboard.php
   ↓
2. PHP checks if user is logged in
   ↓
3. PHP queries database for courses
   ↓
4. PHP queries database for attendance
   ↓
5. PHP calculates statistics
   ↓
6. PHP generates HTML with data
   ↓
7. Browser displays page
```

---

## 📞 STILL STUCK?

### Check These:

1. **File Permissions**
   - All .php files: 644
   - Folders: 755

2. **File Names**
   - Must be exactly: `index.php` not `Index.php`
   - Case sensitive on Linux servers

3. **PHP Version**
   - Needs PHP 7.4 or higher
   - Check with: `<?php phpinfo(); ?>`

4. **Database Connection**
   - Test with this simple file:
   ```php
   <?php
   require_once 'config.php';
   echo "Connected successfully!";
   ?>
   ```

---

## ✅ FINAL CHECKLIST

Before showing to professor:

- [ ] All files uploaded
- [ ] Database imported
- [ ] Config.php updated with YOUR credentials
- [ ] Can register new user
- [ ] Can login
- [ ] Dashboard shows data
- [ ] Can report issues
- [ ] Can logout
- [ ] Tested on mobile
- [ ] No PHP errors showing
- [ ] Professional appearance
- [ ] README.md updated with your name
- [ ] Screenshots taken

---

## 🎉 YOU'RE DONE!

**Your project is complete and working!**

### What You've Accomplished:
✅ Built a complete web application
✅ Learned PHP fundamentals
✅ Implemented database operations
✅ Added security features
✅ Created responsive design
✅ Wrote clean, commented code

### You Can Now:
✅ Create login systems
✅ Work with databases
✅ Build dynamic websites
✅ Implement CRUD operations
✅ Secure web applications

**CONGRATULATIONS! 🎓🌟**

---

**Time to Complete:** 5 minutes
**Difficulty:** Beginner-Friendly
**Support:** All files heavily commented
**Status:** Production Ready

**GOOD LUCK WITH YOUR ASSIGNMENT!** 🚀

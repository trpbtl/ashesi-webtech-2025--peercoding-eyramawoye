# 🚀 QUICK START GUIDE - Activity 04

## ⚡ 5-Minute Setup

### Step 1: Start Your Server (30 seconds)
1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL**
3. Wait for green indicators

### Step 2: Setup Database (2 minutes)
1. Open browser: `http://localhost/phpmyadmin`
2. Click **SQL** tab
3. Copy ALL content from `schema.sql`
4. Paste and click **Go**
5. You should see: ✅ 4 tables created, sample data inserted

### Step 3: Configure Password (30 seconds)
1. Open `config.php` in text editor
2. Find line 13: `define('DB_PASS', 'your_actual_password_here');`
3. Replace with your MySQL password (usually empty or 'root')
4. Save file

### Step 4: Test! (2 minutes)
1. Open browser: `http://localhost/activity_04/individual/`
2. You should see the login page
3. Try test credentials:
   - **Student:** `john.doe@ashesi.edu.gh` / `Password123!`
   - **Faculty:** `jane.smith@ashesi.edu.gh` / `Password123!`

---

## ✅ Quick Test Checklist

### Test Authentication:
- [ ] Can register new student account
- [ ] Can register new faculty account
- [ ] Can login with test credentials
- [ ] Redirects to dashboard after login
- [ ] Cannot access dashboard without login
- [ ] Can logout successfully

### Test Student Features:
- [ ] Student sees enrolled courses on dashboard
- [ ] Can click "Browse Courses" button
- [ ] Can see available courses
- [ ] Can request to join a course
- [ ] Request shows as "Pending"
- [ ] Cannot request same course twice

### Test Faculty Features:
- [ ] Faculty sees their courses on dashboard
- [ ] Can click "Create Course" button
- [ ] Can create new course (e.g., CS999)
- [ ] Course appears on dashboard
- [ ] Can click "Manage Requests"
- [ ] Can see pending requests from students
- [ ] Can approve a request
- [ ] Student gets enrolled after approval
- [ ] Can reject a request with comment

### Test Complete Workflow:
1. [ ] Register as student (`test.student@ashesi.edu.gh`)
2. [ ] Login as student
3. [ ] Browse courses
4. [ ] Request to join "CS101"
5. [ ] Logout
6. [ ] Login as faculty (`jane.smith@ashesi.edu.gh`)
7. [ ] Go to "Manage Requests"
8. [ ] See the request from test.student
9. [ ] Approve the request
10. [ ] Logout
11. [ ] Login as student again
12. [ ] See CS101 in "My Enrolled Courses"

---

## 🎯 Demo Script (For Presentation)

### Part 1: Student Registration & Browse (2 min)
```
1. Show registration page
2. Register new student: demo.student@ashesi.edu.gh
3. Login with new account
4. Show dashboard with enrolled courses (empty initially)
5. Click "Browse Courses"
6. Show available courses
7. Request to join "CS101"
8. Show "Pending" status
```

### Part 2: Faculty Course Creation (2 min)
```
1. Logout, login as faculty (jane.smith@ashesi.edu.gh)
2. Show faculty dashboard
3. Click "Create Course"
4. Create course: 
   - Code: WEB301
   - Name: Advanced Web Technologies
   - Semester: Spring
   - Year: 2025
5. Show course appears on dashboard
```

### Part 3: Request Approval (2 min)
```
1. Click "Manage Requests" (should show 1 pending)
2. Show student request for CS101
3. Show student details (name, email, ID)
4. Click "Approve"
5. Show success message
6. Show request disappears from pending
7. Show in "Recent Actions" as approved
```

### Part 4: Student Sees Enrollment (1 min)
```
1. Logout, login as student again
2. Show CS101 now in "My Enrolled Courses"
3. Show request no longer in "Pending Requests"
4. Done! ✅
```

---

## 🐛 Troubleshooting

### "Database connection failed"
**Fix:** Check password in `config.php` line 13

### "Table doesn't exist"
**Fix:** Run `schema.sql` in phpMyAdmin

### "Invalid email or password"
**Fix:** 
1. Check you're using test credentials correctly
2. Or register a new account

### "CSRF token invalid"
**Fix:** Clear browser cookies and try again

### "Headers already sent"
**Fix:** Make sure no spaces before `<?php` in any file

### Page is blank/white
**Fix:** 
1. Check PHP error logs in XAMPP
2. Make sure all files are uploaded
3. Check file permissions

---

## 📱 Testing on Different Devices

### Desktop Browser:
- Chrome ✅
- Firefox ✅
- Edge ✅
- Safari ✅

### Mobile:
- Responsive design works on all screen sizes
- Test on mobile view in browser DevTools

---

## 🎓 What to Show Your Instructor

1. **Login Page** - Show security features
2. **Registration** - Show validation working
3. **Student Dashboard** - Show enrolled courses
4. **Browse Courses** - Show request functionality
5. **Faculty Dashboard** - Show course creation
6. **Manage Requests** - Show approval system
7. **Database** - Show course_requests table
8. **Code** - Show password hashing, CSRF tokens

---

## 📊 Key Metrics to Highlight

- **Files Created:** 13 PHP files
- **Database Tables:** 4 tables
- **Security Features:** 6+ implementations
- **User Roles:** 2 (Student, Faculty)
- **Complete Workflows:** 3+ end-to-end flows
- **Lines of Code:** ~2000+ lines
- **Time to Complete:** Full implementation

---

## ✨ Bonus Points Features

These go beyond requirements:
- Request history tracking
- Rejection with comments
- Enrollment statistics
- Request grouping
- Modern responsive UI
- Confirmation modals
- Real-time counts
- Professional design

---

**Ready to demo? Follow the Demo Script above!** 🚀

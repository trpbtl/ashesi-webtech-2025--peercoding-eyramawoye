# 🧪 COMPREHENSIVE TESTING GUIDE

## Test User Accounts

All passwords: **Password123!**

### Students:
| Name | Email | Ashesi ID |
|------|-------|-----------|
| John Doe | john.doe@ashesi.edu.gh | 2026001 |
| Alice Johnson | alice.johnson@ashesi.edu.gh | 2026002 |
| Bob Wilson | bob.wilson@ashesi.edu.gh | 2026003 |

### Faculty:
| Name | Email | Ashesi ID |
|------|-------|-----------|
| Dr. Jane Smith | jane.smith@ashesi.edu.gh | FAC001 |
| Prof. Michael Brown | michael.brown@ashesi.edu.gh | FAC002 |

---

## 🔐 SECTION 1: Authentication Testing

### Test 1.1: Registration Validation
**Steps:**
1. Go to registration page
2. Try submitting empty form
3. Try invalid email (test@test)
4. Try weak password (test123)
5. Try mismatched passwords
6. Fill valid data and submit

**Expected Results:**
- ✅ Empty form: Shows error "Please fill in all required fields"
- ✅ Invalid email: Browser validation catches it
- ✅ Weak password: Shows "Password must be at least 8 characters..."
- ✅ Mismatched: Shows "Passwords do not match"
- ✅ Valid submission: Redirects to login with success message

### Test 1.2: Duplicate Registration
**Steps:**
1. Try registering with `john.doe@ashesi.edu.gh`
2. Try registering with Ashesi ID `2026001`

**Expected Results:**
- ✅ Email exists: Shows "This email is already registered"
- ✅ ID exists: Shows "This Ashesi ID is already registered"

### Test 1.3: Login Validation
**Steps:**
1. Try logging in with empty fields
2. Try invalid email
3. Try wrong password
4. Try correct credentials

**Expected Results:**
- ✅ Empty: Shows "Please fill in all fields"
- ✅ Invalid email: Shows "Invalid email format"
- ✅ Wrong password: Shows "Invalid email or password"
- ✅ Correct: Redirects to dashboard with welcome message

### Test 1.4: Session Management
**Steps:**
1. Login successfully
2. Try accessing login page (index.php)
3. Logout
4. Try accessing dashboard.php

**Expected Results:**
- ✅ After login: Redirected to dashboard (can't access login)
- ✅ After logout: Redirected to login page
- ✅ Without login: Dashboard redirects to login

---

## 👨‍🎓 SECTION 2: Student Features Testing

### Test 2.1: Student Dashboard Display
**Login as:** `john.doe@ashesi.edu.gh`

**Verify:**
- [ ] Welcome message shows "Welcome back, John!"
- [ ] Shows "Student Portal" in navigation
- [ ] Three action cards visible (Browse, My Courses, Pending)
- [ ] Enrolled courses section shows courses
- [ ] Navigation has Dashboard and Logout

### Test 2.2: Browse Courses
**Steps:**
1. Login as student
2. Click "Browse Courses"
3. View available courses

**Expected Results:**
- ✅ Shows courses not enrolled in
- ✅ Each course shows: code, name, semester, faculty
- ✅ "Request to Join" button visible
- ✅ Courses already enrolled don't show

### Test 2.3: Request to Join Course
**Steps:**
1. Browse courses
2. Click "Request to Join" on CS101
3. Check pending requests section

**Expected Results:**
- ✅ Success message: "Course join request submitted successfully!"
- ✅ Request appears in "Pending Requests" section
- ✅ Shows as "Awaiting Approval"
- ✅ Course no longer in available list

### Test 2.4: Duplicate Request Prevention
**Steps:**
1. After requesting CS101
2. Go back to browse courses
3. Try to request CS101 again

**Expected Results:**
- ✅ CS101 should not appear in available courses
- ✅ Cannot make duplicate request

### Test 2.5: View Enrolled Courses
**Steps:**
1. Login as john.doe (sample data has enrollments)
2. Check "My Enrolled Courses" section

**Expected Results:**
- ✅ Shows courses student is enrolled in
- ✅ Each course shows: code, name, semester, faculty
- ✅ Counts correctly displayed

---

## 👨‍🏫 SECTION 3: Faculty Features Testing

### Test 3.1: Faculty Dashboard Display
**Login as:** `jane.smith@ashesi.edu.gh`

**Verify:**
- [ ] Welcome message shows "Welcome back, Jane!"
- [ ] Shows "Faculty Portal" in navigation
- [ ] Three action cards (Create, Pending, My Courses)
- [ ] Shows courses faculty is teaching
- [ ] Shows enrollment count per course

### Test 3.2: Create Course - Validation
**Steps:**
1. Click "Create Course"
2. Try submitting empty form
3. Try invalid course code (test123)
4. Try duplicate course code (CS101)

**Expected Results:**
- ✅ Empty: Shows "Please fill in all required fields"
- ✅ Invalid code: Browser validation catches it (pattern mismatch)
- ✅ Duplicate: Shows "Course code already exists"

### Test 3.3: Create Course - Success
**Steps:**
1. Fill valid course details:
   - Code: TEST999
   - Name: Test Course
   - Semester: Fall
   - Year: 2025
   - Description: Test description
2. Submit

**Expected Results:**
- ✅ Success message: "Course created successfully!"
- ✅ Redirected to dashboard
- ✅ New course appears in "My Courses"
- ✅ Shows "0 student(s) enrolled"

### Test 3.4: View Pending Requests
**Prerequisites:** Have a student request to join faculty's course

**Steps:**
1. Login as faculty
2. Click "Manage Requests" or check pending count

**Expected Results:**
- ✅ Pending count shows correct number
- ✅ Requests grouped by course
- ✅ Shows student details (name, email, ID)
- ✅ Shows request timestamp
- ✅ Approve and Reject buttons visible

### Test 3.5: Approve Request
**Steps:**
1. View pending requests
2. Click "Approve" on a student request
3. Confirm approval

**Expected Results:**
- ✅ Success message: "Approved [Student] for [Course]"
- ✅ Request removed from pending
- ✅ Appears in "Recent Actions" as approved
- ✅ Student added to enrollments table
- ✅ Course enrollment count increases

### Test 3.6: Reject Request
**Steps:**
1. View pending requests
2. Click "Reject" on a student request
3. Optionally add comment: "Class is full"
4. Submit rejection

**Expected Results:**
- ✅ Modal opens with student/course info
- ✅ Can add optional comment
- ✅ Success message: "Rejected request from [Student] for [Course]"
- ✅ Request removed from pending
- ✅ Appears in "Recent Actions" as rejected
- ✅ Comment saved and visible

---

## 🔄 SECTION 4: Complete Workflow Testing

### Test 4.1: End-to-End Course Request Flow

**Part A: Student Side**
1. Register new student: `workflow.test@ashesi.edu.gh`
2. Login with new account
3. Dashboard shows no enrolled courses
4. Browse courses
5. Request to join "CS201"
6. See pending status
7. Logout

**Part B: Faculty Side**
8. Login as `jane.smith@ashesi.edu.gh`
9. See pending request count = 1
10. Go to "Manage Requests"
11. See request from workflow.test
12. Approve request
13. See success message
14. Check "My Courses" - CS201 enrollment count increased
15. Logout

**Part C: Verify Student**
16. Login as workflow.test@ashesi.edu.gh
17. Dashboard now shows CS201 in enrolled courses
18. Browse courses - CS201 no longer available
19. Pending requests = 0

**Expected:** ✅ Complete flow works seamlessly

### Test 4.2: Multiple Course Requests
**Steps:**
1. Login as student
2. Request to join 3 different courses
3. Check pending count = 3
4. Login as different faculty members
5. Each approves their course request

**Expected:** ✅ All requests processed correctly

### Test 4.3: Mixed Approvals and Rejections
**Steps:**
1. Student requests 4 courses
2. Faculty approves 2
3. Faculty rejects 2 with different comments
4. Student checks dashboard

**Expected:**
- ✅ 2 courses in enrolled
- ✅ 2 rejections in history
- ✅ Comments visible for rejections

---

## 🔒 SECTION 5: Security Testing

### Test 5.1: Password Security
**Verify:**
1. Check database - passwords are hashed
2. Try weak passwords - rejected
3. Password shown as dots in input
4. Toggle password visibility works

**Expected:**
- ✅ Passwords not visible in database
- ✅ Weak passwords rejected
- ✅ Password toggle works

### Test 5.2: CSRF Protection
**Steps:**
1. Inspect any form
2. Look for hidden csrf_token input
3. Try submitting form without token (manually)

**Expected:**
- ✅ All forms have CSRF token
- ✅ Submission fails without valid token

### Test 5.3: SQL Injection Prevention
**Steps:**
1. Try SQL in email field: `test' OR '1'='1`
2. Try SQL in course code: `CS101'; DROP TABLE users;--`

**Expected:**
- ✅ Treated as literal string
- ✅ No SQL executed
- ✅ Error or no results

### Test 5.4: XSS Prevention
**Steps:**
1. Try `<script>alert('XSS')</script>` in name field
2. Try `<img src=x onerror=alert('XSS')>` in description

**Expected:**
- ✅ Scripts don't execute
- ✅ Rendered as text
- ✅ HTML escaped

### Test 5.5: Role-Based Access Control
**Steps:**
1. Login as student
2. Manually go to: `create_course.php`
3. Manually go to: `manage_requests.php`
4. Login as faculty
5. Manually go to: `browse_courses.php`

**Expected:**
- ✅ Students redirected from faculty pages
- ✅ Faculty redirected from student pages
- ✅ Error message shown

---

## 📊 SECTION 6: Database Testing

### Test 6.1: Check Tables Exist
```sql
SHOW TABLES;
```
**Expected:** users, courses, course_requests, enrollments

### Test 6.2: Check Sample Data
```sql
SELECT * FROM users;
SELECT * FROM courses;
```
**Expected:** 5 users, 3 courses

### Test 6.3: Test Course Request Creation
**After student requests course, check:**
```sql
SELECT * FROM course_requests WHERE status = 'pending';
```
**Expected:** Shows the request

### Test 6.4: Test Approval Creates Enrollment
**After faculty approves, check:**
```sql
SELECT * FROM enrollments WHERE student_id = X AND course_id = Y;
```
**Expected:** Enrollment exists

### Test 6.5: Test No Duplicate Enrollments
**Try to manually insert duplicate:**
```sql
INSERT INTO enrollments (student_id, course_id) VALUES (1, 1);
```
**Expected:** Error - unique constraint violation

---

## 🎨 SECTION 7: UI/UX Testing

### Test 7.1: Responsive Design
**Steps:**
1. Open site in browser
2. Open DevTools (F12)
3. Toggle device toolbar
4. Test on Mobile (375px)
5. Test on Tablet (768px)
6. Test on Desktop (1920px)

**Expected:**
- ✅ Layout adapts to screen size
- ✅ No horizontal scrolling
- ✅ Buttons remain clickable
- ✅ Text readable

### Test 7.2: Browser Compatibility
**Test on:**
- [ ] Chrome
- [ ] Firefox
- [ ] Edge
- [ ] Safari

**Expected:** ✅ Works on all browsers

### Test 7.3: User Feedback
**Verify:**
- [ ] Success messages are green
- [ ] Error messages are red
- [ ] Pending status is yellow
- [ ] Icons are appropriate
- [ ] Loading states visible

### Test 7.4: Navigation
**Verify:**
- [ ] Dashboard link works
- [ ] Logout link works
- [ ] Back buttons work
- [ ] Breadcrumbs clear
- [ ] No broken links

---

## ✅ FINAL CHECKLIST

### Functionality:
- [ ] All authentication features work
- [ ] Students can browse and request courses
- [ ] Faculty can create courses
- [ ] Faculty can approve/reject requests
- [ ] Dashboard shows correct data
- [ ] All forms validate properly

### Security:
- [ ] Passwords are hashed
- [ ] CSRF tokens present
- [ ] SQL injection prevented
- [ ] XSS prevented
- [ ] Role-based access enforced

### Database:
- [ ] All tables created
- [ ] Sample data loaded
- [ ] Foreign keys working
- [ ] Unique constraints enforced

### UI/UX:
- [ ] Responsive on all devices
- [ ] Colors and styling consistent
- [ ] User feedback clear
- [ ] Navigation intuitive

### Code Quality:
- [ ] No PHP errors
- [ ] No console errors
- [ ] Code well-commented
- [ ] File structure organized

---

## 🎯 Pass/Fail Criteria

### PASS if:
- ✅ All authentication tests pass
- ✅ Student can request and enroll in courses
- ✅ Faculty can create and manage courses
- ✅ Security measures in place
- ✅ No critical bugs

### FAIL if:
- ❌ Cannot login/register
- ❌ Requests not working
- ❌ Passwords not hashed
- ❌ SQL injection possible
- ❌ Critical functionality broken

---

## 📝 Test Results Template

```
Test Date: _________________
Tester: ____________________

Section 1 (Authentication): ☐ Pass ☐ Fail
Section 2 (Student): ☐ Pass ☐ Fail
Section 3 (Faculty): ☐ Pass ☐ Fail
Section 4 (Workflows): ☐ Pass ☐ Fail
Section 5 (Security): ☐ Pass ☐ Fail
Section 6 (Database): ☐ Pass ☐ Fail
Section 7 (UI/UX): ☐ Pass ☐ Fail

Overall Result: ☐ PASS ☐ FAIL

Notes:
_________________________________
_________________________________
_________________________________
```

---

**Run all tests before submitting!** ✅

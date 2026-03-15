# Task 4.1: Manual Testing Guide - Full Registration Flow from Frontend

**Task**: Test full registration flow from frontend  
**Status**: 🔄 IN PROGRESS  
**Date**: 2024  
**Tester**: [To be filled by tester]

---

## Overview

This manual testing guide validates the complete registration and email verification flow from the Vercel-deployed frontend to ensure:
- Registration succeeds without CORS errors
- Verification emails are received with correct production URLs
- Email verification links work correctly
- Login succeeds after verification
- Multiple Vercel deployment URLs are supported

---

## Prerequisites

### Required Access
- [ ] Access to Vercel-deployed frontend: `https://frontend-ten-psi-9hutf2paf3.vercel.app`
- [ ] Access to email account with `@student.laverdad.edu.ph` domain
- [ ] Browser with Developer Tools (Chrome/Firefox recommended)
- [ ] Access to backend logs (Render dashboard) - optional but helpful

### Test Environment
- **Frontend URL**: `https://frontend-ten-psi-9hutf2paf3.vercel.app`
- **Backend URL**: `https://mobile-backend-84tg.onrender.com`
- **Email Domain**: `@student.laverdad.edu.ph` (required)

### Browser Setup
1. Open browser in **Incognito/Private mode** (to avoid cached data)
2. Open **Developer Tools** (F12)
3. Navigate to **Console** tab (to monitor CORS errors)
4. Navigate to **Network** tab (to inspect requests)

---

## Test Case 1: Registration from Primary Vercel URL

### Objective
Verify registration succeeds from the primary production Vercel deployment without CORS errors.

### Steps

#### 1.1 Navigate to Registration Page
1. Open browser in incognito mode
2. Navigate to: `https://frontend-ten-psi-9hutf2paf3.vercel.app/register`
3. Open Developer Tools (F12) → Console tab
4. Open Network tab and filter by "Fetch/XHR"

**Expected**: Registration page loads successfully

#### 1.2 Fill Registration Form
1. Enter test user details:
   - **Name**: `Test User [timestamp]` (e.g., "Test User 20240315143000")
   - **Email**: `testuser[timestamp]@student.laverdad.edu.ph` (e.g., "testuser20240315143000@student.laverdad.edu.ph")
   - **Password**: `TestPassword123!`
   - **Confirm Password**: `TestPassword123!`

**Note**: Use unique email for each test run to avoid duplicate email errors.

#### 1.3 Submit Registration
1. Click "Register" or "Sign Up" button
2. **Monitor Console tab** for any errors
3. **Monitor Network tab** for the registration request

**Expected Results**:
- [ ] ✅ No CORS errors in Console
- [ ] ✅ Network tab shows POST request to `/api/auth/register`
- [ ] ✅ Request status: `201 Created` or `200 OK`
- [ ] ✅ Response includes user data (name, email, id)
- [ ] ✅ No "Access to fetch has been blocked by CORS policy" errors
- [ ] ✅ Response headers include `Access-Control-Allow-Origin: https://frontend-ten-psi-9hutf2paf3.vercel.app`

**Actual Results**:
```
[To be filled by tester]
- Console errors: 
- Network request status: 
- Response data: 
- CORS headers present: 
```

#### 1.4 Check for Success Message
1. Observe the UI after registration submission

**Expected Results**:
- [ ] ✅ Success message displayed (e.g., "Registration successful! Please check your email to verify your account.")
- [ ] ✅ User is redirected to login page or verification pending page
- [ ] ✅ No error messages displayed

**Actual Results**:
```
[To be filled by tester]
- Success message: 
- Redirect behavior: 
```

---

## Test Case 2: Email Verification Link

### Objective
Verify that the verification email is received with the correct production URL and the link works correctly.

### Steps

#### 2.1 Check Email Inbox
1. Open email client for the test email address
2. Wait up to 2 minutes for verification email
3. Check spam/junk folder if not in inbox

**Expected Results**:
- [ ] ✅ Verification email received within 2 minutes
- [ ] ✅ Email sender: `rolandomajait1@gmail.com` (or configured sender)
- [ ] ✅ Email subject contains "Verify" or "Email Verification"

**Actual Results**:
```
[To be filled by tester]
- Email received: Yes/No
- Time to receive: 
- Sender: 
- Subject: 
```

#### 2.2 Inspect Verification Link
1. Open the verification email
2. **DO NOT CLICK THE LINK YET**
3. Hover over the verification link or view email source
4. Copy the verification URL

**Expected Results**:
- [ ] ✅ Verification URL starts with: `https://frontend-ten-psi-9hutf2paf3.vercel.app/verify-email?token=`
- [ ] ✅ URL is NOT `http://localhost:5173/verify-email?token=...`
- [ ] ✅ Token parameter is present and appears to be a valid hash (64 characters)
- [ ] ✅ No double slashes in URL (e.g., not `//verify-email`)

**Actual Results**:
```
[To be filled by tester]
- Full verification URL: 
- Frontend domain in URL: 
- Token present: Yes/No
- Token format: 
```

#### 2.3 Click Verification Link
1. Click the verification link in the email
2. **Monitor Console tab** for errors
3. **Monitor Network tab** for verification request

**Expected Results**:
- [ ] ✅ Browser navigates to verification page
- [ ] ✅ Network tab shows GET request to `/api/auth/verify-email?token=...` or similar
- [ ] ✅ Request status: `200 OK`
- [ ] ✅ No CORS errors in Console
- [ ] ✅ Success message displayed (e.g., "Email verified successfully!")

**Actual Results**:
```
[To be filled by tester]
- Verification request status: 
- Console errors: 
- Success message: 
- Redirect behavior: 
```

#### 2.4 Verify Email Status in Database (Optional)
If you have database access:
1. Query the `users` table for the test user
2. Check `email_verified_at` column

**Expected**: `email_verified_at` is set to current timestamp (not NULL)

**Actual Results**:
```
[To be filled by tester]
- email_verified_at value: 
```

---

## Test Case 3: Login After Verification

### Objective
Verify that login succeeds after email verification.

### Steps

#### 3.1 Navigate to Login Page
1. Navigate to: `https://frontend-ten-psi-9hutf2paf3.vercel.app/login`
2. Ensure Developer Tools are still open

#### 3.2 Submit Login Credentials
1. Enter the test user credentials:
   - **Email**: [email used in registration]
   - **Password**: `TestPassword123!`
2. Click "Login" or "Sign In" button
3. **Monitor Console and Network tabs**

**Expected Results**:
- [ ] ✅ No CORS errors in Console
- [ ] ✅ Network tab shows POST request to `/api/auth/login`
- [ ] ✅ Request status: `200 OK`
- [ ] ✅ Response includes authentication token
- [ ] ✅ User is redirected to dashboard or home page
- [ ] ✅ No "Please verify your email" error message

**Actual Results**:
```
[To be filled by tester]
- Login request status: 
- Console errors: 
- Authentication token received: Yes/No
- Redirect behavior: 
- Error messages: 
```

#### 3.3 Verify Authenticated State
1. Check if user is logged in (e.g., user menu, profile icon visible)
2. Try accessing a protected route (e.g., profile page, dashboard)

**Expected Results**:
- [ ] ✅ User is logged in successfully
- [ ] ✅ Protected routes are accessible
- [ ] ✅ User data is displayed correctly

**Actual Results**:
```
[To be filled by tester]
- Logged in state: 
- Protected routes accessible: 
```

---

## Test Case 4: CORS Preflight Verification

### Objective
Verify that CORS preflight OPTIONS requests are handled correctly.

### Steps

#### 4.1 Inspect Preflight Request
1. In the Network tab, find the registration POST request from Test Case 1
2. Look for a corresponding OPTIONS request to the same endpoint (should appear just before the POST)
3. Click on the OPTIONS request to view details

**Expected Results**:
- [ ] ✅ OPTIONS request to `/api/auth/register` is present
- [ ] ✅ Request status: `200 OK` or `204 No Content`
- [ ] ✅ Response headers include:
  - `Access-Control-Allow-Origin: https://frontend-ten-psi-9hutf2paf3.vercel.app`
  - `Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS` (or similar)
  - `Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With` (or similar)
  - `Access-Control-Allow-Credentials: true`

**Actual Results**:
```
[To be filled by tester]
- OPTIONS request present: Yes/No
- OPTIONS request status: 
- Access-Control-Allow-Origin header: 
- Access-Control-Allow-Methods header: 
- Access-Control-Allow-Headers header: 
- Access-Control-Allow-Credentials header: 
```

---

## Test Case 5: Multiple Vercel Deployment URLs (If Available)

### Objective
Verify that the wildcard CORS pattern supports different Vercel deployment URLs.

### Prerequisites
- Access to additional Vercel deployment URLs (e.g., preview deployments, branch deployments)
- If not available, this test can be skipped

### Steps

#### 5.1 Identify Alternative Vercel URLs
1. Check Vercel dashboard for preview deployments
2. Note any alternative URLs (e.g., `frontend-git-feature-branch.vercel.app`)

**Alternative Vercel URLs**:
```
[To be filled by tester]
1. 
2. 
3. 
```

#### 5.2 Test Registration from Alternative URL
For each alternative URL:
1. Navigate to `[alternative-url]/register` in incognito mode
2. Repeat Test Case 1 (Registration) steps
3. Monitor for CORS errors

**Expected Results**:
- [ ] ✅ Registration succeeds from alternative Vercel URL
- [ ] ✅ No CORS errors
- [ ] ✅ Response includes `Access-Control-Allow-Origin: [alternative-url]`

**Actual Results**:
```
[To be filled by tester]
URL 1: 
- Registration status: 
- CORS errors: 

URL 2: 
- Registration status: 
- CORS errors: 
```

---

## Test Case 6: Negative Test - Unverified Login Attempt

### Objective
Verify that unverified users cannot log in (preservation requirement 3.2).

### Steps

#### 6.1 Register New User Without Verification
1. Register a new user (follow Test Case 1 steps 1.1-1.3)
2. **DO NOT click the verification link**
3. Note the email and password

#### 6.2 Attempt Login Without Verification
1. Navigate to login page
2. Enter the unverified user's credentials
3. Click "Login"

**Expected Results**:
- [ ] ✅ Login is rejected
- [ ] ✅ Error message: "Please verify your email" or similar
- [ ] ✅ User is NOT logged in
- [ ] ✅ User is NOT redirected to dashboard

**Actual Results**:
```
[To be filled by tester]
- Login status: 
- Error message: 
- Redirect behavior: 
```

---

## Test Case 7: Edge Case - Invalid Email Domain

### Objective
Verify that non-@student.laverdad.edu.ph emails are rejected (preservation requirement 3.4).

### Steps

#### 7.1 Attempt Registration with Invalid Domain
1. Navigate to registration page
2. Enter user details with non-@student.laverdad.edu.ph email:
   - **Email**: `testuser@gmail.com` (or any non-allowed domain)
3. Submit registration

**Expected Results**:
- [ ] ✅ Registration is rejected
- [ ] ✅ Error message indicates email domain is not allowed
- [ ] ✅ No verification email is sent

**Actual Results**:
```
[To be filled by tester]
- Registration status: 
- Error message: 
```

---

## Test Case 8: Edge Case - Duplicate Email

### Objective
Verify that duplicate email registration is rejected (preservation requirement 3.5).

### Steps

#### 8.1 Attempt Registration with Existing Email
1. Navigate to registration page
2. Enter user details with the email from Test Case 1 (already registered)
3. Submit registration

**Expected Results**:
- [ ] ✅ Registration is rejected
- [ ] ✅ Error message: "Email already exists" or similar
- [ ] ✅ No new verification email is sent

**Actual Results**:
```
[To be filled by tester]
- Registration status: 
- Error message: 
```

---

## Summary Checklist

### Requirements Validated

| Requirement | Description | Status | Notes |
|------------|-------------|--------|-------|
| 2.1 | Registration succeeds from Vercel frontend | ⬜ Pass / ⬜ Fail | |
| 2.2 | CORS headers allow registration request | ⬜ Pass / ⬜ Fail | |
| 2.5 | Verification email received | ⬜ Pass / ⬜ Fail | |
| 2.6 | Verification link works correctly | ⬜ Pass / ⬜ Fail | |
| 2.7 | Login succeeds after verification | ⬜ Pass / ⬜ Fail | |
| 3.2 | Unverified users cannot login | ⬜ Pass / ⬜ Fail | |
| 3.4 | Invalid email domains rejected | ⬜ Pass / ⬜ Fail | |
| 3.5 | Duplicate emails rejected | ⬜ Pass / ⬜ Fail | |

### Overall Test Results

- **Total Test Cases**: 8
- **Passed**: [To be filled]
- **Failed**: [To be filled]
- **Skipped**: [To be filled]

### Critical Issues Found
```
[To be filled by tester]
1. 
2. 
3. 
```

### Non-Critical Issues Found
```
[To be filled by tester]
1. 
2. 
3. 
```

---

## Troubleshooting Guide

### Issue: CORS Errors Still Appearing

**Symptoms**: Console shows "Access to fetch has been blocked by CORS policy"

**Possible Causes**:
1. Backend not deployed with updated CORS configuration
2. Browser cache not cleared
3. FRONTEND_URL environment variable not set on Render

**Resolution Steps**:
1. Verify backend deployment on Render includes updated `config/cors.php`
2. Clear browser cache and cookies
3. Check Render environment variables: `FRONTEND_URL` should be set
4. Restart backend service on Render
5. Test in incognito mode

### Issue: Verification Email Not Received

**Symptoms**: No email received after registration

**Possible Causes**:
1. Email in spam/junk folder
2. Brevo API key not configured
3. FRONTEND_URL not set (MailService returns false)

**Resolution Steps**:
1. Check spam/junk folder
2. Wait up to 5 minutes (email delivery can be delayed)
3. Check backend logs on Render for MailService errors
4. Verify Brevo API key is set in Render environment variables
5. Verify FRONTEND_URL is set in Render environment variables

### Issue: Verification Link Returns 404

**Symptoms**: Clicking verification link shows "Page not found"

**Possible Causes**:
1. Frontend route not configured for `/verify-email`
2. Token parameter missing or malformed

**Resolution Steps**:
1. Check frontend routing configuration
2. Verify URL format: `{FRONTEND_URL}/verify-email?token={token}`
3. Check backend logs for token validation errors

### Issue: Login Fails After Verification

**Symptoms**: Login rejected even after email verification

**Possible Causes**:
1. Email not actually verified (check database)
2. SANCTUM_STATEFUL_DOMAINS misconfigured
3. Authentication token not stored correctly

**Resolution Steps**:
1. Verify `email_verified_at` is set in database
2. Check SANCTUM_STATEFUL_DOMAINS matches frontend domain
3. Clear browser cookies and try again
4. Check backend logs for authentication errors

---

## Test Completion

**Tester Name**: [To be filled]  
**Test Date**: [To be filled]  
**Test Duration**: [To be filled]  
**Overall Status**: ⬜ PASS / ⬜ FAIL / ⬜ PARTIAL

**Tester Signature**: ___________________________

**Reviewer Name**: [To be filled]  
**Review Date**: [To be filled]  
**Reviewer Signature**: ___________________________

---

## Next Steps

After completing this manual testing:

1. **If all tests pass**:
   - Mark Task 4.1 as complete
   - Document test results in this file
   - Proceed to Task 4.2 (Test resend verification flow)

2. **If any tests fail**:
   - Document failures in "Critical Issues Found" section
   - Investigate root cause using troubleshooting guide
   - Fix issues and re-test
   - Do NOT proceed to next task until all tests pass

3. **If tests are blocked**:
   - Document blocking issues
   - Escalate to development team
   - Verify backend deployment status on Render
   - Verify frontend deployment status on Vercel

---

**Task**: 4.1 - Test full registration flow from frontend  
**Status**: 🔄 AWAITING MANUAL TESTING  
**Requirements**: 2.1, 2.2, 2.5, 2.6, 2.7


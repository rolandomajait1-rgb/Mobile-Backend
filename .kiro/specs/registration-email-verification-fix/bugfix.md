# Bugfix Requirements Document

## Introduction

The registration and email verification system in the Laravel backend is experiencing multiple critical failures that prevent users from completing the onboarding flow. Users cannot register new accounts due to network errors and CORS issues, and the email verification flow fails to properly verify user emails. This bug affects the entire user onboarding process, blocking access to the application for new users.

The system uses:
- Laravel Sanctum for authentication
- Custom email verification with tokens stored in `email_verification_tokens` table
- Brevo API for sending verification emails via MailService
- Frontend deployed on Vercel, backend on Render
- Email restriction: only @student.laverdad.edu.ph domains allowed

## Bug Analysis

### Current Behavior (Defect)

**Registration Endpoint Failures:**

1.1 WHEN a user submits registration from the Vercel-deployed frontend THEN the system returns network errors preventing account creation

1.2 WHEN the frontend makes a POST request to `/api/auth/register` THEN CORS policy blocks the request with "Access to fetch has been blocked by CORS policy" errors

1.3 WHEN the backend receives a registration request from an unauthorized origin THEN the preflight OPTIONS request fails without proper CORS headers

1.4 WHEN CORS configuration uses hardcoded frontend URLs THEN requests from new Vercel deployment URLs are rejected

**Email Verification Flow Failures:**

1.5 WHEN a user successfully registers THEN verification emails fail to send or are not received

1.6 WHEN a user clicks the verification link in their email THEN the token validation fails or returns errors

1.7 WHEN the verification token is validated THEN the frontend redirect after verification does not work properly

1.8 WHEN a user attempts to resend verification email THEN the system fails to generate or send a new verification link

**System Configuration Issues:**

1.9 WHEN the backend CORS configuration is missing dynamic frontend URL support THEN legitimate requests from production domains are blocked

1.10 WHEN environment variables (FRONTEND_URL, SANCTUM_STATEFUL_DOMAINS) are misconfigured or missing THEN cross-origin authentication and verification links fail

### Expected Behavior (Correct)

**Registration Endpoint:**

2.1 WHEN a user submits registration from the Vercel-deployed frontend THEN the system SHALL successfully create the account and return a 201 response with user data

2.2 WHEN the frontend makes a POST request to `/api/auth/register` THEN the system SHALL respond with proper CORS headers allowing the request

2.3 WHEN the backend receives a preflight OPTIONS request THEN the system SHALL respond with appropriate Access-Control-Allow-* headers

2.4 WHEN CORS configuration is updated THEN the system SHALL dynamically support Vercel deployment URLs using pattern matching (*.vercel.app)

**Email Verification Flow:**

2.5 WHEN a user successfully registers THEN the system SHALL send a verification email via Brevo API with a valid token

2.6 WHEN a user clicks the verification link THEN the system SHALL validate the token, mark the email as verified, and return success

2.7 WHEN email verification succeeds THEN the system SHALL properly redirect the user to the login page with a success message

2.8 WHEN a user requests to resend verification email THEN the system SHALL generate a new token and send a new verification email

**System Configuration:**

2.9 WHEN the backend CORS configuration includes dynamic origin patterns THEN the system SHALL accept requests from all legitimate Vercel deployment URLs

2.10 WHEN environment variables are properly configured THEN the system SHALL generate correct verification URLs and allow cross-origin authenticated requests

### Unchanged Behavior (Regression Prevention)

**Authentication Flow:**

3.1 WHEN a verified user logs in with correct credentials THEN the system SHALL CONTINUE TO authenticate successfully and return a Sanctum token

3.2 WHEN an unverified user attempts to login THEN the system SHALL CONTINUE TO reject the login with "Please verify your email" message

3.3 WHEN a user logs out THEN the system SHALL CONTINUE TO invalidate the current access token

**Email Validation:**

3.4 WHEN a user registers with a non-@student.laverdad.edu.ph email THEN the system SHALL CONTINUE TO reject the registration with appropriate error message

3.5 WHEN a user registers with an already-used email THEN the system SHALL CONTINUE TO reject with "email already exists" error

**Token Management:**

3.6 WHEN a verification token is older than 24 hours THEN the system SHALL CONTINUE TO reject it as expired

3.7 WHEN an invalid or non-existent token is provided THEN the system SHALL CONTINUE TO return "Invalid verification token" error

**Protected Routes:**

3.8 WHEN an authenticated user accesses protected API endpoints THEN the system SHALL CONTINUE TO authorize the request with valid Sanctum token

3.9 WHEN an unauthenticated user attempts to access protected routes THEN the system SHALL CONTINUE TO return 401 Unauthorized

**Password Reset:**

3.10 WHEN a user requests password reset THEN the system SHALL CONTINUE TO send reset emails via Brevo API

3.11 WHEN a user completes password reset with valid token THEN the system SHALL CONTINUE TO update the password successfully

**Localhost Development:**

3.12 WHEN developers make requests from localhost:3000 or localhost:5173 THEN the system SHALL CONTINUE TO accept these requests for local development

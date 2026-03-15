# Registration and Email Verification Fix Design

## Overview

This bugfix addresses critical failures in the registration and email verification system that prevent users from completing the onboarding flow. The root causes are: (1) hardcoded CORS allowed_origins that reject requests from new Vercel deployment URLs, (2) missing dynamic origin pattern matching for Vercel's deployment model, and (3) potential environment variable misconfiguration in production. The fix strategy involves updating CORS configuration to use environment variables with wildcard pattern support, ensuring FRONTEND_URL and SANCTUM_STATEFUL_DOMAINS are properly configured, and verifying the email verification token flow works end-to-end.

## Glossary

- **Bug_Condition (C)**: The condition that triggers registration/verification failures - when requests originate from Vercel-deployed frontend URLs not in the hardcoded CORS allowed_origins list
- **Property (P)**: The desired behavior - registration requests from legitimate frontend origins should succeed with proper CORS headers, and email verification links should work correctly
- **Preservation**: Existing authentication flows (login, logout, password reset) and localhost development access must remain unchanged
- **CORS (Cross-Origin Resource Sharing)**: Browser security mechanism that controls which origins can access backend resources
- **Sanctum Stateful Domains**: Laravel Sanctum configuration that determines which domains receive stateful authentication cookies
- **Vercel Deployment Model**: Each deployment gets a unique URL (e.g., `frontend-ten-psi-9hutf2paf3.vercel.app`), requiring pattern-based origin matching
- **Email Verification Token**: SHA-256 hashed token stored in `email_verification_tokens` table, valid for 24 hours
- **MailService**: Service class using Brevo API to send verification and password reset emails
- **FRONTEND_URL**: Environment variable defining the primary frontend URL for generating verification links

## Bug Details

### Bug Condition

The bug manifests when a user attempts to register from the Vercel-deployed frontend or clicks an email verification link. The CORS configuration in `config/cors.php` uses a hardcoded array of allowed_origins that includes only one specific Vercel URL (`frontend-ten-psi-9hutf2paf3.vercel.app`). When Vercel creates new deployments with different URLs, or when the frontend URL changes, the backend rejects preflight OPTIONS requests with CORS policy errors, preventing registration. Additionally, if FRONTEND_URL or SANCTUM_STATEFUL_DOMAINS environment variables are misconfigured, email verification links will be invalid or authentication will fail.

**Formal Specification:**
```
FUNCTION isBugCondition(input)
  INPUT: input of type HTTPRequest
  OUTPUT: boolean
  
  RETURN (input.origin NOT IN config('cors.allowed_origins'))
         AND (input.origin NOT MATCHES config('cors.allowed_origins_patterns'))
         AND (input.method == 'OPTIONS' OR input.path == '/api/auth/register')
         AND (input.origin MATCHES '*.vercel.app' OR input.origin == env('FRONTEND_URL'))
END FUNCTION
```

### Examples

- **Registration from new Vercel deployment**: User visits `https://frontend-abc123.vercel.app`, attempts to register, browser sends preflight OPTIONS request to `/api/auth/register`, backend returns CORS error because `frontend-abc123.vercel.app` is not in hardcoded allowed_origins
- **Registration from production URL after redeployment**: Frontend URL changes from `frontend-ten-psi-9hutf2paf3.vercel.app` to `frontend-production.vercel.app`, registration fails with "Network Error" due to CORS rejection
- **Email verification link with wrong FRONTEND_URL**: User receives verification email with link `http://localhost:5173/verify-email?token=...` instead of production URL, clicking link fails because localhost is not accessible
- **Edge case - SANCTUM_STATEFUL_DOMAINS mismatch**: FRONTEND_URL is set correctly but SANCTUM_STATEFUL_DOMAINS is missing or different, causing authentication cookie issues after verification

## Expected Behavior

### Preservation Requirements

**Unchanged Behaviors:**
- Login with verified credentials must continue to work exactly as before
- Logout functionality must continue to invalidate tokens correctly
- Password reset flow must continue to send emails and validate tokens
- Email domain validation (@student.laverdad.edu.ph) must remain enforced
- Localhost development access (localhost:3000, localhost:5173) must continue to work
- Token expiration rules (24 hours for verification, 60 minutes for password reset) must remain unchanged
- Protected route authorization with Sanctum tokens must continue to work
- Admin-only endpoints must continue to enforce role-based access control

**Scope:**
All inputs that do NOT involve cross-origin registration requests or email verification should be completely unaffected by this fix. This includes:
- Same-origin requests (if any)
- Authenticated API calls with valid Sanctum tokens
- Public endpoints (articles, categories, tags)
- Password reset functionality
- User profile updates
- Article interactions (likes, shares)

## Hypothesized Root Cause

Based on the bug description and code analysis, the most likely issues are:

1. **Hardcoded CORS Origins**: The `config/cors.php` file uses a static array for `allowed_origins` with only one Vercel URL hardcoded. Vercel's deployment model creates unique URLs for each deployment, so any new deployment URL will be rejected.
   - Current: `'allowed_origins' => ['https://frontend-ten-psi-9hutf2paf3.vercel.app', ...]`
   - Problem: New deployments get different URLs (e.g., `frontend-abc123.vercel.app`)

2. **Missing Origin Pattern Matching**: The `allowed_origins_patterns` array is empty, preventing wildcard matching for Vercel domains
   - Current: `'allowed_origins_patterns' => []`
   - Needed: Pattern like `/^https:\/\/.*\.vercel\.app$/` to match all Vercel deployments

3. **Environment Variable Not Used in CORS**: The CORS config doesn't read FRONTEND_URL from environment variables, making it impossible to update allowed origins without code changes
   - Current: Hardcoded URLs in config file
   - Needed: Dynamic reading from `env('FRONTEND_URL')`

4. **SANCTUM_STATEFUL_DOMAINS Misconfiguration**: The Sanctum config may not include the production frontend domain, causing authentication cookie issues
   - Check: `.env.production` should have `SANCTUM_STATEFUL_DOMAINS=frontend-ten-psi-9hutf2paf3.vercel.app` (without protocol)
   - Problem: If missing or incorrect, stateful authentication will fail

5. **FRONTEND_URL Not Set in Production**: If FRONTEND_URL is not set or set to localhost in production, verification emails will contain invalid links
   - Check: `.env.production` should have `FRONTEND_URL=https://frontend-ten-psi-9hutf2paf3.vercel.app`
   - Problem: MailService uses this to generate verification URLs

## Correctness Properties

Property 1: Bug Condition - Registration CORS Success

_For any_ HTTP request where the origin is a legitimate frontend deployment URL (matches FRONTEND_URL or *.vercel.app pattern) and the request is to `/api/auth/register`, the fixed CORS configuration SHALL respond with appropriate Access-Control-Allow-Origin headers, allowing the registration request to proceed successfully.

**Validates: Requirements 2.1, 2.2, 2.3, 2.4**

Property 2: Bug Condition - Email Verification Link Validity

_For any_ user registration where email verification is triggered, the fixed system SHALL generate verification links using the correct FRONTEND_URL from environment variables, ensuring links point to the production frontend and tokens are properly validated when clicked.

**Validates: Requirements 2.5, 2.6, 2.7, 2.8, 2.9, 2.10**

Property 3: Preservation - Existing Authentication Flows

_For any_ authentication operation that is NOT registration or email verification (login, logout, password reset, token validation), the fixed code SHALL produce exactly the same behavior as the original code, preserving all existing authentication functionality.

**Validates: Requirements 3.1, 3.2, 3.3, 3.10, 3.11**

Property 4: Preservation - Email and Access Control

_For any_ request that involves email domain validation, role-based access control, or localhost development access, the fixed code SHALL maintain the same validation rules and access patterns as the original code.

**Validates: Requirements 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.12**

## Fix Implementation

### Changes Required

Assuming our root cause analysis is correct:

**File**: `amber backend/config/cors.php`

**Specific Changes**:
1. **Replace Hardcoded Origins with Environment Variable**:
   - Change `allowed_origins` to read from `FRONTEND_URL` environment variable
   - Keep localhost URLs for development
   - Implementation:
   ```php
   'allowed_origins' => array_filter([
       env('FRONTEND_URL'),
       'http://localhost:3000',
       'http://localhost:5173',
   ]),
   ```

2. **Add Wildcard Pattern for Vercel Deployments**:
   - Update `allowed_origins_patterns` to match all Vercel deployment URLs
   - Implementation:
   ```php
   'allowed_origins_patterns' => [
       '/^https:\/\/.*\.vercel\.app$/',
   ],
   ```

3. **Verify supports_credentials Setting**:
   - Ensure `supports_credentials` is `true` for Sanctum authentication
   - Current value is correct: `'supports_credentials' => true`

**File**: `amber backend/.env.production` (Render Environment Variables)

**Specific Changes**:
4. **Verify FRONTEND_URL is Set Correctly**:
   - Ensure: `FRONTEND_URL=https://frontend-ten-psi-9hutf2paf3.vercel.app`
   - No trailing slash
   - Must match actual Vercel deployment URL

5. **Verify SANCTUM_STATEFUL_DOMAINS is Set Correctly**:
   - Ensure: `SANCTUM_STATEFUL_DOMAINS=frontend-ten-psi-9hutf2paf3.vercel.app`
   - No protocol (no https://)
   - No trailing slash
   - Must match the domain portion of FRONTEND_URL

**File**: `amber backend/config/sanctum.php`

**Verification Only** (no changes needed):
6. **Confirm Sanctum Reads SANCTUM_STATEFUL_DOMAINS**:
   - Current implementation correctly uses `env('SANCTUM_STATEFUL_DOMAINS')`
   - No code changes required, only environment variable verification

**File**: `amber backend/app/Services/MailService.php`

**Verification Only** (no changes needed):
7. **Confirm MailService Uses FRONTEND_URL**:
   - Current implementation correctly reads `env('FRONTEND_URL')` for verification links
   - Logs error if FRONTEND_URL is not set
   - No code changes required

**File**: `amber backend/app/Http/Controllers/AuthController.php`

**Verification Only** (no changes needed):
8. **Confirm Registration Returns Proper CORS Headers**:
   - Current implementation manually adds CORS headers in register() method
   - This is redundant with CORS middleware but provides fallback
   - Consider removing manual headers after CORS config fix is verified

## Testing Strategy

### Validation Approach

The testing strategy follows a two-phase approach: first, surface counterexamples that demonstrate the CORS and email verification bugs on unfixed code, then verify the fix works correctly across different deployment URLs and preserves existing authentication behavior.

### Exploratory Bug Condition Checking

**Goal**: Surface counterexamples that demonstrate the CORS rejection and email verification bugs BEFORE implementing the fix. Confirm or refute the root cause analysis. If we refute, we will need to re-hypothesize.

**Test Plan**: Write tests that simulate registration requests from different origins and verify CORS headers. Test email verification link generation and token validation. Run these tests on the UNFIXED code to observe failures and understand the root cause.

**Test Cases**:
1. **Registration from Hardcoded Vercel URL**: Send POST to `/api/auth/register` with Origin header `https://frontend-ten-psi-9hutf2paf3.vercel.app` (should succeed on unfixed code)
2. **Registration from New Vercel URL**: Send POST with Origin `https://frontend-abc123.vercel.app` (will fail on unfixed code with CORS error)
3. **Preflight OPTIONS Request**: Send OPTIONS to `/api/auth/register` with new Vercel origin (will fail on unfixed code)
4. **Email Verification Link Generation**: Register user and inspect verification email content for FRONTEND_URL usage (may show localhost on unfixed code if env var missing)
5. **Token Validation**: Click verification link and observe token validation (may fail if SANCTUM_STATEFUL_DOMAINS incorrect)

**Expected Counterexamples**:
- CORS policy blocks requests from non-hardcoded Vercel URLs
- Preflight OPTIONS requests return without Access-Control-Allow-Origin header
- Verification emails contain localhost URLs instead of production URLs
- Token validation fails due to SANCTUM_STATEFUL_DOMAINS mismatch
- Possible causes: hardcoded origins, empty patterns array, missing environment variables

### Fix Checking

**Goal**: Verify that for all inputs where the bug condition holds (requests from legitimate frontend origins), the fixed CORS configuration produces the expected behavior (proper CORS headers and successful registration).

**Pseudocode:**
```
FOR ALL request WHERE isBugCondition(request) DO
  response := handleRequest_fixed(request)
  ASSERT response.headers['Access-Control-Allow-Origin'] == request.origin
  ASSERT response.status IN [200, 201, 204]
END FOR
```

**Test Cases**:
1. **Registration from FRONTEND_URL**: Verify registration succeeds with proper CORS headers
2. **Registration from Different Vercel URL**: Verify wildcard pattern matches and allows request
3. **Email Verification Link**: Verify generated links use correct FRONTEND_URL
4. **Token Validation**: Verify tokens validate correctly and redirect works
5. **Resend Verification**: Verify resend generates new token and sends email

### Preservation Checking

**Goal**: Verify that for all inputs where the bug condition does NOT hold (existing authentication flows), the fixed code produces the same result as the original code.

**Pseudocode:**
```
FOR ALL request WHERE NOT isBugCondition(request) DO
  ASSERT handleRequest_original(request) = handleRequest_fixed(request)
END FOR
```

**Testing Approach**: Property-based testing is recommended for preservation checking because:
- It generates many test cases automatically across the input domain
- It catches edge cases that manual unit tests might miss
- It provides strong guarantees that behavior is unchanged for all non-buggy inputs

**Test Plan**: Observe behavior on UNFIXED code first for login, logout, password reset, and protected routes, then write property-based tests capturing that behavior.

**Test Cases**:
1. **Login Preservation**: Observe that verified user login works on unfixed code, verify continues after fix
2. **Unverified Login Rejection**: Observe that unverified users are rejected on unfixed code, verify continues after fix
3. **Logout Preservation**: Observe that logout invalidates tokens on unfixed code, verify continues after fix
4. **Email Domain Validation**: Observe that non-@student.laverdad.edu.ph emails are rejected on unfixed code, verify continues after fix
5. **Token Expiration**: Observe that expired tokens are rejected on unfixed code, verify continues after fix
6. **Protected Routes**: Observe that authenticated requests succeed and unauthenticated fail on unfixed code, verify continues after fix
7. **Localhost Development**: Observe that localhost origins are allowed on unfixed code, verify continues after fix
8. **Password Reset**: Observe that password reset flow works on unfixed code, verify continues after fix

### Unit Tests

- Test CORS configuration reads FRONTEND_URL from environment
- Test wildcard pattern matching for Vercel URLs
- Test email verification token generation and validation
- Test verification link generation uses correct FRONTEND_URL
- Test SANCTUM_STATEFUL_DOMAINS parsing in Sanctum config
- Test edge cases (missing env vars, invalid tokens, expired tokens)

### Property-Based Tests

- Generate random Vercel deployment URLs and verify CORS pattern matching
- Generate random valid/invalid tokens and verify validation logic
- Generate random email addresses and verify domain validation
- Test that all authentication endpoints preserve behavior for non-registration requests

### Integration Tests

- Test full registration flow from frontend to email verification
- Test registration from multiple Vercel deployment URLs
- Test email verification link click and redirect
- Test resend verification email flow
- Test that login fails before verification and succeeds after
- Test that CORS headers are present on all cross-origin requests
- Test localhost development workflow remains functional

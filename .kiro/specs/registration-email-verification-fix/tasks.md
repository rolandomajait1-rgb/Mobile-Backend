# Implementation Plan

- [x] 1. Write bug condition exploration test
  - **Property 1: Bug Condition** - CORS Rejection and Email Verification Failures
  - **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
  - **DO NOT attempt to fix the test or the code when it fails**
  - **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
  - **GOAL**: Surface counterexamples that demonstrate CORS rejection and email verification bugs
  - **Scoped PBT Approach**: Scope the property to concrete failing cases - registration from non-hardcoded Vercel URLs
  - Test implementation details from Bug Condition in design:
    - Test that registration request from `https://frontend-abc123.vercel.app` is blocked by CORS (isBugCondition: origin NOT IN allowed_origins)
    - Test that preflight OPTIONS request from new Vercel URL fails without proper CORS headers
    - Test that verification email contains incorrect FRONTEND_URL (localhost instead of production)
    - Test that SANCTUM_STATEFUL_DOMAINS mismatch causes authentication failures
  - The test assertions should match the Expected Behavior Properties from design:
    - Registration from legitimate Vercel URLs should succeed with proper CORS headers
    - Email verification links should use correct FRONTEND_URL from environment
    - Token validation should work correctly with proper SANCTUM_STATEFUL_DOMAINS
  - Run test on UNFIXED code
  - **EXPECTED OUTCOME**: Test FAILS (this is correct - it proves the bug exists)
  - Document counterexamples found:
    - CORS policy blocks requests from non-hardcoded Vercel URLs
    - Preflight OPTIONS returns without Access-Control-Allow-Origin header
    - Verification emails contain localhost URLs instead of production
    - Token validation fails due to domain mismatch
  - Mark task complete when test is written, run, and failure is documented
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.9, 1.10_

- [x] 2. Write preservation property tests (BEFORE implementing fix)
  - **Property 2: Preservation** - Existing Authentication and Email Flows
  - **IMPORTANT**: Follow observation-first methodology
  - Observe behavior on UNFIXED code for non-buggy inputs:
    - Observe: Verified user login with correct credentials succeeds
    - Observe: Unverified user login is rejected with "Please verify your email"
    - Observe: Logout invalidates current access token
    - Observe: Non-@student.laverdad.edu.ph emails are rejected during registration
    - Observe: Duplicate email registration is rejected
    - Observe: Expired tokens (>24h for verification, >60min for password reset) are rejected
    - Observe: Protected routes require valid Sanctum token
    - Observe: Password reset flow sends emails and validates tokens correctly
    - Observe: Localhost origins (localhost:3000, localhost:5173) are allowed
  - Write property-based tests capturing observed behavior patterns from Preservation Requirements:
    - For all verified users with correct credentials, login succeeds (Req 3.1)
    - For all unverified users, login is rejected (Req 3.2)
    - For all logout requests, token is invalidated (Req 3.3)
    - For all non-@student.laverdad.edu.ph emails, registration is rejected (Req 3.4)
    - For all duplicate emails, registration is rejected (Req 3.5)
    - For all expired tokens, validation fails (Req 3.6, 3.7)
    - For all authenticated requests to protected routes, access is granted (Req 3.8)
    - For all unauthenticated requests to protected routes, 401 is returned (Req 3.9)
    - For all password reset requests, emails are sent and tokens validate (Req 3.10, 3.11)
    - For all localhost origins, CORS allows requests (Req 3.12)
  - Property-based testing generates many test cases for stronger guarantees
  - Run tests on UNFIXED code
  - **EXPECTED OUTCOME**: Tests PASS (this confirms baseline behavior to preserve)
  - Mark task complete when tests are written, run, and passing on unfixed code
  - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 3.12_

- [ ] 3. Fix CORS configuration and environment variables

  - [x] 3.1 Update CORS configuration in config/cors.php
    - Replace hardcoded `allowed_origins` array with dynamic environment variable reading
    - Implementation: Use `array_filter([env('FRONTEND_URL'), 'http://localhost:3000', 'http://localhost:5173'])`
    - Add wildcard pattern for Vercel deployments to `allowed_origins_patterns`
    - Implementation: Add `/^https:\/\/.*\.vercel\.app$/` pattern
    - Verify `supports_credentials` remains `true` for Sanctum authentication
    - _Bug_Condition: isBugCondition(input) where input.origin NOT IN config('cors.allowed_origins') AND input.origin NOT MATCHES patterns AND input.origin is legitimate Vercel URL_
    - _Expected_Behavior: For all requests from legitimate frontend origins (FRONTEND_URL or *.vercel.app), respond with proper Access-Control-Allow-Origin headers (Property 1)_
    - _Preservation: Localhost development access (localhost:3000, localhost:5173) must continue to work (Requirement 3.12)_
    - _Requirements: 1.2, 1.3, 1.4, 1.9, 2.2, 2.3, 2.4, 2.9_

  - [x] 3.2 Verify and update environment variables in production
    - Check Render environment variables for backend deployment
    - Verify `FRONTEND_URL=https://frontend-ten-psi-9hutf2paf3.vercel.app` (no trailing slash)
    - Verify `SANCTUM_STATEFUL_DOMAINS=frontend-ten-psi-9hutf2paf3.vercel.app` (no protocol, no trailing slash)
    - Ensure domain portion of FRONTEND_URL matches SANCTUM_STATEFUL_DOMAINS
    - Document current values and any changes made
    - _Bug_Condition: isBugCondition(input) where FRONTEND_URL is missing or incorrect, causing invalid verification links_
    - _Expected_Behavior: For all user registrations, generate verification links using correct FRONTEND_URL (Property 2)_
    - _Preservation: Email domain validation (@student.laverdad.edu.ph) must remain enforced (Requirement 3.4)_
    - _Requirements: 1.5, 1.6, 1.10, 2.5, 2.6, 2.10_

  - [x] 3.3 Verify Sanctum configuration reads environment variables
    - Confirm `config/sanctum.php` correctly reads `env('SANCTUM_STATEFUL_DOMAINS')`
    - No code changes needed - verification only
    - Test that stateful authentication cookies work with production domain
    - _Expected_Behavior: Sanctum stateful authentication works correctly for production frontend domain_
    - _Preservation: Token-based authentication for API requests must continue to work (Requirement 3.8)_
    - _Requirements: 1.10, 2.10_

  - [x] 3.4 Verify MailService uses FRONTEND_URL correctly
    - Confirm `app/Services/MailService.php` reads `env('FRONTEND_URL')` for verification links
    - Confirm error logging when FRONTEND_URL is not set
    - No code changes needed - verification only
    - Test that generated verification URLs use production FRONTEND_URL
    - _Expected_Behavior: For all verification emails, links point to production frontend (Property 2)_
    - _Preservation: Password reset email flow must continue to work (Requirement 3.10)_
    - _Requirements: 1.5, 2.5, 2.6, 2.7, 2.8_

  - [x] 3.5 Review manual CORS headers in AuthController
    - Review `AuthController::register()` method that manually adds CORS headers
    - Consider removing manual headers after CORS config fix is verified (they are redundant)
    - Document decision: keep for fallback or remove for cleaner code
    - If removed, verify CORS middleware handles all headers correctly
    - _Preservation: Registration endpoint must continue to return 201 with user data (Requirement 2.1)_
    - _Requirements: 1.1, 2.1, 2.2_

  - [x] 3.6 Verify bug condition exploration test now passes
    - **Property 1: Expected Behavior** - CORS Success and Email Verification
    - **IMPORTANT**: Re-run the SAME test from task 1 - do NOT write a new test
    - The test from task 1 encodes the expected behavior
    - When this test passes, it confirms the expected behavior is satisfied
    - Run bug condition exploration test from step 1
    - Verify registration from non-hardcoded Vercel URLs now succeeds with proper CORS headers
    - Verify preflight OPTIONS requests return correct Access-Control-Allow-Origin headers
    - Verify verification emails contain correct production FRONTEND_URL
    - Verify token validation works with proper SANCTUM_STATEFUL_DOMAINS
    - **EXPECTED OUTCOME**: Test PASSES (confirms bug is fixed)
    - _Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.9, 2.10_

  - [x] 3.7 Verify preservation tests still pass
    - **Property 2: Preservation** - Existing Authentication and Email Flows
    - **IMPORTANT**: Re-run the SAME tests from task 2 - do NOT write new tests
    - Run preservation property tests from step 2
    - Verify all authentication flows (login, logout, password reset) continue to work
    - Verify email domain validation still enforces @student.laverdad.edu.ph
    - Verify token expiration rules remain unchanged
    - Verify protected routes still require authentication
    - Verify localhost development access still works
    - **EXPECTED OUTCOME**: Tests PASS (confirms no regressions)
    - Confirm all tests still pass after fix (no regressions)
    - _Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8, 3.9, 3.10, 3.11, 3.12_

- [ ] 4. Integration testing

  - [x] 4.1 Test full registration flow from frontend
    - Register new user from Vercel-deployed frontend
    - Verify registration succeeds without CORS errors
    - Verify verification email is received with correct production URL
    - Click verification link and verify email is marked as verified
    - Verify login succeeds after verification
    - Test with multiple different Vercel deployment URLs if available
    - _Requirements: 2.1, 2.2, 2.5, 2.6, 2.7_

  - [ ] 4.2 Test resend verification flow
    - Register user and wait for verification email
    - Use resend verification endpoint with user's email
    - Verify new token is generated and new email is sent
    - Verify old token is invalidated
    - Click new verification link and verify success
    - _Requirements: 2.8_

  - [ ] 4.3 Test CORS headers on all endpoints
    - Send preflight OPTIONS requests to all API endpoints from Vercel origin
    - Verify Access-Control-Allow-Origin header is present
    - Verify Access-Control-Allow-Methods includes required methods
    - Verify Access-Control-Allow-Headers includes required headers
    - Test with both hardcoded Vercel URL and new deployment URLs
    - _Requirements: 2.2, 2.3, 2.4, 2.9_

  - [ ] 4.4 Test localhost development workflow
    - Start frontend on localhost:5173
    - Register new user from localhost
    - Verify CORS allows request
    - Verify verification email is sent (may use localhost URL in dev)
    - Verify login works after verification
    - _Requirements: 3.12_

  - [ ] 4.5 Test edge cases
    - Test registration with expired verification token (>24 hours)
    - Test registration with invalid token
    - Test registration with already-verified email
    - Test registration with non-@student.laverdad.edu.ph email
    - Test registration with duplicate email
    - Verify all edge cases return appropriate error messages
    - _Requirements: 3.4, 3.5, 3.6, 3.7_

- [ ] 5. Documentation updates

  - [ ] 5.1 Update deployment documentation
    - Document required environment variables (FRONTEND_URL, SANCTUM_STATEFUL_DOMAINS)
    - Document CORS configuration changes
    - Document Vercel deployment URL pattern matching
    - Add troubleshooting section for CORS issues
    - _Requirements: 2.9, 2.10_

  - [ ] 5.2 Update .env.example file
    - Add FRONTEND_URL with example value
    - Add SANCTUM_STATEFUL_DOMAINS with example value
    - Add comments explaining the format (no trailing slash, no protocol for Sanctum)
    - _Requirements: 2.10_

- [ ] 6. Checkpoint - Ensure all tests pass
  - Run all bug condition exploration tests - verify they pass
  - Run all preservation property tests - verify they pass
  - Run all integration tests - verify they pass
  - Verify no regressions in existing authentication flows
  - Ask the user if questions arise or if manual testing is needed

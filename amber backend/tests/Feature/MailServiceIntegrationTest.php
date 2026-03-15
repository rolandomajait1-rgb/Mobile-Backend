<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Integration tests for MailService FRONTEND_URL usage
 * 
 * **Validates: Task 3.4 - Verify MailService uses FRONTEND_URL correctly**
 * **Requirements: 1.5, 2.5, 2.6, 2.7, 2.8, 3.10**
 */
class MailServiceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that verification emails use production FRONTEND_URL
     * 
     * **Validates: Requirements 2.5, 2.6, 2.7, 2.8**
     */
    public function test_verification_email_uses_production_frontend_url(): void
    {
        // Arrange
        $productionUrl = 'https://frontend-ten-psi-9hutf2paf3.vercel.app';
        config(['app.env' => 'production']);
        $_ENV['FRONTEND_URL'] = $productionUrl;
        config(['services.brevo.key' => 'test-api-key']);
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-verification-token';
        
        // Mock Brevo API
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-123'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert
        $this->assertTrue($result, 'Email should be sent successfully');
        
        // Verify the HTTP request contains the production URL
        Http::assertSent(function ($request) use ($productionUrl, $token) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Verify production URL is in the email
            $expectedUrl = $productionUrl . '/verify-email?token=' . $token;
            $hasProductionUrl = str_contains($htmlContent, $expectedUrl);
            
            // Verify localhost is NOT in the email
            $hasLocalhost = str_contains($htmlContent, 'localhost');
            
            return $hasProductionUrl && !$hasLocalhost;
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }

    /**
     * Test that password reset emails use production FRONTEND_URL
     * This verifies preservation requirement 3.10
     * 
     * **Validates: Requirement 3.10**
     */
    public function test_password_reset_email_uses_production_frontend_url(): void
    {
        // Arrange
        $productionUrl = 'https://frontend-ten-psi-9hutf2paf3.vercel.app';
        config(['app.env' => 'production']);
        $_ENV['FRONTEND_URL'] = $productionUrl;
        config(['services.brevo.key' => 'test-api-key']);
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-reset-token';
        
        // Mock Brevo API
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-456'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendPasswordResetEmail($user, $token);
        
        // Assert
        $this->assertTrue($result, 'Password reset email should be sent successfully');
        
        // Verify the HTTP request contains the production URL
        Http::assertSent(function ($request) use ($productionUrl, $token, $user) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Verify production URL is in the email
            $expectedUrl = $productionUrl . '/reset-password?token=' . $token;
            $hasProductionUrl = str_contains($htmlContent, $expectedUrl);
            
            // Verify localhost is NOT in the email
            $hasLocalhost = str_contains($htmlContent, 'localhost');
            
            return $hasProductionUrl && !$hasLocalhost;
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }

    /**
     * Test that MailService returns false when FRONTEND_URL is missing
     * 
     * **Validates: Requirements 2.5, 2.6, 2.7, 2.8**
     */
    public function test_returns_false_when_frontend_url_missing(): void
    {
        // Arrange
        unset($_ENV['FRONTEND_URL']);
        config(['app.url' => '']);
        config(['services.brevo.key' => 'test-api-key']);
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-token';
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert - The important thing is that it returns false and doesn't send invalid emails
        $this->assertFalse($result, 'Email should fail when FRONTEND_URL is not set');
        
        // Verify no HTTP request was made (no email sent with invalid URL)
        Http::assertNothingSent();
    }

    /**
     * Test that MailService handles trailing slashes correctly
     * 
     * **Validates: Requirements 2.5, 2.6**
     */
    public function test_handles_trailing_slash_in_frontend_url(): void
    {
        // Arrange - URL with trailing slash
        $urlWithSlash = 'https://frontend-test.vercel.app/';
        $_ENV['FRONTEND_URL'] = $urlWithSlash;
        config(['services.brevo.key' => 'test-api-key']);
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-token';
        
        // Mock Brevo API
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-789'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert
        $this->assertTrue($result);
        
        // Verify no double slash in URL
        Http::assertSent(function ($request) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Should not have double slash before /verify-email
            $hasDoubleSlash = str_contains($htmlContent, '//verify-email');
            
            return !$hasDoubleSlash;
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }
}

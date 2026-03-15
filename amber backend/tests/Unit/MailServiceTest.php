<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\MailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class MailServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up required config for tests
        config(['services.brevo.key' => 'test-api-key']);
        config(['mail.from.address' => 'test@example.com']);
        config(['mail.from.name' => 'Test App']);
    }

    /**
     * Test that sendVerificationEmail reads FRONTEND_URL from environment
     * 
     * **Validates: Requirements 1.5, 2.5, 2.6, 2.7, 2.8**
     */
    public function test_sendVerificationEmail_uses_frontend_url_from_env(): void
    {
        // Arrange
        $frontendUrl = 'https://frontend-test.vercel.app';
        $_ENV['FRONTEND_URL'] = $frontendUrl;
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-token-123';
        
        // Mock HTTP request to Brevo API
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-id'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert
        $this->assertTrue($result, 'Email should be sent successfully');
        
        // Verify the request was made with correct verification URL
        Http::assertSent(function ($request) use ($frontendUrl, $token) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Verify the verification URL contains the FRONTEND_URL
            $expectedUrl = $frontendUrl . '/verify-email?token=' . $token;
            return str_contains($htmlContent, $expectedUrl);
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }

    /**
     * Test that sendVerificationEmail logs error when FRONTEND_URL is not set
     * 
     * **Validates: Requirements 2.5, 2.6, 2.7, 2.8**
     */
    public function test_sendVerificationEmail_logs_error_when_frontend_url_not_set(): void
    {
        // Arrange
        unset($_ENV['FRONTEND_URL']);
        config(['app.url' => '']); // Also clear fallback
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-token-123';
        
        // Expect Log::error to be called
        Log::shouldReceive('error')
            ->once()
            ->with('FRONTEND_URL not set in production - verification links will be invalid');
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert
        $this->assertFalse($result, 'Email should fail when FRONTEND_URL is not set');
    }

    /**
     * Test that sendVerificationEmail generates correct verification URL format
     * 
     * **Validates: Requirements 2.5, 2.6, 2.7**
     */
    public function test_sendVerificationEmail_generates_correct_url_format(): void
    {
        // Arrange
        $frontendUrl = 'https://frontend-production.vercel.app';
        $_ENV['FRONTEND_URL'] = $frontendUrl;
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'abc123def456';
        
        // Mock HTTP request
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-id'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert
        $this->assertTrue($result);
        
        // Verify URL format: {FRONTEND_URL}/verify-email?token={token}
        Http::assertSent(function ($request) use ($frontendUrl, $token) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Check exact URL format
            $expectedUrl = $frontendUrl . '/verify-email?token=' . $token;
            $hasCorrectUrl = str_contains($htmlContent, $expectedUrl);
            
            // Ensure no trailing slash issues
            $hasDoubleSlash = str_contains($htmlContent, '//verify-email');
            
            return $hasCorrectUrl && !$hasDoubleSlash;
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }

    /**
     * Test that sendVerificationEmail removes trailing slash from FRONTEND_URL
     * 
     * **Validates: Requirements 2.5, 2.6**
     */
    public function test_sendVerificationEmail_handles_trailing_slash_in_frontend_url(): void
    {
        // Arrange - FRONTEND_URL with trailing slash
        $frontendUrl = 'https://frontend-test.vercel.app/';
        $_ENV['FRONTEND_URL'] = $frontendUrl;
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'test-token';
        
        // Mock HTTP request
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-id'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendVerificationEmail($user, $token);
        
        // Assert
        $this->assertTrue($result);
        
        // Verify no double slash in URL
        Http::assertSent(function ($request) use ($token) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Should not have double slash
            $hasDoubleSlash = str_contains($htmlContent, '//verify-email');
            
            // Should have correct format
            $hasCorrectFormat = str_contains($htmlContent, '/verify-email?token=' . $token);
            
            return !$hasDoubleSlash && $hasCorrectFormat;
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }

    /**
     * Test that sendPasswordResetEmail also uses FRONTEND_URL correctly
     * This verifies preservation of password reset functionality (Requirement 3.10)
     * 
     * **Validates: Requirement 3.10**
     */
    public function test_sendPasswordResetEmail_uses_frontend_url(): void
    {
        // Arrange
        $frontendUrl = 'https://frontend-test.vercel.app';
        $_ENV['FRONTEND_URL'] = $frontendUrl;
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'reset-token-123';
        
        // Mock HTTP request
        Http::fake([
            'api.brevo.com/*' => Http::response(['messageId' => 'test-id'], 200),
        ]);
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendPasswordResetEmail($user, $token);
        
        // Assert
        $this->assertTrue($result, 'Password reset email should be sent successfully');
        
        // Verify the request was made with correct reset URL
        Http::assertSent(function ($request) use ($frontendUrl, $token, $user) {
            $body = $request->data();
            $htmlContent = $body['htmlContent'] ?? '';
            
            // Verify the reset URL contains the FRONTEND_URL
            $expectedUrl = $frontendUrl . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
            return str_contains($htmlContent, $expectedUrl);
        });
        
        // Clean up
        unset($_ENV['FRONTEND_URL']);
    }

    /**
     * Test that sendPasswordResetEmail logs error when FRONTEND_URL is not set
     * 
     * **Validates: Requirement 3.10**
     */
    public function test_sendPasswordResetEmail_logs_error_when_frontend_url_not_set(): void
    {
        // Arrange
        unset($_ENV['FRONTEND_URL']);
        config(['app.url' => '']); // Also clear fallback
        
        $user = User::factory()->create([
            'email' => 'test@student.laverdad.edu.ph',
            'name' => 'Test User',
        ]);
        
        $token = 'reset-token-123';
        
        // Expect Log::error to be called
        Log::shouldReceive('error')
            ->once()
            ->with('FRONTEND_URL not set in production - password reset links will be invalid');
        
        // Act
        $mailService = new MailService();
        $result = $mailService->sendPasswordResetEmail($user, $token);
        
        // Assert
        $this->assertFalse($result, 'Password reset email should fail when FRONTEND_URL is not set');
    }
}

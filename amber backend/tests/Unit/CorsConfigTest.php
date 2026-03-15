<?php

namespace Tests\Unit;

use Tests\TestCase;

class CorsConfigTest extends TestCase
{
    /**
     * Test that CORS configuration uses dynamic environment variable for allowed origins
     */
    public function test_cors_uses_frontend_url_environment_variable(): void
    {
        $allowedOrigins = config('cors.allowed_origins');
        
        // Verify the configuration uses array_filter with env('FRONTEND_URL')
        // The actual FRONTEND_URL value from .env should be present
        $frontendUrl = env('FRONTEND_URL');
        
        if ($frontendUrl) {
            $this->assertContains($frontendUrl, $allowedOrigins, 
                'FRONTEND_URL from environment should be in allowed origins');
        }
        
        // Verify localhost URLs are present for development
        $this->assertContains('http://localhost:3000', $allowedOrigins);
        $this->assertContains('http://localhost:5173', $allowedOrigins);
        
        // Verify array_filter is working (no null/empty values)
        foreach ($allowedOrigins as $origin) {
            $this->assertNotEmpty($origin, 'All origins should be non-empty');
        }
    }

    /**
     * Test that CORS configuration includes Vercel wildcard pattern
     */
    public function test_cors_includes_vercel_wildcard_pattern(): void
    {
        $patterns = config('cors.allowed_origins_patterns');
        
        // Verify Vercel pattern is present
        $this->assertContains('/^https:\/\/.*\.vercel\.app$/', $patterns);
    }

    /**
     * Test that supports_credentials remains true for Sanctum authentication
     */
    public function test_cors_supports_credentials_is_true(): void
    {
        $supportsCredentials = config('cors.supports_credentials');
        
        $this->assertTrue($supportsCredentials);
    }

    /**
     * Test that Vercel pattern matches various deployment URLs
     */
    public function test_vercel_pattern_matches_deployment_urls(): void
    {
        $pattern = '/^https:\/\/.*\.vercel\.app$/';
        
        // Test various Vercel deployment URLs
        $validUrls = [
            'https://frontend-ten-psi-9hutf2paf3.vercel.app',
            'https://frontend-abc123.vercel.app',
            'https://my-app-production.vercel.app',
            'https://test-deployment-xyz.vercel.app',
        ];
        
        foreach ($validUrls as $url) {
            $this->assertEquals(1, preg_match($pattern, $url), "Pattern should match: $url");
        }
        
        // Test invalid URLs that should NOT match
        $invalidUrls = [
            'http://frontend.vercel.app', // http instead of https
            'https://vercel.app', // no subdomain
            'https://frontend.vercel.com', // wrong TLD
            'https://frontend.vercel.app/', // trailing slash
        ];
        
        foreach ($invalidUrls as $url) {
            $this->assertEquals(0, preg_match($pattern, $url), "Pattern should NOT match: $url");
        }
    }
}

<?php

namespace App\Config;

class Auth
{
    // Session configuration
    const SESSION_NAME = 'clinic_session';
    const SESSION_LIFETIME = 3600; // 1 hour
    
    // Password hashing
    const PASSWORD_ALGO = PASSWORD_ARGON2ID;
    const PASSWORD_OPTIONS = [
        'memory_cost' => 65536,
        'time_cost' => 4,
        'threads' => 3
    ];
    
    // Login throttling
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 900; // 15 minutes
    
    // CSRF protection
    const CSRF_TOKEN_NAME = 'csrf_token';
    const CSRF_TOKEN_LENGTH = 32;
    
    // Remember me
    const REMEMBER_ME_DURATION = 2592000; // 30 days
    const REMEMBER_ME_TOKEN_LENGTH = 64;
    
    // Security headers
    const SECURITY_HEADERS = [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';"
    ];
    
    // Allowed file types for uploads
    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'application/pdf',
        'text/plain'
    ];
    
    // Rate limiting
    const RATE_LIMIT_WINDOW = 60; // 1 minute
    const RATE_LIMIT_MAX_REQUESTS = 100;
    
    // Audit logging
    const AUDIT_LOG_ACTIONS = [
        'CREATE' => 'CREATE',
        'UPDATE' => 'UPDATE',
        'DELETE' => 'DELETE',
        'LOGIN' => 'LOGIN',
        'LOGOUT' => 'LOGOUT',
        'PASSWORD_CHANGE' => 'PASSWORD_CHANGE',
        'PERMISSION_CHANGE' => 'PERMISSION_CHANGE'
    ];
}

<?php

namespace App\Lib;

use App\Config\Auth as AuthConfig;
use App\Config\Database;

class Auth
{
    private $pdo;
    private $user = null;

    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function login($username, $password)
    {
        // Check for login throttling
        if ($this->isThrottled()) {
            throw new \Exception('Too many login attempts. Please try again later.');
        }

        // Get user by username
        $stmt = $this->pdo->prepare("
            SELECT u.*, d.display_name as doctor_name, d.specialty 
            FROM users u 
            LEFT JOIN doctors d ON u.id = d.user_id 
            WHERE u.username = ? AND u.is_active = 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->recordFailedLogin();
            throw new \Exception('Invalid credentials');
        }

        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            $this->recordFailedLogin();
            throw new \Exception('Invalid credentials');
        }

        // Reset failed login attempts
        $this->resetFailedLogins();

        // Create session
        $this->createSession($user);

        // Update last login
        $this->updateLastLogin($user['id']);

        return $user;
    }

    /**
     * Establish a REAL cookie-backed web session from a valid mobile Bearer
     * access token. Used by the mobile→web bridge so the in-app browser lands
     * already signed in. Unlike checkBearerToken() (request-scoped, no cookie),
     * this calls createSession() so the browser keeps the PHPSESSID. Returns the
     * user row on success, or false if the token is malformed / invalid.
     */
    public function loginWithMobileToken($rawToken)
    {
        $raw = strtolower(trim((string) $rawToken));
        if (!preg_match('/^[a-f0-9]{64}$/', $raw)) {
            return false;
        }

        if (!class_exists('App\\Lib\\MobileToken')) {
            require_once __DIR__ . '/MobileToken.php';
        }

        $token = (new MobileToken($this->pdo))->verifyAccess($raw);
        if (!$token) {
            return false;
        }

        $user = $this->getUserById($token['user_id']);
        if (!$user) {
            return false;
        }

        $this->user = $user;
        $this->createSession($user);
        $this->updateLastLogin($user['id']);

        return $user;
    }

    public function logout()
    {
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Clear session
        session_destroy();
    }

    public function check()
    {
        // Check if user is already loaded
        if ($this->user) {
            // Verify session is still valid (not expired due to inactivity)
            if (!$this->isSessionValid()) {
                // Store expiration message before logout
                $expiredMessage = 'Your session has expired due to inactivity. Please log in again.';
                $this->logout();
                
                // Start new session to store expiration message
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['session_expired'] = true;
                $_SESSION['expired_message'] = $expiredMessage;
                
                return false;
            }
            // Update last activity on each request
            $this->updateLastActivity();
            return true;
        }

        // Check session
        if (isset($_SESSION['user_id'])) {
            // Check if session has expired due to inactivity
            if (!$this->isSessionValid()) {
                // Store expiration message before logout
                $expiredMessage = 'Your session has expired due to inactivity. Please log in again.';
                $this->logout();
                
                // Start new session to store expiration message
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                $_SESSION['session_expired'] = true;
                $_SESSION['expired_message'] = $expiredMessage;
                
                return false;
            }
            
            $this->user = $this->getUserById($_SESSION['user_id']);
            if ($this->user) {
                // Update last activity on each request
                $this->updateLastActivity();
                return true;
            }
        }

        // Check remember me token
        if (isset($_COOKIE['remember_token'])) {
            $this->user = $this->getUserByRememberToken($_COOKIE['remember_token']);
            if ($this->user) {
                $this->createSession($this->user);
                // Update last activity on each request
                $this->updateLastActivity();
                return true;
            }
        }

        // Mobile API: opaque Bearer access token (no session / no cookie).
        // Runs LAST so the web Session/Cookie path is completely unaffected.
        if ($this->checkBearerToken()) {
            return true;
        }

        return false;
    }

    /**
     * Mobile API authentication: resolve an opaque Bearer access token into a
     * user, mirroring the session shape so every existing controller gate
     * ($this->auth->check()/user()/requireRole()) and clinic scoping keep
     * working unchanged. No cookie is persisted — the mobile client is
     * stateless and sends none, so this never becomes a web session.
     */
    private function checkBearerToken()
    {
        $raw = $this->getBearerToken();
        if ($raw === null) {
            return false;
        }

        // The mobile token lib is not in the composer classmap; load on demand.
        if (!class_exists('App\\Lib\\MobileToken')) {
            require_once __DIR__ . '/MobileToken.php';
        }

        $token = (new MobileToken($this->pdo))->verifyAccess($raw);
        if (!$token) {
            return false;
        }

        // Re-fetch the user every request (enforces is_active immediately, just
        // like the session path) rather than trusting a cached token payload.
        $user = $this->getUserById($token['user_id']);
        if (!$user) {
            return false;
        }

        $this->user = $user;

        // Hydrate the request-scoped session shape used everywhere downstream.
        $_SESSION['user_id']       = $user['id'];
        $_SESSION['user']          = $user;
        $_SESSION['role']          = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['mobile_token']  = true;

        return true;
    }

    /** Extract an opaque Bearer token (64 hex chars) from the Authorization header. */
    private function getBearerToken()
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
            ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
            ?? '';

        if ($header === '' && function_exists('getallheaders')) {
            foreach (getallheaders() as $k => $v) {
                if (strtolower($k) === 'authorization') {
                    $header = $v;
                    break;
                }
            }
        }

        if (preg_match('/^Bearer\s+([a-f0-9]{64})$/i', trim((string) $header), $m)) {
            return strtolower($m[1]);
        }
        return null;
    }
    
    /**
     * Check if session is still valid (not expired due to inactivity)
     * Session expires after 1 minute (60 seconds) for testing
     * TODO: Change back to 4 hours (14400 seconds) after testing
     */
    private function isSessionValid()
    {
        if (!isset($_SESSION['last_activity'])) {
            return false;
        }
        
        $inactivityTimeout = 4 * 60 * 60; // 4 hours in seconds
        $timeSinceLastActivity = time() - $_SESSION['last_activity'];
        
        return $timeSinceLastActivity < $inactivityTimeout;
    }
    
    /**
     * Get session timeout in seconds
     */
    public function getSessionTimeout()
    {
        return 4 * 60 * 60; // 4 hours in seconds
    }
    
    /**
     * Get remaining session time in seconds
     * This method does NOT update last_activity to allow accurate time tracking
     */
    public function getRemainingSessionTime()
    {
        if (!isset($_SESSION['last_activity'])) {
            return 0;
        }
        
        $timeout = $this->getSessionTimeout();
        $timeSinceLastActivity = time() - $_SESSION['last_activity'];
        $remaining = $timeout - $timeSinceLastActivity;
        
        return max(0, $remaining);
    }
    
    /**
     * Check authentication without updating last_activity
     * Used for API endpoints that need to check session time
     */
    public function checkWithoutUpdate()
    {
        if ($this->user) {
            return $this->isSessionValid();
        }

        if (isset($_SESSION['user_id'])) {
            if (!$this->isSessionValid()) {
                return false;
            }
            $this->user = $this->getUserById($_SESSION['user_id']);
            return $this->user !== false;
        }

        if (isset($_COOKIE['remember_token'])) {
            $this->user = $this->getUserByRememberToken($_COOKIE['remember_token']);
            if ($this->user) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Update last activity timestamp
     */
    private function updateLastActivity()
    {
        $_SESSION['last_activity'] = time();
    }

    public function user()
    {
        if (!$this->check()) {
            return null;
        }
        return $this->user;
    }

    public function requireAuth()
    {
        if (!$this->check()) {
            // Check if this is an API request
            if ($this->isApiRequest()) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            \App\Lib\UrlHelper::redirect('/login');
        }
    }

    public function requireRole($roles)
    {
        $this->requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        // Check if admin is in View As mode
        $currentRole = $this->getCurrentRole();
        $originalRole = $this->getOriginalRole();
        
        // Special handling for admin functions when in View As mode
        if ($this->isViewAsMode() && in_array('admin', $roles)) {
            // If trying to access admin functions while in View As mode,
            // check if original role was admin
            if ($originalRole === 'admin') {
                return; // Allow access
            } else {
                if ($this->isApiRequest()) {
                    http_response_code(403);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['error' => 'Access denied - Admin privileges required'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    exit;
                }
                http_response_code(403);
                throw new \Exception('Access denied - Admin privileges required');
            }
        }
        
        // Normal role check
        if (!in_array($currentRole, $roles)) {
            if ($this->isApiRequest()) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['error' => 'Access denied'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                exit;
            }
            http_response_code(403);
            throw new \Exception('Access denied');
        }
    }
    
    /**
     * Check if the current request is an API request
     */
    private function isApiRequest()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $acceptHeader = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        
        // Check if URI starts with /api/
        if (strpos($uri, '/api/') === 0) {
            return true;
        }
        
        // Check if Accept header contains application/json
        if (strpos($acceptHeader, 'application/json') !== false) {
            return true;
        }
        
        // Check if X-Requested-With header is XMLHttpRequest
        if (strtolower($requestedWith) === 'xmlhttprequest') {
            return true;
        }
        
        return false;
    }

    /**
     * Get the current effective role (considering View As mode)
     */
    public function getCurrentRole()
    {
        if (!$this->check()) {
            return null;
        }
        
        // If admin is in View As mode, return the viewed role
        if ($this->isViewAsMode()) {
            return $_SESSION['view_as_role'] ?? $this->user['role'];
        }
        
        return $this->user['role'];
    }

    /**
     * Check if admin is in View As mode
     */
    public function isViewAsMode()
    {
        return isset($_SESSION['view_as_mode']) && 
               $_SESSION['view_as_mode'] === true && 
               isset($this->user['role']) && 
               $this->user['role'] === 'admin';
    }

    /**
     * Start View As mode for admin
     */
    public function startViewAs($role)
    {
        if (!isset($this->user['role']) || $this->user['role'] !== 'admin') {
            throw new \Exception('Only administrators can use View As feature');
        }
        
        $allowedRoles = ['doctor', 'secretary'];
        if (!in_array($role, $allowedRoles)) {
            throw new \Exception('Invalid role for View As');
        }
        
        $_SESSION['view_as_mode'] = true;
        $_SESSION['view_as_role'] = $role;
        $_SESSION['original_role'] = $this->user['role'];
        
        // Update the user object to reflect the viewed role
        $this->user['role'] = $role;
        $_SESSION['user']['role'] = $role;
    }

    /**
     * Stop View As mode and return to admin role
     */
    public function stopViewAs()
    {
        if (!$this->isViewAsMode()) {
            return;
        }
        
        $_SESSION['view_as_mode'] = false;
        unset($_SESSION['view_as_role']);
        
        // Restore original admin role
        $this->user['role'] = $_SESSION['original_role'] ?? 'admin';
        $_SESSION['user']['role'] = $this->user['role'];
        unset($_SESSION['original_role']);
    }

    /**
     * Get the original role before View As
     */
    public function getOriginalRole()
    {
        return $_SESSION['original_role'] ?? $this->user['role'];
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        // Verify current password
        $stmt = $this->pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            throw new \Exception('Current password is incorrect');
        }

        // Validate new password
        $this->validatePassword($newPassword);

        // Hash new password
        $newHash = password_hash($newPassword, AuthConfig::PASSWORD_ALGO, AuthConfig::PASSWORD_OPTIONS);

        // Update password
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);

        // Invalidate all other sessions
        $this->invalidateOtherSessions($userId);

        return true;
    }

    private function validatePassword($password)
    {
        if (strlen($password) < AuthConfig::MIN_PASSWORD_LENGTH) {
            throw new \Exception('Password must be at least ' . AuthConfig::MIN_PASSWORD_LENGTH . ' characters long');
        }

        if (AuthConfig::PASSWORD_REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            throw new \Exception('Password must contain at least one uppercase letter');
        }

        if (AuthConfig::PASSWORD_REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            throw new \Exception('Password must contain at least one lowercase letter');
        }

        if (AuthConfig::PASSWORD_REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            throw new \Exception('Password must contain at least one number');
        }

        if (AuthConfig::PASSWORD_REQUIRE_SPECIAL && !preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new \Exception('Password must contain at least one special character');
        }
    }

    private function createSession($user)
    {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['role'] = $user['role'];
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time(); // Track when user logged in
    }

    private function getUserById($id)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, d.display_name as doctor_name, d.specialty 
            FROM users u 
            LEFT JOIN doctors d ON u.id = d.user_id 
            WHERE u.id = ? AND u.is_active = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    private function getUserByRememberToken($token)
    {
        $stmt = $this->pdo->prepare("
            SELECT u.*, d.display_name as doctor_name, d.specialty 
            FROM users u 
            LEFT JOIN doctors d ON u.id = d.user_id 
            JOIN user_sessions s ON u.id = s.user_id 
            WHERE s.session_id = ? AND s.expires_at > NOW() AND u.is_active = 1
        ");
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    private function updateLastLogin($userId)
    {
        $stmt = $this->pdo->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
        $stmt->execute([$userId]);
    }

    private function isThrottled()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "failed_login_{$ip}";
        
        if (isset($_SESSION[$key])) {
            $attempts = $_SESSION[$key]['count'];
            $lastAttempt = $_SESSION[$key]['time'];
            
            if ($attempts >= AuthConfig::MAX_LOGIN_ATTEMPTS && 
                (time() - $lastAttempt) < AuthConfig::LOCKOUT_DURATION) {
                return true;
            }
        }
        
        return false;
    }

    private function recordFailedLogin()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "failed_login_{$ip}";
        
        if (!isset($_SESSION[$key])) {
            $_SESSION[$key] = ['count' => 0, 'time' => time()];
        }
        
        $_SESSION[$key]['count']++;
        $_SESSION[$key]['time'] = time();
    }

    private function resetFailedLogins()
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = "failed_login_{$ip}";
        unset($_SESSION[$key]);
    }

    private function invalidateOtherSessions($userId)
    {
        // Clear all sessions for this user
        $stmt = $this->pdo->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Also revoke any mobile (Bearer) tokens for this user so a password
        // change immediately logs out every device. Non-fatal on un-migrated
        // installs (the table may not exist yet).
        try {
            if (!class_exists('App\\Lib\\MobileToken')) {
                require_once __DIR__ . '/MobileToken.php';
            }
            (new MobileToken($this->pdo))->revokeAllForUser($userId, 'password_change');
        } catch (\Throwable $e) {
            // ignore — mobile token table not present
        }

        // Clear current session data (will force re-login)
        session_destroy();
        session_start();
    }

    public function createRememberToken($userId)
    {
        $token = bin2hex(random_bytes(AuthConfig::REMEMBER_ME_TOKEN_LENGTH / 2));
        $expires = date('Y-m-d H:i:s', time() + AuthConfig::REMEMBER_ME_DURATION);
        
        $stmt = $this->pdo->prepare("
            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $userId, 
            $token, 
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null,
            $expires
        ]);
        
        return $token;
    }

    public function setRememberMe($userId)
    {
        $token = $this->createRememberToken($userId);
        setcookie('remember_token', $token, time() + AuthConfig::REMEMBER_ME_DURATION, '/', '', true, true);
    }
}


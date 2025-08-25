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
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function login($email, $password)
    {
        // Check for login throttling
        if ($this->isThrottled()) {
            throw new \Exception('Too many login attempts. Please try again later.');
        }

        // Get user by email
        $stmt = $this->pdo->prepare("
            SELECT u.*, d.display_name as doctor_name, d.specialty 
            FROM users u 
            LEFT JOIN doctors d ON u.id = d.user_id 
            WHERE u.email = ? AND u.is_active = 1
        ");
        $stmt->execute([$email]);
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

    public function logout()
    {
        // Clear session
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
    }

    public function check()
    {
        if ($this->user) {
            return true;
        }

        // Check session
        if (isset($_SESSION['user_id'])) {
            $this->user = $this->getUserById($_SESSION['user_id']);
            if ($this->user) {
                return true;
            }
        }

        // Check remember me token
        if (isset($_COOKIE['remember_token'])) {
            $this->user = $this->getUserByRememberToken($_COOKIE['remember_token']);
            if ($this->user) {
                $this->createSession($this->user);
                return true;
            }
        }

        return false;
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
            header('Location: /login');
            exit;
        }
    }

    public function requireRole($roles)
    {
        $this->requireAuth();
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        if (!in_array($this->user['role'], $roles)) {
            http_response_code(403);
            throw new \Exception('Access denied');
        }
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

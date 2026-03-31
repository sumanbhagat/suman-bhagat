<?php
/**
 * Authentication Class
 * Handles user login, logout, and session management
 */
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/../database/connection.php';

class Auth {
    private $db;
    
    public function __construct() {
        startSecureSession();
        $this->db = getDB();
    }
    
    /**
     * Login user
     */
    public function login($username, $password, $remember = false) {
        $ip_address = getClientIP();
        
        // Check rate limiting
        if (!checkRateLimit($ip_address, 'login', 5, 900)) { // 5 attempts per 15 minutes
            return ['success' => false, 'error' => 'Too many login attempts. Please try again later.'];
        }
        
        try {
            // Find user by username or email
            $stmt = $this->db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
            
            // Verify password
            if (!verifyPassword($password, $user['password_hash'])) {
                // Log failed attempt
                $this->logFailedLogin($username, $ip_address);
                return ['success' => false, 'error' => 'Invalid credentials'];
            }
            
            // Check if account is locked
            if ($this->isAccountLocked($user['id'])) {
                return ['success' => false, 'error' => 'Account is temporarily locked due to too many failed attempts'];
            }
            
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            $_SESSION['login_time'] = time();
            
            // Update last login
            $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            // Create remember token if requested
            if ($remember) {
                $this->createRememberToken($user['id']);
            }
            
            // Log successful login
            logActivity($user['id'], 'login', 'users', $user['id']);
            
            return ['success' => true, 'user' => $user];
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'An error occurred. Please try again.'];
        }
    }
    
    /**
     * Register new user
     */
    public function register($username, $email, $password, $full_name) {
        // Validate inputs
        if (strlen($username) < 3 || strlen($username) > 50) {
            return ['success' => false, 'error' => 'Username must be between 3 and 50 characters'];
        }
        
        if (!validateEmail($email)) {
            return ['success' => false, 'error' => 'Please enter a valid email address'];
        }
        
        if (!isStrongPassword($password)) {
            return ['success' => false, 'error' => 'Password must be at least 8 characters with uppercase, lowercase, and number'];
        }
        
        if (empty($full_name) || strlen($full_name) < 2) {
            return ['success' => false, 'error' => 'Please enter a valid full name'];
        }
        
        try {
            // Check if username or email exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'error' => 'Username or email already exists'];
            }
            
            // Hash password
            $password_hash = hashPassword($password);
            
            // Insert user
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, 'editor')");
            $stmt->execute([$username, $email, $password_hash, $full_name]);
            
            $user_id = $this->db->lastInsertId();
            
            // Log activity
            logActivity($user_id, 'register', 'users', $user_id);
            
            return ['success' => true, 'user_id' => $user_id];
            
        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            logActivity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
        }
        
        // Clear session
        session_destroy();
        
        // Clear remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
            
            // Remove from database
            try {
                $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE session_token = ?");
                $stmt->execute([$_COOKIE['remember_token']]);
            } catch (Exception $e) {
                error_log("Error removing remember token: " . $e->getMessage());
            }
        }
        
        return true;
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            // Check session timeout (30 minutes of inactivity)
            if (time() - $_SESSION['login_time'] > 1800) {
                $this->logout();
                return false;
            }
            
            // Update activity time
            $_SESSION['login_time'] = time();
            return true;
        }
        
        // Check remember me cookie
        if (isset($_COOKIE['remember_token'])) {
            return $this->validateRememberToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    /**
     * Require authentication
     */
    public function requireAuth() {
        if (!$this->isLoggedIn()) {
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            secureRedirect('login.php');
        }
    }
    
    /**
     * Require admin privileges
     */
    public function requireAdmin() {
        $this->requireAuth();
        
        if (!$this->isAdmin()) {
            $_SESSION['error'] = 'Access denied. Admin privileges required.';
            secureRedirect('dashboard.php');
        }
    }
    
    /**
     * Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id, username, email, full_name, role, avatar, last_login FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch();
        } catch (Exception $e) {
            return null;
        }
    }
    
    /**
     * Update user profile
     */
    public function updateProfile($user_id, $data) {
        try {
            $allowed_fields = ['full_name', 'email', 'avatar'];
            $updates = [];
            $values = [];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowed_fields)) {
                    $updates[] = "$field = ?";
                    $values[] = sanitizeInput($value);
                }
            }
            
            if (empty($updates)) {
                return ['success' => false, 'error' => 'No valid fields to update'];
            }
            
            $values[] = $user_id;
            $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            logActivity($user_id, 'profile_update', 'users', $user_id);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Update failed'];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($user_id, $current_password, $new_password) {
        try {
            // Verify current password
            $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            
            if (!$user || !verifyPassword($current_password, $user['password_hash'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }
            
            // Validate new password
            if (!isStrongPassword($new_password)) {
                return ['success' => false, 'error' => 'New password does not meet strength requirements'];
            }
            
            // Update password
            $new_hash = hashPassword($new_password);
            $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$new_hash, $user_id]);
            
            logActivity($user_id, 'password_change', 'users', $user_id);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Password change failed'];
        }
    }
    
    /**
     * Create remember me token
     */
    private function createRememberToken($user_id) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        $ip_address = getClientIP();
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        try {
            $stmt = $this->db->prepare("INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $token, $ip_address, $user_agent, $expires]);
            
            // Set cookie (30 days)
            setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
            
        } catch (Exception $e) {
            error_log("Remember token creation error: " . $e->getMessage());
        }
    }
    
    /**
     * Validate remember me token
     */
    private function validateRememberToken($token) {
        try {
            $stmt = $this->db->prepare("SELECT us.*, u.username, u.role, u.full_name, u.is_active FROM user_sessions us JOIN users u ON us.user_id = u.id WHERE us.session_token = ? AND us.expires_at > NOW() AND u.is_active = 1");
            $stmt->execute([$token]);
            $session = $stmt->fetch();
            
            if ($session) {
                // Restore session
                $_SESSION['user_id'] = $session['user_id'];
                $_SESSION['username'] = $session['username'];
                $_SESSION['role'] = $session['role'];
                $_SESSION['full_name'] = $session['full_name'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Remember token validation error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log failed login attempt
     */
    private function logFailedLogin($username, $ip_address) {
        try {
            $stmt = $this->db->prepare("INSERT INTO login_attempts (ip_address, username, attempt_time, is_successful) VALUES (?, ?, NOW(), 0)");
            $stmt->execute([$ip_address, $username]);
        } catch (Exception $e) {
            error_log("Failed login logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Check if account is locked
     */
    private function isAccountLocked($user_id) {
        try {
            // Check for 5 failed attempts in last 15 minutes
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM login_attempts WHERE username = (SELECT username FROM users WHERE id = ?) AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND is_successful = 0");
            $stmt->execute([$user_id]);
            $failed_attempts = $stmt->fetchColumn();
            
            return $failed_attempts >= 5;
            
        } catch (Exception $e) {
            return false;
        }
    }
}

// Global auth instance
$auth = new Auth();
?>

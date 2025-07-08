<?php
require_once 'Database.php';
require_once 'User.php';

class Auth {
    private $db;
    private $user;

    public function __construct() {
        $this->db = new Database();
        $this->user = new User();
        
        // เริ่ม session หากยังไม่เริ่ม
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login($username, $password, $rememberMe = false) {
        try {
            // ค้นหาผู้ใช้ด้วย username หรือ email
            $user = $this->user->getUserByUsername($username);
            if (!$user) {
                $user = $this->user->getUserByEmail($username);
            }

            if (!$user) {
                return ['success' => false, 'message' => 'ไม่พบผู้ใช้นี้ในระบบ'];
            }

            if (!$this->user->verifyPassword($password, $user['password'])) {
                return ['success' => false, 'message' => 'รหัสผ่านไม่ถูกต้อง'];
            }

            // สร้าง session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['login_time'] = time();

            // อัพเดทเวลา login ล่าสุด
            $this->user->updateLastLogin($user['id']);

            // สร้าง session token ถ้าเลือก Remember Me
            if ($rememberMe) {
                $this->createRememberToken($user['id']);
            }

            return [
                'success' => true, 
                'message' => 'เข้าสู่ระบบสำเร็จ',
                'user' => [
                    'id' => $user['id'],
                    'username' => $user['username'],
                    'full_name' => $user['full_name'],
                    'role' => $user['role']
                ]
            ];

        } catch (Exception $e) {
            return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการเข้าสู่ระบบ'];
        }
    }

    public function logout() {
        // ลบ remember token
        if (isset($_COOKIE['remember_token'])) {
            $this->deleteRememberToken($_COOKIE['remember_token']);
            setcookie('remember_token', '', time() - 3600, '/');
        }

        // ลบ session
        session_unset();
        session_destroy();

        return ['success' => true, 'message' => 'ออกจากระบบสำเร็จ'];
    }

    public function isLoggedIn() {
        // ตรวจสอบ session
        if (isset($_SESSION['user_id']) && isset($_SESSION['username'])) {
            return true;
        }

        // ตรวจสอบ remember token
        if (isset($_COOKIE['remember_token'])) {
            return $this->validateRememberToken($_COOKIE['remember_token']);
        }

        return false;
    }

    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'full_name' => $_SESSION['full_name'],
            'role' => $_SESSION['role']
        ];
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit();
        }
    }

    public function requireAdmin() {
        $this->requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            header('Location: index.php?error=access_denied');
            exit();
        }
    }

    public function hasPermission($action) {
        if (!$this->isLoggedIn()) {
            return false;
        }

        // Admin มีสิทธิ์ทุกอย่าง
        if ($_SESSION['role'] === 'admin') {
            return true;
        }

        // User ธรรมดามีสิทธิ์เฉพาะการดู
        $allowedActions = ['view', 'read'];
        return in_array($action, $allowedActions);
    }

    private function createRememberToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 วัน

        $sql = "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent, expires_at) 
                VALUES (?, ?, ?, ?, ?)";
        
        $params = [
            $userId,
            $token,
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expiresAt
        ];

        $this->db->execute($sql, $params);

        // ตั้ง cookie
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
        
        return $token;
    }

    private function validateRememberToken($token) {
        $sql = "SELECT s.*, u.* FROM user_sessions s 
                JOIN users u ON s.user_id = u.id 
                WHERE s.session_token = ? AND s.expires_at > NOW() AND u.is_active = 1";
        
        $session = $this->db->fetch($sql, [$token]);
        
        if ($session) {
            // สร้าง session ใหม่
            $_SESSION['user_id'] = $session['user_id'];
            $_SESSION['username'] = $session['username'];
            $_SESSION['full_name'] = $session['full_name'];
            $_SESSION['role'] = $session['role'];
            $_SESSION['login_time'] = time();

            return true;
        }

        return false;
    }

    private function deleteRememberToken($token) {
        $sql = "DELETE FROM user_sessions WHERE session_token = ?";
        return $this->db->execute($sql, [$token]);
    }

    public function cleanExpiredSessions() {
        $sql = "DELETE FROM user_sessions WHERE expires_at < NOW()";
        return $this->db->execute($sql);
    }
}
?>

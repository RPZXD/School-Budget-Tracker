<?php
require_once 'Database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getUserById($id) {
        $sql = "SELECT id, username, email, full_name, role, is_active, last_login, created_at 
                FROM users WHERE id = ? AND is_active = 1";
        return $this->db->fetch($sql, [$id]);
    }

    public function getUserByUsername($username) {
        $sql = "SELECT * FROM users WHERE username = ? AND is_active = 1";
        return $this->db->fetch($sql, [$username]);
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
        return $this->db->fetch($sql, [$email]);
    }

    public function createUser($data) {
        $sql = "INSERT INTO users (username, email, password, full_name, role) 
                VALUES (?, ?, ?, ?, ?)";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $params = [
            $data['username'],
            $data['email'],
            $hashedPassword,
            $data['full_name'],
            $data['role'] ?? 'user'
        ];
        
        $this->db->execute($sql, $params);
        return $this->db->lastInsertId();
    }

    public function updateUser($id, $data) {
        $fields = [];
        $params = [];
        
        if (isset($data['email'])) {
            $fields[] = "email = ?";
            $params[] = $data['email'];
        }
        
        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = $data['full_name'];
        }
        
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        
        if (isset($data['password'])) {
            $fields[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->execute($sql, $params);
    }

    public function updateLastLogin($id) {
        $sql = "UPDATE users SET last_login = NOW() WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }

    public function verifyPassword($plainPassword, $hashedPassword) {
        return password_verify($plainPassword, $hashedPassword);
    }

    public function getAllUsers() {
        $sql = "SELECT id, username, email, full_name, role, is_active, last_login, created_at 
                FROM users ORDER BY created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function toggleUserStatus($id) {
        $sql = "UPDATE users SET is_active = NOT is_active WHERE id = ?";
        return $this->db->execute($sql, [$id]);
    }
}
?>

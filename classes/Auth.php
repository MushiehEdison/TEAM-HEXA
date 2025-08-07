<?php
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database(); // No need to call connect() - connection happens in constructor
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT id, username, email, password, full_name, role, status FROM users WHERE (username = :username OR email = :username) AND status = 'active'";
            $this->db->query($query);
            $this->db->bind(':username', $username);
            $user = $this->db->single();
            
            if ($user && password_verify($password, $user->password)) {
                $_SESSION['user_id'] = $user->id;
                $_SESSION['username'] = $user->username;
                $_SESSION['full_name'] = $user->full_name;
                $_SESSION['role'] = $user->role;
                $_SESSION['email'] = $user->email;
                
                // Update last login
                $update_query = "UPDATE users SET updated_at = CURRENT_TIMESTAMP WHERE id = :id";
                $this->db->query($update_query);
                $this->db->bind(':id', $user->id);
                $this->db->execute();
                
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function logout() {
        session_destroy();
        return true;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }
    
    public function isAdmin() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }
    
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'username' => $_SESSION['username'] ?? '',
                'full_name' => $_SESSION['full_name'] ?? '',
                'role' => $_SESSION['role'] ?? '',
                'email' => $_SESSION['email'] ?? ''
            ];
        }
        return null;
    }
    
    public function createUser($username, $email, $password, $full_name, $role = 'staff') {
        try {
            // Check if username or email already exists
            $check_query = "SELECT id FROM users WHERE username = :username OR email = :email";
            $this->db->query($check_query);
            $this->db->bind(':username', $username);
            $this->db->bind(':email', $email);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $query = "INSERT INTO users (username, email, password, full_name, role) VALUES (:username, :email, :password, :full_name, :role)";
            $this->db->query($query);
            $this->db->bind(':username', $username);
            $this->db->bind(':email', $email);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':full_name', $full_name);
            $this->db->bind(':role', $role);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'User created successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to create user'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function updatePassword($user_id, $current_password, $new_password) {
        try {
            // Verify current password
            $query = "SELECT password FROM users WHERE id = :id";
            $this->db->query($query);
            $this->db->bind(':id', $user_id);
            $user = $this->db->single();
            
            if (!password_verify($current_password, $user->password)) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $update_query = "UPDATE users SET password = :password WHERE id = :id";
            $this->db->query($update_query);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':id', $user_id);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Password updated successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to update password'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
}
?>
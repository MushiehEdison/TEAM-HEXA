<?php
// classes/Staff.php
require_once 'Database.php';

class Staff {
    private $db;
    
    public function __construct() {
        $this->db = new Database(); // Database now auto-connects in constructor
    }
    
    public function getAllStaff() {
        try {
            $this->db->query("SELECT id, username, email, full_name, role, status, created_at, updated_at FROM users ORDER BY created_at DESC");
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getStaffById($id) {
        try {
            $this->db->query("SELECT id, username, email, full_name, role, status, created_at FROM users WHERE id = :id");
            $this->db->bind(':id', $id);
            return $this->db->single();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function createStaff($username, $email, $password, $full_name, $role = 'staff') {
        try {
            $this->db->beginTransaction();

            // Check if username or email already exists
            $this->db->query("SELECT id FROM users WHERE username = :username OR email = :email");
            $this->db->bind(':username', $username);
            $this->db->bind(':email', $email);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $this->db->query("INSERT INTO users (username, email, password, full_name, role, status) 
                     VALUES (:username, :email, :password, :full_name, :role, 'active')");
            $this->db->bind(':username', $username);
            $this->db->bind(':email', $email);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':full_name', $full_name);
            $this->db->bind(':role', $role);
            
            if ($this->db->execute()) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Staff member created successfully', 'staff_id' => $this->db->lastInsertId()];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to create staff member'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function updateStaff($id, $username, $email, $full_name, $role, $status) {
        try {
            $this->db->beginTransaction();

            // Check if username or email already exists (excluding current user)
            $this->db->query("SELECT id FROM users WHERE (username = :username OR email = :email) AND id != :id");
            $this->db->bind(':username', $username);
            $this->db->bind(':email', $email);
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Username or email already exists'];
            }
            
            $this->db->query("UPDATE users SET username = :username, email = :email, full_name = :full_name, 
                     role = :role, status = :status WHERE id = :id");
            $this->db->bind(':username', $username);
            $this->db->bind(':email', $email);
            $this->db->bind(':full_name', $full_name);
            $this->db->bind(':role', $role);
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Staff member updated successfully'];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to update staff member'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function deleteStaff($id) {
        try {
            $this->db->beginTransaction();

            // Check if this is the last admin
            $this->db->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin' AND status = 'active'");
            $admin_count = $this->db->single()->admin_count;
            
            $this->db->query("SELECT role FROM users WHERE id = :id");
            $this->db->bind(':id', $id);
            $user = $this->db->single();
            
            if ($user->role === 'admin' && $admin_count <= 1) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Cannot delete the last admin user'];
            }
            
            $this->db->query("UPDATE users SET status = 'inactive' WHERE id = :id");
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Staff member deactivated successfully'];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to deactivate staff member'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function resetPassword($id, $new_password) {
        try {
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            
            $this->db->query("UPDATE users SET password = :password WHERE id = :id");
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Password reset successfully'];
            }
            
            return ['success' => false, 'message' => 'Failed to reset password'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getStaffStats() {
        try {
            $stats = [];
            
            // Total staff count
            $this->db->query("SELECT COUNT(*) as count FROM users");
            $stats['total_staff'] = $this->db->single()->count;
            
            // Active staff count
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE status = 'active'");
            $stats['active_staff'] = $this->db->single()->count;
            
            // Admin count
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND status = 'active'");
            $stats['admin_count'] = $this->db->single()->count;
            
            // Staff count
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE role = 'staff' AND status = 'active'");
            $stats['staff_count'] = $this->db->single()->count;
            
            // Recent additions (last 30 days)
            $this->db->query("SELECT COUNT(*) as count FROM users WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
            $stats['recent_additions'] = $this->db->single()->count;
            
            return $stats;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function toggleStatus($id) {
        try {
            $this->db->beginTransaction();

            // Get current status
            $this->db->query("SELECT status, role FROM users WHERE id = :id");
            $this->db->bind(':id', $id);
            $user = $this->db->single();
            
            if (!$user) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Check if this is the last admin being deactivated
            if ($user->role === 'admin' && $user->status === 'active') {
                $this->db->query("SELECT COUNT(*) as admin_count FROM users WHERE role = 'admin' AND status = 'active'");
                $admin_count = $this->db->single()->admin_count;
                
                if ($admin_count <= 1) {
                    $this->db->rollBack();
                    return ['success' => false, 'message' => 'Cannot deactivate the last admin user'];
                }
            }
            
            $new_status = $user->status === 'active' ? 'inactive' : 'active';
            
            $this->db->query("UPDATE users SET status = :status WHERE id = :id");
            $this->db->bind(':status', $new_status);
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                $this->db->commit();
                $action = $new_status === 'active' ? 'activated' : 'deactivated';
                return ['success' => true, 'message' => "Staff member {$action} successfully", 'new_status' => $new_status];
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to update status'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getActivityLog($limit = 50) {
        try {
            $this->db->query("SELECT 
                        'user_created' as activity_type,
                        full_name as description,
                        created_at as activity_date,
                        'System' as performed_by
                      FROM users 
                      ORDER BY created_at DESC 
                      LIMIT :limit");
            $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
}
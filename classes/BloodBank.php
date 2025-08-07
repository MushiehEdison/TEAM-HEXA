<?php
// classes/BloodBank.php
require_once 'Database.php';

class BloodBank {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Blood Inventory Management
    public function addBloodInventory($blood_type, $quantity, $collection_date, $expiry_date, $donor_id = null) {
        try {
            $query = "INSERT INTO blood_inventory (blood_type, quantity, collection_date, expiry_date, donor_id) 
                     VALUES (:blood_type, :quantity, :collection_date, :expiry_date, :donor_id)";
            $this->db->query($query);
            $this->db->bind(':blood_type', $blood_type);
            $this->db->bind(':quantity', $quantity);
            $this->db->bind(':collection_date', $collection_date);
            $this->db->bind(':expiry_date', $expiry_date);
            $this->db->bind(':donor_id', $donor_id);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Blood inventory added successfully'];
            }
            return ['success' => false, 'message' => 'Failed to add blood inventory'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getBloodInventory($blood_type = null) {
        try {
            $query = "SELECT bi.*, d.full_name as donor_name 
                     FROM blood_inventory bi 
                     LEFT JOIN donors d ON bi.donor_id = d.id 
                     WHERE bi.status = 'available'";
            
            if ($blood_type) {
                $query .= " AND bi.blood_type = :blood_type";
            }
            
            $query .= " ORDER BY bi.expiry_date ASC";
            
            $this->db->query($query);
            if ($blood_type) {
                $this->db->bind(':blood_type', $blood_type);
            }
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getBloodSummary() {
        try {
            $query = "SELECT 
                        blood_type,
                        SUM(CASE WHEN status = 'available' THEN quantity ELSE 0 END) as available_units,
                        SUM(CASE WHEN status = 'expired' THEN quantity ELSE 0 END) as expired_units,
                        SUM(CASE WHEN status = 'used' THEN quantity ELSE 0 END) as used_units,
                        COUNT(CASE WHEN status = 'available' AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) THEN 1 END) as expiring_soon
                      FROM blood_inventory 
                      GROUP BY blood_type 
                      ORDER BY blood_type";
            $this->db->query($query);
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Donor Management
    public function addDonor($full_name, $phone, $email, $blood_type, $address) {
        try {
            $query = "INSERT INTO donors (full_name, phone, email, blood_type, address) 
                     VALUES (:full_name, :phone, :email, :blood_type, :address)";
            $this->db->query($query);
            $this->db->bind(':full_name', $full_name);
            $this->db->bind(':phone', $phone);
            $this->db->bind(':email', $email);
            $this->db->bind(':blood_type', $blood_type);
            $this->db->bind(':address', $address);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Donor added successfully', 'donor_id' => $this->db->lastInsertId()];
            }
            return ['success' => false, 'message' => 'Failed to add donor'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getDonors($limit = null) {
        try {
            $query = "SELECT * FROM donors ORDER BY created_at DESC";
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $this->db->query($query);
            if ($limit) {
                $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            }
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Blood Request Management
    public function addBloodRequest($patient_name, $blood_type, $quantity_needed, $urgency, $hospital_name, $contact_person, $phone, $request_date, $needed_by) {
        try {
            $query = "INSERT INTO blood_requests (patient_name, blood_type, quantity_needed, urgency, hospital_name, contact_person, phone, request_date, needed_by) 
                     VALUES (:patient_name, :blood_type, :quantity_needed, :urgency, :hospital_name, :contact_person, :phone, :request_date, :needed_by)";
            $this->db->query($query);
            $this->db->bind(':patient_name', $patient_name);
            $this->db->bind(':blood_type', $blood_type);
            $this->db->bind(':quantity_needed', $quantity_needed);
            $this->db->bind(':urgency', $urgency);
            $this->db->bind(':hospital_name', $hospital_name);
            $this->db->bind(':contact_person', $contact_person);
            $this->db->bind(':phone', $phone);
            $this->db->bind(':request_date', $request_date);
            $this->db->bind(':needed_by', $needed_by);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Blood request added successfully'];
            }
            return ['success' => false, 'message' => 'Failed to add blood request'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getBloodRequests($status = null) {
        try {
            $query = "SELECT * FROM blood_requests";
            if ($status) {
                $query .= " WHERE status = :status";
            }
            $query .= " ORDER BY urgency = 'critical' DESC, urgency = 'high' DESC, needed_by ASC";
            
            $this->db->query($query);
            if ($status) {
                $this->db->bind(':status', $status);
            }
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function updateRequestStatus($request_id, $status) {
        try {
            $query = "UPDATE blood_requests SET status = :status WHERE id = :id";
            $this->db->query($query);
            $this->db->bind(':status', $status);
            $this->db->bind(':id', $request_id);
            
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    // Donation Management
    public function addDonation($donor_id, $blood_type, $quantity, $donation_date, $hemoglobin_level, $blood_pressure, $temperature, $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // Add donation record
            $query = "INSERT INTO donations (donor_id, blood_type, quantity, donation_date, hemoglobin_level, blood_pressure, temperature, notes) 
                     VALUES (:donor_id, :blood_type, :quantity, :donation_date, :hemoglobin_level, :blood_pressure, :temperature, :notes)";
            $this->db->query($query);
            $this->db->bind(':donor_id', $donor_id);
            $this->db->bind(':blood_type', $blood_type);
            $this->db->bind(':quantity', $quantity);
            $this->db->bind(':donation_date', $donation_date);
            $this->db->bind(':hemoglobin_level', $hemoglobin_level);
            $this->db->bind(':blood_pressure', $blood_pressure);
            $this->db->bind(':temperature', $temperature);
            $this->db->bind(':notes', $notes);
            
            if ($this->db->execute()) {
                // Calculate expiry date (35 days from donation)
                $expiry_date = date('Y-m-d', strtotime($donation_date . ' + 35 days'));
                
                // Add to blood inventory
                $inventory_result = $this->addBloodInventory($blood_type, $quantity, $donation_date, $expiry_date, $donor_id);
                
                if ($inventory_result['success']) {
                    // Update donor's last donation date
                    $update_donor = "UPDATE donors SET last_donation = :donation_date WHERE id = :donor_id";
                    $this->db->query($update_donor);
                    $this->db->bind(':donation_date', $donation_date);
                    $this->db->bind(':donor_id', $donor_id);
                    $this->db->execute();
                    
                    $this->db->commit();
                    return ['success' => true, 'message' => 'Donation recorded successfully'];
                }
            }
            
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Failed to record donation'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getDonations($limit = null) {
        try {
            $query = "SELECT d.*, don.full_name as donor_name, don.phone as donor_phone 
                     FROM donations d 
                     LEFT JOIN donors don ON d.donor_id = don.id 
                     ORDER BY d.donation_date DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit";
            }
            
            $this->db->query($query);
            if ($limit) {
                $this->db->bind(':limit', $limit, PDO::PARAM_INT);
            }
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Transaction Management
    public function fulfillBloodRequest($request_id, $staff_id) {
        try {
            $this->db->beginTransaction();
            
            // Get request details
            $request_query = "SELECT * FROM blood_requests WHERE id = :id AND status = 'approved'";
            $this->db->query($request_query);
            $this->db->bind(':id', $request_id);
            $request = $this->db->single();
            
            if (!$request) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Request not found or not approved'];
            }
            
            // Check available blood
            $available_query = "SELECT * FROM blood_inventory 
                               WHERE blood_type = :blood_type AND status = 'available' AND quantity > 0 
                               ORDER BY expiry_date ASC";
            $this->db->query($available_query);
            $this->db->bind(':blood_type', $request->blood_type);
            $available_units = $this->db->resultSet();
            
            $total_available = array_sum(array_column($available_units, 'quantity'));
            
            if ($total_available < $request->quantity_needed) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Insufficient blood units available'];
            }
            
            // Fulfill request using FIFO (First In, First Out)
            $remaining_needed = $request->quantity_needed;
            
            foreach ($available_units as $unit) {
                if ($remaining_needed <= 0) break;
                
                $to_use = min($remaining_needed, $unit->quantity);
                
                // Update inventory
                if ($to_use == $unit->quantity) {
                    // Use entire unit
                    $update_query = "UPDATE blood_inventory SET status = 'used', quantity = 0 WHERE id = :id";
                } else {
                    // Use partial unit
                    $update_query = "UPDATE blood_inventory SET quantity = quantity - :used WHERE id = :id";
                }
                
                $this->db->query($update_query);
                $this->db->bind(':id', $unit->id);
                if ($to_use < $unit->quantity) {
                    $this->db->bind(':used', $to_use);
                }
                $this->db->execute();
                
                // Record transaction
                $transaction_query = "INSERT INTO blood_transactions (inventory_id, request_id, transaction_type, quantity, transaction_date, staff_id) 
                                     VALUES (:inventory_id, :request_id, 'request', :quantity, CURDATE(), :staff_id)";
                $this->db->query($transaction_query);
                $this->db->bind(':inventory_id', $unit->id);
                $this->db->bind(':request_id', $request_id);
                $this->db->bind(':quantity', $to_use);
                $this->db->bind(':staff_id', $staff_id);
                $this->db->execute();
                
                $remaining_needed -= $to_use;
            }
            
            // Update request status
            $update_request = "UPDATE blood_requests SET status = 'fulfilled' WHERE id = :id";
            $this->db->query($update_request);
            $this->db->bind(':id', $request_id);
            $this->db->execute();
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Blood request fulfilled successfully'];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Dashboard Statistics with proper error handling
   public function getDashboardStats() {
    $stats = [
        'blood_types' => [],
        'pending_requests' => 0,
        'critical_requests' => 0,
        'expiring_units' => 0,
        'total_donors' => 0,
        'recent_donations' => 0
    ];
    
    try {
        // Total blood units by type
        $blood_query = "SELECT blood_type, SUM(quantity) as total 
                       FROM blood_inventory 
                       WHERE status = 'available' 
                       GROUP BY blood_type";
        $this->db->query($blood_query);
        $blood_result = $this->db->resultSet();
        $stats['blood_types'] = is_array($blood_result) ? $blood_result : [];
        
        // Pending requests
        $requests_query = "SELECT COUNT(*) as count FROM blood_requests WHERE status = 'pending'";
        $this->db->query($requests_query);
        $pending_result = $this->db->single();
        $stats['pending_requests'] = ($pending_result && isset($pending_result->count)) ? (int)$pending_result->count : 0;
        
        // Critical requests
        $critical_query = "SELECT COUNT(*) as count FROM blood_requests WHERE urgency = 'critical' AND status IN ('pending', 'approved')";
        $this->db->query($critical_query);
        $critical_result = $this->db->single();
        $stats['critical_requests'] = ($critical_result && isset($critical_result->count)) ? (int)$critical_result->count : 0;
        
        // Expiring units (within 7 days)
        $expiring_query = "SELECT COUNT(*) as count FROM blood_inventory 
                          WHERE status = 'available' AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $this->db->query($expiring_query);
        $expiring_result = $this->db->single();
        $stats['expiring_units'] = ($expiring_result && isset($expiring_result->count)) ? (int)$expiring_result->count : 0;
        
        // Total donors
        $donors_query = "SELECT COUNT(*) as count FROM donors";
        $this->db->query($donors_query);
        $donors_result = $this->db->single();
        $stats['total_donors'] = ($donors_result && isset($donors_result->count)) ? (int)$donors_result->count : 0;
        
        // Recent donations (last 30 days)
        $recent_donations_query = "SELECT COUNT(*) as count FROM donations WHERE donation_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
        $this->db->query($recent_donations_query);
        $donations_result = $this->db->single();
        $stats['recent_donations'] = ($donations_result && isset($donations_result->count)) ? (int)$donations_result->count : 0;
        
    } catch (PDOException $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
    }
    
    return $stats;
}
    
    // Expiry Management
    public function markExpiredBlood() {
        try {
            $query = "UPDATE blood_inventory SET status = 'expired' WHERE expiry_date < CURDATE() AND status = 'available'";
            $this->db->query($query);
            return $this->db->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getExpiringBlood($days = 7) {
        try {
            $query = "SELECT bi.*, d.full_name as donor_name 
                     FROM blood_inventory bi 
                     LEFT JOIN donors d ON bi.donor_id = d.id 
                     WHERE bi.status = 'available' 
                     AND bi.expiry_date <= DATE_ADD(CURDATE(), INTERVAL :days DAY) 
                     ORDER BY bi.expiry_date ASC";
            
            $this->db->query($query);
            $this->db->bind(':days', $days);
            
            return $this->db->resultSet();
        } catch (PDOException $e) {
            return [];
        }
    }
}
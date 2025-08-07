<?php
// classes/BloodRequest.php
class BloodRequest {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Get all blood requests
    public function getAllRequests() {
        $this->db->query("SELECT * FROM blood_requests ORDER BY request_date DESC");
        return $this->db->resultSet();
    }
    
    // Get pending requests
    public function getPendingRequests() {
        $this->db->query("SELECT * FROM blood_requests WHERE status = 'pending' ORDER BY urgency DESC, request_date");
        return $this->db->resultSet();
    }
    
    // Get approved requests
    public function getApprovedRequests() {
        $this->db->query("SELECT * FROM blood_requests WHERE status = 'approved' ORDER BY needed_by");
        return $this->db->resultSet();
    }
    
    // Get fulfilled requests
    public function getFulfilledRequests() {
        $this->db->query("SELECT * FROM blood_requests WHERE status = 'fulfilled' ORDER BY request_date DESC");
        return $this->db->resultSet();
    }
    
    // Get request statistics with proper error handling - FIXED
    public function getRequestStats() {
        $stats = [];
        
        try {
            // Total requests
            $this->db->query("SELECT COUNT(*) as total FROM blood_requests");
            $result = $this->db->single();
            $stats['total_requests'] = is_array($result) ? (int)$result['total'] : (int)$result->total;
            
            // Pending requests
            $this->db->query("SELECT COUNT(*) as total FROM blood_requests WHERE status = 'pending'");
            $result = $this->db->single();
            $stats['pending_requests'] = is_array($result) ? (int)$result['total'] : (int)$result->total;
            
            // Approved requests
            $this->db->query("SELECT COUNT(*) as total FROM blood_requests WHERE status = 'approved'");
            $result = $this->db->single();
            $stats['approved_requests'] = is_array($result) ? (int)$result['total'] : (int)$result->total;
            
            // Fulfilled requests
            $this->db->query("SELECT COUNT(*) as total FROM blood_requests WHERE status = 'fulfilled'");
            $result = $this->db->single();
            $stats['fulfilled_requests'] = is_array($result) ? (int)$result['total'] : (int)$result->total;
            
            // Rejected requests
            $this->db->query("SELECT COUNT(*) as total FROM blood_requests WHERE status = 'rejected'");
            $result = $this->db->single();
            $stats['rejected_requests'] = is_array($result) ? (int)$result['total'] : (int)$result->total;
            
            // Urgent pending requests
            $this->db->query("SELECT COUNT(*) as total FROM blood_requests WHERE status = 'pending' AND urgency = 'critical'");
            $result = $this->db->single();
            $stats['critical_requests'] = is_array($result) ? (int)$result['total'] : (int)$result->total;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("BloodRequest::getRequestStats() Error: " . $e->getMessage());
            // Return default values if there's an error
            return [
                'total_requests' => 0,
                'pending_requests' => 0,
                'approved_requests' => 0,
                'fulfilled_requests' => 0,
                'rejected_requests' => 0,
                'critical_requests' => 0
            ];
        }
    }
    
    // Add new blood request with validation
    public function addRequest($data) {
        // Validate required fields
        $required = ['patient_name', 'blood_type', 'quantity_needed', 'urgency', 
                    'hospital_name', 'contact_person', 'phone', 'request_date', 'needed_by'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }
        
        // Validate quantity
        if (!is_numeric($data['quantity_needed']) || $data['quantity_needed'] <= 0) {
            throw new Exception("Quantity must be a positive number");
        }
        
        // Validate dates
        if (strtotime($data['needed_by']) < strtotime($data['request_date'])) {
            throw new Exception("Needed by date cannot be before request date");
        }
        
        // Validate blood type
        $validBloodTypes = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        if (!in_array($data['blood_type'], $validBloodTypes)) {
            throw new Exception("Invalid blood type");
        }
        
        // Validate urgency
        $validUrgencies = ['low', 'medium', 'high', 'critical'];
        if (!in_array($data['urgency'], $validUrgencies)) {
            throw new Exception("Invalid urgency level");
        }
        
        try {
            $this->db->query("INSERT INTO blood_requests 
                             (patient_name, blood_type, quantity_needed, urgency, 
                             hospital_name, contact_person, phone, request_date, needed_by, notes, status, created_at)
                             VALUES 
                             (:patient_name, :blood_type, :quantity_needed, :urgency, 
                             :hospital_name, :contact_person, :phone, :request_date, :needed_by, :notes, 'pending', NOW())");
            
            // Bind parameters
            $this->db->bind(':patient_name', trim($data['patient_name']));
            $this->db->bind(':blood_type', $data['blood_type']);
            $this->db->bind(':quantity_needed', (int)$data['quantity_needed']);
            $this->db->bind(':urgency', $data['urgency']);
            $this->db->bind(':hospital_name', trim($data['hospital_name']));
            $this->db->bind(':contact_person', trim($data['contact_person']));
            $this->db->bind(':phone', trim($data['phone']));
            $this->db->bind(':request_date', $data['request_date']);
            $this->db->bind(':needed_by', $data['needed_by']);
            $this->db->bind(':notes', $data['notes'] ?? null);
            
            if ($this->db->execute()) {
                return ['success' => true, 'message' => 'Blood request submitted successfully', 'id' => $this->db->lastInsertId()];
            } else {
                return ['success' => false, 'message' => 'Failed to submit blood request'];
            }
        } catch (Exception $e) {
            error_log("BloodRequest::addRequest() Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    // Approve blood request with transaction safety - FIXED
    public function approveRequest($id) {
        $this->db->beginTransaction();
        
        try {
            // Get request details
            $this->db->query("SELECT * FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $request = $this->db->single();
            
            // Handle both array and object return types
            if (is_array($request)) {
                $request = (object)$request;
            }
            
            if (!$request) {
                throw new Exception("Request not found");
            }
            
            if ($request->status !== 'pending') {
                throw new Exception("Only pending requests can be approved");
            }
            
            // Check inventory availability
            $this->db->query("SELECT COALESCE(SUM(quantity), 0) as available 
                             FROM blood_inventory 
                             WHERE blood_type = :blood_type AND status = 'available'");
            $this->db->bind(':blood_type', $request->blood_type);
            $inventory = $this->db->single();
            
            // Handle both array and object return types
            $available = is_array($inventory) ? (int)$inventory['available'] : (int)$inventory->available;
            
            if ($available < $request->quantity_needed) {
                throw new Exception("Not enough blood available in inventory. Available: $available units, Needed: $request->quantity_needed units");
            }
            
            // Update request status
            $this->db->query("UPDATE blood_requests 
                             SET status = 'approved', updated_at = NOW() 
                             WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Request approved successfully'];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("BloodRequest::approveRequest() Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Reject blood request with proper note handling
    public function rejectRequest($id, $reason) {
        if (empty($reason)) {
            return ['success' => false, 'message' => 'Rejection reason is required'];
        }
        
        $this->db->beginTransaction();
        
        try {
            // Check if request exists and is pending
            $this->db->query("SELECT status, notes FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $current = $this->db->single();
            
            // Handle both array and object return types
            if (is_array($current)) {
                $current = (object)$current;
            }
            
            if (!$current) {
                throw new Exception("Request not found");
            }
            
            if ($current->status !== 'pending') {
                throw new Exception("Only pending requests can be rejected");
            }
            
            $new_notes = ($current->notes ?? '') . "\n\nRejection Reason (" . date('Y-m-d H:i:s') . "): " . $reason;
            
            $this->db->query("UPDATE blood_requests 
                             SET status = 'rejected', 
                             notes = :notes, 
                             updated_at = NOW()
                             WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $this->db->bind(':notes', trim($new_notes));
            
            if ($this->db->execute()) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Request rejected successfully'];
            } else {
                throw new Exception("Database update failed");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("BloodRequest::rejectRequest() Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Fulfill blood request with inventory management - FIXED
    public function fulfillRequest($id) {
        $this->db->beginTransaction();
        
        try {
            // Get request details
            $this->db->query("SELECT * FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $request = $this->db->single();
            
            // Handle both array and object return types
            if (is_array($request)) {
                $request = (object)$request;
            }
            
            if (!$request) {
                throw new Exception("Request not found");
            }
            
            if ($request->status !== 'approved') {
                throw new Exception("Only approved requests can be fulfilled");
            }
            
            // Get available inventory (oldest first)
            $this->db->query("SELECT * FROM blood_inventory 
                             WHERE blood_type = :blood_type AND status = 'available' AND quantity > 0
                             ORDER BY expiry_date ASC");
            $this->db->bind(':blood_type', $request->blood_type);
            $inventory = $this->db->resultSet();
            
            if (empty($inventory)) {
                throw new Exception("No available blood units found");
            }
            
            $quantity_needed = (int)$request->quantity_needed;
            $used_units = [];
            
            // Mark units as used
            foreach ($inventory as $unit) {
                if ($quantity_needed <= 0) break;
                
                // Handle both array and object return types
                if (is_array($unit)) {
                    $unit = (object)$unit;
                }
                
                $available_quantity = (int)$unit->quantity;
                $use_quantity = min($available_quantity, $quantity_needed);
                
                // Update inventory - mark as used
                $this->db->query("UPDATE blood_inventory 
                                 SET status = 'used', 
                                 quantity = quantity - :use_quantity,
                                 updated_at = NOW()
                                 WHERE id = :id");
                $this->db->bind(':use_quantity', $use_quantity);
                $this->db->bind(':id', (int)$unit->id);
                $this->db->execute();
                
                $used_units[] = [
                    'inventory_id' => (int)$unit->id,
                    'quantity' => $use_quantity
                ];
                
                $quantity_needed -= $use_quantity;
            }
            
            if ($quantity_needed > 0) {
                throw new Exception("Not enough blood available to fulfill the entire request. Remaining needed: $quantity_needed units");
            }
            
            // Update request status
            $this->db->query("UPDATE blood_requests 
                             SET status = 'fulfilled', updated_at = NOW()
                             WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            
            // Record transactions
            foreach ($used_units as $unit) {
                $this->db->query("INSERT INTO blood_transactions 
                                 (inventory_id, request_id, transaction_type, quantity, transaction_date, created_at)
                                 VALUES 
                                 (:inventory_id, :request_id, 'request', :quantity, NOW(), NOW())");
                $this->db->bind(':inventory_id', $unit['inventory_id']);
                $this->db->bind(':request_id', (int)$id);
                $this->db->bind(':quantity', $unit['quantity']);
                $this->db->execute();
            }
            
            $this->db->commit();
            return ['success' => true, 'message' => 'Request fulfilled successfully'];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("BloodRequest::fulfillRequest() Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    // Get request details with proper HTML escaping - FIXED
    public function getRequestDetails($id) {
        try {
            $this->db->query("SELECT * FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $request = $this->db->single();
            
            // Handle both array and object return types
            if (is_array($request)) {
                $request = (object)$request;
            }
            
            if (!$request) {
                return ['success' => false, 'message' => 'Request not found'];
            }
            
            // Get transaction history if fulfilled
            $transactions = [];
            if ($request->status === 'fulfilled') {
                $this->db->query("SELECT bt.*, bi.collection_date, bi.expiry_date
                                 FROM blood_transactions bt
                                 JOIN blood_inventory bi ON bt.inventory_id = bi.id
                                 WHERE bt.request_id = :request_id");
                $this->db->bind(':request_id', (int)$id);
                $transactions = $this->db->resultSet();
            }
            
            // Generate HTML for details modal
            $html = '
            <div class="request-details">
                <div class="detail-row">
                    <span class="detail-label">Request ID:</span>
                    <span class="detail-value">#' . str_pad($request->id, 4, '0', STR_PAD_LEFT) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Patient Name:</span>
                    <span class="detail-value">' . htmlspecialchars($request->patient_name) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Blood Type:</span>
                    <span class="detail-value blood-type">' . htmlspecialchars($request->blood_type) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Quantity Needed:</span>
                    <span class="detail-value">' . (int)$request->quantity_needed . ' units</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Urgency:</span>
                    <span class="detail-value urgency-' . strtolower(htmlspecialchars($request->urgency)) . '">' . 
                    htmlspecialchars(ucfirst($request->urgency)) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Hospital:</span>
                    <span class="detail-value">' . htmlspecialchars($request->hospital_name) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Contact Person:</span>
                    <span class="detail-value">' . htmlspecialchars($request->contact_person) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Contact Phone:</span>
                    <span class="detail-value"><a href="tel:' . htmlspecialchars($request->phone) . '">' . htmlspecialchars($request->phone) . '</a></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Request Date:</span>
                    <span class="detail-value">' . date('M j, Y g:i A', strtotime($request->request_date)) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Needed By:</span>
                    <span class="detail-value">' . date('M j, Y', strtotime($request->needed_by)) . '</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value status status-' . htmlspecialchars($request->status) . '">' . 
                    htmlspecialchars(ucfirst($request->status)) . '</span>
                </div>';
            
            if (!empty($request->notes)) {
                $html .= '
                <div class="detail-row">
                    <span class="detail-label">Notes:</span>
                    <span class="detail-value notes-content">' . nl2br(htmlspecialchars($request->notes)) . '</span>
                </div>';
            }
            
            if (!empty($transactions)) {
                $html .= '
                <div class="detail-section">
                    <h4>Fulfillment Details</h4>
                    <table class="transaction-table">
                        <thead>
                            <tr>
                                <th>Unit ID</th>
                                <th>Quantity</th>
                                <th>Collection Date</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>';
                
                foreach ($transactions as $tx) {
                    // Handle both array and object return types
                    if (is_array($tx)) {
                        $tx = (object)$tx;
                    }
                    
                    $daysToExpiry = floor((strtotime($tx->expiry_date) - time()) / (60 * 60 * 24));
                    $expiryClass = $daysToExpiry < 7 ? 'expiry-warning' : ($daysToExpiry < 14 ? 'expiry-caution' : 'expiry-ok');
                    
                    $html .= '
                                <tr>
                                    <td>#' . (int)$tx->inventory_id . '</td>
                                    <td>' . (int)$tx->quantity . ' units</td>
                                    <td>' . date('M j, Y', strtotime($tx->collection_date)) . '</td>
                                    <td class="' . $expiryClass . '">' . date('M j, Y', strtotime($tx->expiry_date)) . '</td>
                                    <td><span class="status-badge status-used">Used</span></td>
                                </tr>';
                }
                
                $html .= '
                        </tbody>
                    </table>
                </div>';
            }
            
            $html .= '</div>';
            
            return ['success' => true, 'html' => $html, 'request' => $request];
            
        } catch (Exception $e) {
            error_log("BloodRequest::getRequestDetails() Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error fetching request details: ' . $e->getMessage()];
        }
    }
    
    // Get blood request history for a patient with search
    public function getPatientRequestHistory($patient_name) {
        try {
            $this->db->query("SELECT * FROM blood_requests 
                             WHERE patient_name LIKE :patient_name
                             ORDER BY request_date DESC
                             LIMIT 50");
            $this->db->bind(':patient_name', '%' . trim($patient_name) . '%');
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("BloodRequest::getPatientRequestHistory() Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Check blood request status - FIXED
    public function checkRequestStatus($id) {
        try {
            $this->db->query("SELECT status FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $result = $this->db->single();
            
            // Handle both array and object return types
            if (is_array($result)) {
                return $result['status'] ?? null;
            } else {
                return $result ? $result->status : null;
            }
        } catch (Exception $e) {
            error_log("BloodRequest::checkRequestStatus() Error: " . $e->getMessage());
            return null;
        }
    }
    
    // Get requests by blood type
    public function getRequestsByBloodType($bloodType) {
        try {
            $this->db->query("SELECT * FROM blood_requests 
                             WHERE blood_type = :blood_type 
                             ORDER BY request_date DESC");
            $this->db->bind(':blood_type', $bloodType);
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("BloodRequest::getRequestsByBloodType() Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get urgent requests
    public function getUrgentRequests() {
        try {
            $this->db->query("SELECT * FROM blood_requests 
                             WHERE status = 'pending' AND urgency IN ('high', 'critical')
                             ORDER BY 
                                CASE urgency 
                                    WHEN 'critical' THEN 1 
                                    WHEN 'high' THEN 2 
                                    ELSE 3 
                                END, 
                                request_date ASC");
            return $this->db->resultSet();
        } catch (Exception $e) {
            error_log("BloodRequest::getUrgentRequests() Error: " . $e->getMessage());
            return [];
        }
    }
    
    // Delete request (admin only)
    public function deleteRequest($id) {
        $this->db->beginTransaction();
        
        try {
            // Check if request exists
            $this->db->query("SELECT status FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            $request = $this->db->single();
            
            if (!$request) {
                throw new Exception("Request not found");
            }
            
            // Handle both array and object return types
            $status = is_array($request) ? $request['status'] : $request->status;
            
            if ($status === 'fulfilled') {
                throw new Exception("Cannot delete fulfilled requests");
            }
            
            // Delete related transactions first
            $this->db->query("DELETE FROM blood_transactions WHERE request_id = :id");
            $this->db->bind(':id', (int)$id);
            $this->db->execute();
            
            // Delete the request
            $this->db->query("DELETE FROM blood_requests WHERE id = :id");
            $this->db->bind(':id', (int)$id);
            
            if ($this->db->execute()) {
                $this->db->commit();
                return ['success' => true, 'message' => 'Request deleted successfully'];
            } else {
                throw new Exception("Failed to delete request");
            }
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("BloodRequest::deleteRequest() Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
?>
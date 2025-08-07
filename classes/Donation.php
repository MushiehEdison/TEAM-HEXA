<?php
// classes/Donation.php
require_once 'Database.php';

class Donation {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Get all donations
     */
    public function getAllDonations() {
        $query = "SELECT d.*, donor.full_name as donor_name 
                  FROM donations d
                  LEFT JOIN donors donor ON d.donor_id = donor.id
                  ORDER BY d.donation_date DESC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Get today's donations
     */
    public function getTodaysDonations() {
        $query = "SELECT d.*, donor.full_name as donor_name 
                  FROM donations d
                  LEFT JOIN donors donor ON d.donor_id = donor.id
                  WHERE DATE(d.donation_date) = CURDATE()
                  ORDER BY d.donation_date DESC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Get pending donations
     */
    public function getPendingDonations() {
        $query = "SELECT d.*, donor.full_name as donor_name 
                  FROM donations d
                  LEFT JOIN donors donor ON d.donor_id = donor.id
                  WHERE d.status = 'pending'
                  ORDER BY d.donation_date DESC";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Get donation statistics
     */
    public function getDonationStats() {
        $stats = [];

        // Total donations
        $query = "SELECT COUNT(*) as total FROM donations";
        $this->db->query($query);
        $stats['total_donations'] = $this->db->single()->total;

        // Today's donations
        $query = "SELECT COUNT(*) as total FROM donations WHERE DATE(donation_date) = CURDATE()";
        $this->db->query($query);
        $stats['today_donations'] = $this->db->single()->total;

        // Pending donations
        $query = "SELECT COUNT(*) as total FROM donations WHERE status = 'pending'";
        $this->db->query($query);
        $stats['pending_donations'] = $this->db->single()->total;

        // Eligible donors (those who can donate based on last donation date)
        $query = "SELECT COUNT(*) as total FROM donors 
                  WHERE (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 56)";
        $this->db->query($query);
        $stats['eligible_donors'] = $this->db->single()->total;

        return $stats;
    }

    /**
     * Get all eligible donors
     */
    public function getEligibleDonors() {
        $query = "SELECT id, full_name, blood_type 
                  FROM donors 
                  WHERE (last_donation_date IS NULL OR DATEDIFF(CURDATE(), last_donation_date) >= 56)
                  ORDER BY full_name";
        $this->db->query($query);
        return $this->db->resultSet();
    }

    /**
     * Add a new donation
     */
    public function addDonation($data) {
        // Validate required fields
        $required = ['donor_id', 'blood_type', 'quantity', 'donation_date', 
                    'hemoglobin_level', 'blood_pressure', 'temperature'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("$field is required");
            }
        }

        // Start transaction
        $this->db->beginTransaction();

        try {
            // Insert donation record
            $query = "INSERT INTO donations 
                      (donor_id, blood_type, quantity, donation_date, hemoglobin_level, 
                       blood_pressure, temperature, status, notes, created_at)
                      VALUES 
                      (:donor_id, :blood_type, :quantity, :donation_date, :hemoglobin_level, 
                       :blood_pressure, :temperature, 'pending', :notes, NOW())";
            
            $this->db->query($query);
            $this->db->bind(':donor_id', $data['donor_id']);
            $this->db->bind(':blood_type', $data['blood_type']);
            $this->db->bind(':quantity', $data['quantity']);
            $this->db->bind(':donation_date', $data['donation_date']);
            $this->db->bind(':hemoglobin_level', $data['hemoglobin_level']);
            $this->db->bind(':blood_pressure', $data['blood_pressure']);
            $this->db->bind(':temperature', $data['temperature']);
            $this->db->bind(':notes', $data['notes'] ?? null);
            
            $this->db->execute();
            $donation_id = $this->db->lastInsertId();

            // Update donor's last donation date
            $query = "UPDATE donors SET last_donation_date = :donation_date WHERE id = :donor_id";
            $this->db->query($query);
            $this->db->bind(':donation_date', $data['donation_date']);
            $this->db->bind(':donor_id', $data['donor_id']);
            $this->db->execute();

            // Commit transaction
            $this->db->commit();

            return $donation_id;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get donation details by ID
     */
    public function getDonationById($id) {
        $query = "SELECT d.*, donor.full_name as donor_name, donor.gender, donor.date_of_birth
                  FROM donations d
                  LEFT JOIN donors donor ON d.donor_id = donor.id
                  WHERE d.id = :id";
        $this->db->query($query);
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    /**
     * Approve a donation and add to inventory
     */
    public function approveDonation($id) {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Get donation details
            $donation = $this->getDonationById($id);
            if (!$donation) {
                throw new Exception("Donation not found");
            }

            if ($donation->status !== 'pending') {
                throw new Exception("Only pending donations can be approved");
            }

            // Calculate expiry date (42 days from donation date)
            $expiry_date = date('Y-m-d', strtotime($donation->donation_date . ' +42 days'));

            // Add to blood inventory
            $query = "INSERT INTO blood_inventory 
                      (donation_id, blood_type, quantity, collection_date, expiry_date, status, created_at)
                      VALUES 
                      (:donation_id, :blood_type, :quantity, :collection_date, :expiry_date, 'available', NOW())";
            
            $this->db->query($query);
            $this->db->bind(':donation_id', $id);
            $this->db->bind(':blood_type', $donation->blood_type);
            $this->db->bind(':quantity', $donation->quantity);
            $this->db->bind(':collection_date', $donation->donation_date);
            $this->db->bind(':expiry_date', $expiry_date);
            $this->db->execute();

            // Update donation status
            $query = "UPDATE donations SET status = 'approved', updated_at = NOW() WHERE id = :id";
            $this->db->query($query);
            $this->db->bind(':id', $id);
            $this->db->execute();

            // Commit transaction
            $this->db->commit();

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Reject a donation
     */
    public function rejectDonation($id, $reason) {
        $query = "UPDATE donations 
                  SET status = 'rejected', notes = CONCAT(IFNULL(notes, ''), :reason), 
                      updated_at = NOW()
                  WHERE id = :id";
        $this->db->query($query);
        $this->db->bind(':id', $id);
        $this->db->bind(':reason', "\n\nRejection Reason: " . $reason);
        return $this->db->execute();
    }

    /**
     * Get donor's donation history
     */
    public function getDonorHistory($donor_id) {
        $query = "SELECT * FROM donations 
                  WHERE donor_id = :donor_id
                  ORDER BY donation_date DESC";
        $this->db->query($query);
        $this->db->bind(':donor_id', $donor_id);
        return $this->db->resultSet();
    }

    /**
     * Check if donor is eligible to donate
     */
    public function isDonorEligible($donor_id) {
        $query = "SELECT last_donation_date FROM donors WHERE id = :donor_id";
        $this->db->query($query);
        $this->db->bind(':donor_id', $donor_id);
        $result = $this->db->single();

        if (!$result) {
            return false; // Donor not found
        }

        if ($result->last_donation_date === null) {
            return true; // Never donated before
        }

        $last_donation = strtotime($result->last_donation_date);
        $waiting_period = strtotime('-56 days');
        return $last_donation <= $waiting_period;
    }
}
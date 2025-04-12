<?php
/**
 * Interaction Model
 * 
 * This class handles all interaction-related database operations.
 */
class Interaction {
    private $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all interactions for a contact
     * 
     * @param int $contactId Contact ID
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC, DESC)
     * @return array Array of interaction objects
     */
    public function getContactInteractions($contactId, $limit = 10, $offset = 0, $sortBy = 'date', $sortDir = 'DESC') {
        $this->db->query("SELECT i.*, u.name as created_by_name 
                         FROM interactions i 
                         JOIN users u ON i.created_by = u.id 
                         WHERE i.contact_id = :contact_id 
                         ORDER BY i.$sortBy $sortDir 
                         LIMIT :limit OFFSET :offset");
        
        $this->db->bind(':contact_id', $contactId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total interactions for a contact
     * 
     * @param int $contactId Contact ID
     * @return int Total number of interactions
     */
    public function countContactInteractions($contactId) {
        $this->db->query("SELECT COUNT(*) as total FROM interactions WHERE contact_id = :contact_id");
        $this->db->bind(':contact_id', $contactId);
        
        $result = $this->db->single();
        
        return $result->total;
    }
    
    /**
     * Get interaction by ID
     * 
     * @param int $id Interaction ID
     * @return object|boolean Interaction object or false if not found
     */
    public function getInteractionById($id) {
        $this->db->query("SELECT i.*, u.name as created_by_name 
                         FROM interactions i 
                         JOIN users u ON i.created_by = u.id 
                         WHERE i.id = :id");
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Add new interaction
     * 
     * @param array $data Interaction data
     * @return int|boolean New interaction ID or false if failed
     */
    public function addInteraction($data) {
        $this->db->query("INSERT INTO interactions (
            contact_id, type, subject, description, date, 
            duration, status, outcome, created_by
        ) VALUES (
            :contact_id, :type, :subject, :description, :date, 
            :duration, :status, :outcome, :created_by
        )");
        
        // Bind values
        $this->db->bind(':contact_id', $data['contact_id']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':subject', $data['subject']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':date', $data['date']);
        $this->db->bind(':duration', $data['duration'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'planned');
        $this->db->bind(':outcome', $data['outcome'] ?? null);
        $this->db->bind(':created_by', $data['created_by']);
        
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Update interaction
     * 
     * @param array $data Interaction data
     * @return boolean True if updated, false otherwise
     */
    public function updateInteraction($data) {
        $this->db->query("UPDATE interactions SET 
                          type = :type, 
                          subject = :subject, 
                          description = :description, 
                          date = :date, 
                          duration = :duration, 
                          status = :status, 
                          outcome = :outcome 
                          WHERE id = :id");
        
        // Bind values
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':subject', $data['subject']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':date', $data['date']);
        $this->db->bind(':duration', $data['duration'] ?? null);
        $this->db->bind(':status', $data['status'] ?? 'planned');
        $this->db->bind(':outcome', $data['outcome'] ?? null);
        $this->db->bind(':id', $data['id']);
        
        return $this->db->execute();
    }
    
    /**
     * Delete interaction
     * 
     * @param int $id Interaction ID
     * @return boolean True if deleted, false otherwise
     */
    public function deleteInteraction($id) {
        $this->db->query("DELETE FROM interactions WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Update interaction status
     * 
     * @param int $id Interaction ID
     * @param string $status New status
     * @param string $outcome Outcome (for completed interactions)
     * @return boolean True if updated, false otherwise
     */
    public function updateStatus($id, $status, $outcome = null) {
        $this->db->query("UPDATE interactions SET status = :status, outcome = :outcome WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':outcome', $outcome);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Get all interactions for calendar
     * 
     * @param int $userId User ID
     * @param string $start Start date
     * @param string $end End date
     * @return array Array of interaction objects
     */
    public function getCalendarInteractions($userId, $start, $end) {
        $this->db->query("SELECT i.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM interactions i 
                         JOIN contacts c ON i.contact_id = c.id 
                         JOIN users u ON c.owner_id = u.id 
                         WHERE (i.created_by = :user_id OR c.owner_id = :owner_id) 
                         AND i.date BETWEEN :start AND :end 
                         AND i.status != 'canceled'");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':owner_id', $userId);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get upcoming interactions for a user
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @return array Array of interaction objects
     */
    public function getUpcomingInteractions($userId, $limit = 5) {
        $this->db->query("SELECT i.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM interactions i 
                         JOIN contacts c ON i.contact_id = c.id 
                         WHERE (i.created_by = :user_id OR c.owner_id = :owner_id) 
                         AND i.date >= NOW() 
                         AND i.status = 'planned' 
                         ORDER BY i.date ASC 
                         LIMIT :limit");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':owner_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get recent completed interactions
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @return array Array of interaction objects
     */
    public function getRecentCompletedInteractions($userId, $limit = 5) {
        $this->db->query("SELECT i.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM interactions i 
                         JOIN contacts c ON i.contact_id = c.id 
                         WHERE (i.created_by = :user_id OR c.owner_id = :owner_id) 
                         AND i.status = 'completed' 
                         ORDER BY i.date DESC 
                         LIMIT :limit");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':owner_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get interaction count by type
     * 
     * @param int $userId User ID (optional)
     * @param string $period Period (day, week, month, year, all)
     * @return array Array of counts by type
     */
    public function getInteractionCountByType($userId = null, $period = 'all') {
        $sql = "SELECT type, COUNT(*) as count FROM interactions WHERE 1=1";
        
        // Apply user filter
        if($userId) {
            $sql .= " AND created_by = :user_id";
        }
        
        // Apply period filter
        switch($period) {
            case 'day':
                $sql .= " AND DATE(date) = CURDATE()";
                break;
            case 'week':
                $sql .= " AND YEARWEEK(date) = YEARWEEK(CURDATE())";
                break;
            case 'month':
                $sql .= " AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
                break;
            case 'year':
                $sql .= " AND YEAR(date) = YEAR(CURDATE())";
                break;
        }
        
        $sql .= " GROUP BY type ORDER BY count DESC";
        
        $this->db->query($sql);
        
        // Bind parameters
        if($userId) {
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get interaction count by status
     * 
     * @param int $userId User ID (optional)
     * @return array Array of counts by status
     */
    public function getInteractionCountByStatus($userId = null) {
        $sql = "SELECT status, COUNT(*) as count FROM interactions WHERE 1=1";
        
        // Apply user filter
        if($userId) {
            $sql .= " AND created_by = :user_id";
        }
        
        $sql .= " GROUP BY status ORDER BY count DESC";
        
        $this->db->query($sql);
        
        // Bind parameters
        if($userId) {
            $this->db->bind(':user_id', $userId);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Get interaction count by month
     * 
     * @param int $userId User ID (optional)
     * @param int $year Year (default: current year)
     * @return array Array of counts by month
     */
    public function getInteractionCountByMonth($userId = null, $year = null) {
        $year = $year ?? date('Y');
        
        $sql = "SELECT MONTH(date) as month, COUNT(*) as count 
                FROM interactions 
                WHERE YEAR(date) = :year";
        
        // Apply user filter
        if($userId) {
            $sql .= " AND created_by = :user_id";
        }
        
        $sql .= " GROUP BY MONTH(date) ORDER BY month";
        
        $this->db->query($sql);
        
        // Bind parameters
        $this->db->bind(':year', $year);
        
        if($userId) {
            $this->db->bind(':user_id', $userId);
        }
        
        $results = $this->db->resultSet();
        
        // Format results to have all 12 months
        $months = [];
        for($i = 1; $i <= 12; $i++) {
            $months[$i] = 0;
        }
        
        foreach($results as $row) {
            $months[$row->month] = $row->count;
        }
        
        return $months;
    }
} 
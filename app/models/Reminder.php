<?php
/**
 * Reminder Model
 * 
 * This class handles all reminder-related database operations.
 */
class Reminder {
    private $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all reminders for a user
     * 
     * @param int $userId User ID
     * @param string $status Reminder status (pending, completed, dismissed, all)
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of reminder objects
     */
    public function getReminders($userId, $status = 'all', $limit = 10, $offset = 0) {
        $sql = "SELECT r.*, 
                CASE 
                    WHEN r.related_type = 'contact' THEN CONCAT(c.first_name, ' ', c.last_name)
                    WHEN r.related_type = 'deal' THEN d.title
                    WHEN r.related_type = 'interaction' THEN i.subject
                    WHEN r.related_type = 'event' THEN e.title
                    ELSE NULL
                END as related_name
                FROM reminders r 
                LEFT JOIN contacts c ON r.related_type = 'contact' AND r.related_id = c.id
                LEFT JOIN deals d ON r.related_type = 'deal' AND r.related_id = d.id
                LEFT JOIN interactions i ON r.related_type = 'interaction' AND r.related_id = i.id
                LEFT JOIN events e ON r.related_type = 'event' AND r.related_id = e.id
                WHERE r.user_id = :user_id";
        
        // Apply status filter
        if($status !== 'all') {
            $sql .= " AND r.status = :status";
        }
        
        $sql .= " ORDER BY r.due_date ASC, r.priority DESC 
                  LIMIT :limit OFFSET :offset";
        
        $this->db->query($sql);
        
        // Bind parameters
        $this->db->bind(':user_id', $userId);
        if($status !== 'all') {
            $this->db->bind(':status', $status);
        }
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total reminders for a user
     * 
     * @param int $userId User ID
     * @param string $status Reminder status (pending, completed, dismissed, all)
     * @return int Total number of reminders
     */
    public function countReminders($userId, $status = 'all') {
        $sql = "SELECT COUNT(*) as total FROM reminders WHERE user_id = :user_id";
        
        // Apply status filter
        if($status !== 'all') {
            $sql .= " AND status = :status";
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        $this->db->bind(':user_id', $userId);
        if($status !== 'all') {
            $this->db->bind(':status', $status);
        }
        
        $result = $this->db->single();
        
        return $result->total;
    }
    
    /**
     * Get reminder by ID
     * 
     * @param int $id Reminder ID
     * @return object|boolean Reminder object or false if not found
     */
    public function getReminderById($id) {
        $this->db->query("SELECT r.*,
                         CASE 
                             WHEN r.related_type = 'contact' THEN CONCAT(c.first_name, ' ', c.last_name)
                             WHEN r.related_type = 'deal' THEN d.title
                             WHEN r.related_type = 'interaction' THEN i.subject
                             WHEN r.related_type = 'event' THEN e.title
                             ELSE NULL
                         END as related_name
                         FROM reminders r 
                         LEFT JOIN contacts c ON r.related_type = 'contact' AND r.related_id = c.id
                         LEFT JOIN deals d ON r.related_type = 'deal' AND r.related_id = d.id
                         LEFT JOIN interactions i ON r.related_type = 'interaction' AND r.related_id = i.id
                         LEFT JOIN events e ON r.related_type = 'event' AND r.related_id = e.id
                         WHERE r.id = :id");
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Add new reminder
     * 
     * @param array $data Reminder data
     * @return int|boolean New reminder ID or false if failed
     */
    public function addReminder($data) {
        $this->db->query("INSERT INTO reminders (
            title, description, due_date, priority, status, 
            user_id, related_type, related_id
        ) VALUES (
            :title, :description, :due_date, :priority, :status, 
            :user_id, :related_type, :related_id
        )");
        
        // Bind values
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':due_date', $data['due_date']);
        $this->db->bind(':priority', $data['priority'] ?? 'medium');
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':related_type', $data['related_type'] ?? null);
        $this->db->bind(':related_id', $data['related_id'] ?? null);
        
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Update reminder
     * 
     * @param array $data Reminder data
     * @return boolean True if updated, false otherwise
     */
    public function updateReminder($data) {
        $this->db->query("UPDATE reminders SET 
                          title = :title, 
                          description = :description, 
                          due_date = :due_date, 
                          priority = :priority, 
                          status = :status, 
                          related_type = :related_type, 
                          related_id = :related_id 
                          WHERE id = :id AND user_id = :user_id");
        
        // Bind values
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':due_date', $data['due_date']);
        $this->db->bind(':priority', $data['priority'] ?? 'medium');
        $this->db->bind(':status', $data['status'] ?? 'pending');
        $this->db->bind(':related_type', $data['related_type'] ?? null);
        $this->db->bind(':related_id', $data['related_id'] ?? null);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':user_id', $data['user_id']);
        
        return $this->db->execute();
    }
    
    /**
     * Delete reminder
     * 
     * @param int $id Reminder ID
     * @param int $userId User ID (for security)
     * @return boolean True if deleted, false otherwise
     */
    public function deleteReminder($id, $userId) {
        $this->db->query("DELETE FROM reminders WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Update reminder status
     * 
     * @param int $id Reminder ID
     * @param string $status New status
     * @param int $userId User ID (for security)
     * @return boolean True if updated, false otherwise
     */
    public function updateStatus($id, $status, $userId) {
        $this->db->query("UPDATE reminders SET status = :status WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Get due reminders for notifications
     * 
     * @return array Array of reminder objects
     */
    public function getDueReminders() {
        $this->db->query("SELECT r.*, u.email as user_email, u.name as user_name 
                         FROM reminders r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.status = 'pending' 
                         AND r.due_date <= NOW()");
        
        return $this->db->resultSet();
    }
    
    /**
     * Get upcoming reminders for a user
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @return array Array of reminder objects
     */
    public function getUpcomingReminders($userId, $limit = 5) {
        $this->db->query("SELECT r.* 
                         FROM reminders r 
                         WHERE r.user_id = :user_id 
                         AND r.status = 'pending' 
                         AND r.due_date > NOW() 
                         ORDER BY r.due_date ASC 
                         LIMIT :limit");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get overdue reminders for a user
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @return array Array of reminder objects
     */
    public function getOverdueReminders($userId, $limit = 5) {
        $this->db->query("SELECT r.* 
                         FROM reminders r 
                         WHERE r.user_id = :user_id 
                         AND r.status = 'pending' 
                         AND r.due_date < NOW() 
                         ORDER BY r.due_date ASC 
                         LIMIT :limit");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get reminders for a specific related item
     * 
     * @param string $relatedType Related item type
     * @param int $relatedId Related item ID
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of reminder objects
     */
    public function getRelatedReminders($relatedType, $relatedId, $limit = 10, $offset = 0) {
        $this->db->query("SELECT r.*, u.name as user_name 
                         FROM reminders r 
                         JOIN users u ON r.user_id = u.id 
                         WHERE r.related_type = :related_type 
                         AND r.related_id = :related_id 
                         ORDER BY r.due_date DESC 
                         LIMIT :limit OFFSET :offset");
        
        $this->db->bind(':related_type', $relatedType);
        $this->db->bind(':related_id', $relatedId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Count reminders by status for a user
     * 
     * @param int $userId User ID
     * @return array Array of counts by status
     */
    public function countRemindersByStatus($userId) {
        $this->db->query("SELECT status, COUNT(*) as count 
                         FROM reminders 
                         WHERE user_id = :user_id 
                         GROUP BY status");
        
        $this->db->bind(':user_id', $userId);
        
        $results = $this->db->resultSet();
        
        // Format results
        $counts = [
            'pending' => 0,
            'completed' => 0,
            'dismissed' => 0
        ];
        
        foreach($results as $row) {
            $counts[$row->status] = $row->count;
        }
        
        return $counts;
    }
} 
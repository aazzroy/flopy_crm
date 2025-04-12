<?php
/**
 * Event Model
 * 
 * This class handles all calendar event-related database operations.
 */
class Event {
    private $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all events for a user within a date range
     * 
     * @param int $userId User ID
     * @param string $start Start date
     * @param string $end End date
     * @return array Array of event objects
     */
    public function getEvents($userId, $start, $end) {
        $this->db->query("SELECT e.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM events e 
                         LEFT JOIN contacts c ON e.contact_id = c.id 
                         WHERE e.user_id = :user_id 
                         AND (
                             (e.start BETWEEN :start AND :end) 
                             OR (e.end BETWEEN :start AND :end) 
                             OR (e.start <= :start AND e.end >= :end)
                         )");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get event by ID
     * 
     * @param int $id Event ID
     * @return object|boolean Event object or false if not found
     */
    public function getEventById($id) {
        $this->db->query("SELECT e.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM events e 
                         LEFT JOIN contacts c ON e.contact_id = c.id 
                         WHERE e.id = :id");
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Add new event
     * 
     * @param array $data Event data
     * @return int|boolean New event ID or false if failed
     */
    public function addEvent($data) {
        $this->db->query("INSERT INTO events (
            title, description, start, end, all_day, 
            location, color, user_id, contact_id, reminder
        ) VALUES (
            :title, :description, :start, :end, :all_day, 
            :location, :color, :user_id, :contact_id, :reminder
        )");
        
        // Bind values
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':start', $data['start']);
        $this->db->bind(':end', $data['end']);
        $this->db->bind(':all_day', $data['all_day'] ?? 0);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':color', $data['color'] ?? '#4F46E5');
        $this->db->bind(':user_id', $data['user_id']);
        $this->db->bind(':contact_id', $data['contact_id'] ?? null);
        $this->db->bind(':reminder', $data['reminder'] ?? null);
        
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Update event
     * 
     * @param array $data Event data
     * @return boolean True if updated, false otherwise
     */
    public function updateEvent($data) {
        $this->db->query("UPDATE events SET 
                          title = :title, 
                          description = :description, 
                          start = :start, 
                          end = :end, 
                          all_day = :all_day, 
                          location = :location, 
                          color = :color, 
                          contact_id = :contact_id, 
                          reminder = :reminder 
                          WHERE id = :id AND user_id = :user_id");
        
        // Bind values
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':start', $data['start']);
        $this->db->bind(':end', $data['end']);
        $this->db->bind(':all_day', $data['all_day'] ?? 0);
        $this->db->bind(':location', $data['location'] ?? null);
        $this->db->bind(':color', $data['color'] ?? '#4F46E5');
        $this->db->bind(':contact_id', $data['contact_id'] ?? null);
        $this->db->bind(':reminder', $data['reminder'] ?? null);
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':user_id', $data['user_id']);
        
        return $this->db->execute();
    }
    
    /**
     * Delete event
     * 
     * @param int $id Event ID
     * @param int $userId User ID (for security)
     * @return boolean True if deleted, false otherwise
     */
    public function deleteEvent($id, $userId) {
        $this->db->query("DELETE FROM events WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Update event dates (for drag and drop in calendar)
     * 
     * @param int $id Event ID
     * @param string $start New start date
     * @param string $end New end date
     * @param int $userId User ID (for security)
     * @return boolean True if updated, false otherwise
     */
    public function updateEventDates($id, $start, $end, $userId) {
        $this->db->query("UPDATE events SET start = :start, end = :end WHERE id = :id AND user_id = :user_id");
        $this->db->bind(':start', $start);
        $this->db->bind(':end', $end);
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Get upcoming events for a user
     * 
     * @param int $userId User ID
     * @param int $limit Limit results
     * @return array Array of event objects
     */
    public function getUpcomingEvents($userId, $limit = 5) {
        $this->db->query("SELECT e.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM events e 
                         LEFT JOIN contacts c ON e.contact_id = c.id 
                         WHERE e.user_id = :user_id 
                         AND e.start >= NOW() 
                         ORDER BY e.start ASC 
                         LIMIT :limit");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get events for a contact
     * 
     * @param int $contactId Contact ID
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of event objects
     */
    public function getContactEvents($contactId, $limit = 10, $offset = 0) {
        $this->db->query("SELECT e.*, u.name as user_name 
                         FROM events e 
                         JOIN users u ON e.user_id = u.id 
                         WHERE e.contact_id = :contact_id 
                         ORDER BY e.start DESC 
                         LIMIT :limit OFFSET :offset");
        
        $this->db->bind(':contact_id', $contactId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total events for a contact
     * 
     * @param int $contactId Contact ID
     * @return int Total number of events
     */
    public function countContactEvents($contactId) {
        $this->db->query("SELECT COUNT(*) as total FROM events WHERE contact_id = :contact_id");
        $this->db->bind(':contact_id', $contactId);
        
        $result = $this->db->single();
        
        return $result->total;
    }
    
    /**
     * Get events needing reminders
     * 
     * @return array Array of event objects needing reminders
     */
    public function getEventsNeedingReminders() {
        $this->db->query("SELECT e.*, u.email as user_email, u.name as user_name, 
                         CONCAT(c.first_name, ' ', c.last_name) as contact_name 
                         FROM events e 
                         JOIN users u ON e.user_id = u.id 
                         LEFT JOIN contacts c ON e.contact_id = c.id 
                         WHERE e.reminder IS NOT NULL 
                         AND e.start > NOW() 
                         AND DATE_SUB(e.start, INTERVAL e.reminder MINUTE) <= NOW() 
                         AND NOT EXISTS (
                             SELECT 1 FROM reminders r 
                             WHERE r.related_type = 'event' 
                             AND r.related_id = e.id 
                             AND r.status != 'dismissed'
                         )");
        
        return $this->db->resultSet();
    }
    
    /**
     * Create a reminder for an event
     * 
     * @param object $event Event object
     * @return int|boolean New reminder ID or false if failed
     */
    public function createEventReminder($event) {
        // Create reminder model
        $reminderModel = new Reminder();
        
        // Create reminder data
        $reminderData = [
            'title' => 'Event Reminder: ' . $event->title,
            'description' => 'You have an upcoming event: ' . $event->title . 
                            ($event->location ? ' at ' . $event->location : ''),
            'due_date' => $event->start,
            'priority' => 'medium',
            'user_id' => $event->user_id,
            'related_type' => 'event',
            'related_id' => $event->id
        ];
        
        // Add the reminder
        return $reminderModel->addReminder($reminderData);
    }
    
    /**
     * Get event count by day of week
     * 
     * @param int $userId User ID
     * @param int $weeks Number of weeks to analyze
     * @return array Array of counts by day of week
     */
    public function getEventCountByDayOfWeek($userId, $weeks = 4) {
        $this->db->query("SELECT DAYOFWEEK(start) as day_of_week, COUNT(*) as count 
                         FROM events 
                         WHERE user_id = :user_id 
                         AND start BETWEEN DATE_SUB(NOW(), INTERVAL :weeks WEEK) AND NOW() 
                         GROUP BY DAYOFWEEK(start) 
                         ORDER BY day_of_week");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':weeks', $weeks, PDO::PARAM_INT);
        
        $results = $this->db->resultSet();
        
        // Format results (1 = Sunday, 7 = Saturday)
        $days = [];
        for($i = 1; $i <= 7; $i++) {
            $days[$i] = 0;
        }
        
        foreach($results as $row) {
            $days[$row->day_of_week] = $row->count;
        }
        
        return $days;
    }
} 
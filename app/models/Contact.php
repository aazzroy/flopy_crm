<?php
/**
 * Contact Model
 * 
 * This class handles all contact-related database operations.
 */
class Contact {
    private $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all contacts with optional filtering and pagination
     * 
     * @param array $filters Filter options (search, tags, owner, status)
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC, DESC)
     * @return array Array of contact objects
     */
    public function getContacts($filters = [], $limit = 10, $offset = 0, $sortBy = 'created_at', $sortDir = 'DESC') {
        $sql = "SELECT c.*, CONCAT(c.first_name, ' ', c.last_name) as full_name, 
                u.name as owner_name 
                FROM contacts c 
                LEFT JOIN users u ON c.owner_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['search'])) {
            $sql .= " AND (
                c.first_name LIKE :search 
                OR c.last_name LIKE :search 
                OR c.email LIKE :search 
                OR c.phone LIKE :search 
                OR c.mobile LIKE :search 
                OR c.company LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if(!empty($filters['owner_id'])) {
            $sql .= " AND c.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['lead_status'])) {
            $sql .= " AND c.lead_status = :lead_status";
            $params[':lead_status'] = $filters['lead_status'];
        }
        
        if(!empty($filters['lead_source'])) {
            $sql .= " AND c.lead_source = :lead_source";
            $params[':lead_source'] = $filters['lead_source'];
        }
        
        if(!empty($filters['tags'])) {
            $sql .= " AND c.id IN (SELECT contact_id FROM contact_tags WHERE tag_id IN (" . implode(',', array_fill(0, count($filters['tags']), '?')) . "))";
            foreach($filters['tags'] as $index => $tagId) {
                $params[':tag'.$index] = $tagId;
            }
        }
        
        // Apply sorting
        $allowedSortFields = ['first_name', 'last_name', 'email', 'company', 'created_at', 'lead_status', 'lead_score'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY c.$sortBy $sortDir";
        
        // Apply pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $contacts = $this->db->resultSet();
        
        // Get tags for each contact
        foreach($contacts as &$contact) {
            $contact->tags = $this->getContactTags($contact->id);
        }
        
        return $contacts;
    }
    
    /**
     * Count total contacts with optional filtering
     * 
     * @param array $filters Filter options
     * @return int Total number of contacts
     */
    public function countContacts($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM contacts c WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['search'])) {
            $sql .= " AND (
                c.first_name LIKE :search 
                OR c.last_name LIKE :search 
                OR c.email LIKE :search 
                OR c.phone LIKE :search 
                OR c.mobile LIKE :search 
                OR c.company LIKE :search
            )";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if(!empty($filters['owner_id'])) {
            $sql .= " AND c.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['lead_status'])) {
            $sql .= " AND c.lead_status = :lead_status";
            $params[':lead_status'] = $filters['lead_status'];
        }
        
        if(!empty($filters['lead_source'])) {
            $sql .= " AND c.lead_source = :lead_source";
            $params[':lead_source'] = $filters['lead_source'];
        }
        
        if(!empty($filters['tags'])) {
            $sql .= " AND c.id IN (SELECT contact_id FROM contact_tags WHERE tag_id IN (" . implode(',', array_fill(0, count($filters['tags']), '?')) . "))";
            foreach($filters['tags'] as $index => $tagId) {
                $params[':tag'.$index] = $tagId;
            }
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $result = $this->db->single();
        
        return $result->total;
    }
    
    /**
     * Get contact by ID
     * 
     * @param int $id Contact ID
     * @return object|boolean Contact object or false if not found
     */
    public function getContactById($id) {
        $this->db->query("SELECT c.*, CONCAT(c.first_name, ' ', c.last_name) as full_name, 
                          u.name as owner_name 
                          FROM contacts c 
                          LEFT JOIN users u ON c.owner_id = u.id 
                          WHERE c.id = :id");
        $this->db->bind(':id', $id);
        
        $contact = $this->db->single();
        
        if($this->db->rowCount() > 0) {
            // Get tags for this contact
            $contact->tags = $this->getContactTags($id);
            return $contact;
        } else {
            return false;
        }
    }
    
    /**
     * Add new contact
     * 
     * @param array $data Contact data
     * @return int|boolean New contact ID or false if failed
     */
    public function addContact($data) {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Insert contact
            $this->db->query("INSERT INTO contacts (
                first_name, last_name, email, phone, mobile, company, position, 
                website, address, city, state, zip, country, notes, 
                lead_source, lead_status, lead_score, owner_id, created_by
            ) VALUES (
                :first_name, :last_name, :email, :phone, :mobile, :company, :position, 
                :website, :address, :city, :state, :zip, :country, :notes, 
                :lead_source, :lead_status, :lead_score, :owner_id, :created_by
            )");
            
            // Bind values
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':email', $data['email'] ?? null);
            $this->db->bind(':phone', $data['phone'] ?? null);
            $this->db->bind(':mobile', $data['mobile'] ?? null);
            $this->db->bind(':company', $data['company'] ?? null);
            $this->db->bind(':position', $data['position'] ?? null);
            $this->db->bind(':website', $data['website'] ?? null);
            $this->db->bind(':address', $data['address'] ?? null);
            $this->db->bind(':city', $data['city'] ?? null);
            $this->db->bind(':state', $data['state'] ?? null);
            $this->db->bind(':zip', $data['zip'] ?? null);
            $this->db->bind(':country', $data['country'] ?? null);
            $this->db->bind(':notes', $data['notes'] ?? null);
            $this->db->bind(':lead_source', $data['lead_source'] ?? null);
            $this->db->bind(':lead_status', $data['lead_status'] ?? null);
            $this->db->bind(':lead_score', $data['lead_score'] ?? null);
            $this->db->bind(':owner_id', $data['owner_id'] ?? null);
            $this->db->bind(':created_by', $data['created_by']);
            
            $this->db->execute();
            
            $contactId = $this->db->lastInsertId();
            
            // Add tags if provided
            if(!empty($data['tags'])) {
                foreach($data['tags'] as $tagId) {
                    $this->addContactTag($contactId, $tagId);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $contactId;
        } catch(Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Update contact
     * 
     * @param array $data Contact data
     * @return boolean True if updated, false otherwise
     */
    public function updateContact($data) {
        // Begin transaction
        $this->db->beginTransaction();
        
        try {
            // Update contact
            $this->db->query("UPDATE contacts SET
                first_name = :first_name, 
                last_name = :last_name, 
                email = :email, 
                phone = :phone, 
                mobile = :mobile, 
                company = :company, 
                position = :position, 
                website = :website, 
                address = :address, 
                city = :city, 
                state = :state, 
                zip = :zip, 
                country = :country, 
                notes = :notes, 
                lead_source = :lead_source, 
                lead_status = :lead_status, 
                lead_score = :lead_score, 
                owner_id = :owner_id
                WHERE id = :id");
            
            // Bind values
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':email', $data['email'] ?? null);
            $this->db->bind(':phone', $data['phone'] ?? null);
            $this->db->bind(':mobile', $data['mobile'] ?? null);
            $this->db->bind(':company', $data['company'] ?? null);
            $this->db->bind(':position', $data['position'] ?? null);
            $this->db->bind(':website', $data['website'] ?? null);
            $this->db->bind(':address', $data['address'] ?? null);
            $this->db->bind(':city', $data['city'] ?? null);
            $this->db->bind(':state', $data['state'] ?? null);
            $this->db->bind(':zip', $data['zip'] ?? null);
            $this->db->bind(':country', $data['country'] ?? null);
            $this->db->bind(':notes', $data['notes'] ?? null);
            $this->db->bind(':lead_source', $data['lead_source'] ?? null);
            $this->db->bind(':lead_status', $data['lead_status'] ?? null);
            $this->db->bind(':lead_score', $data['lead_score'] ?? null);
            $this->db->bind(':owner_id', $data['owner_id'] ?? null);
            $this->db->bind(':id', $data['id']);
            
            $this->db->execute();
            
            // Update tags if provided
            if(isset($data['tags'])) {
                // Delete all existing tags
                $this->deleteContactTags($data['id']);
                
                // Add new tags
                foreach($data['tags'] as $tagId) {
                    $this->addContactTag($data['id'], $tagId);
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            return true;
        } catch(Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Delete contact
     * 
     * @param int $id Contact ID
     * @return boolean True if deleted, false otherwise
     */
    public function deleteContact($id) {
        $this->db->query("DELETE FROM contacts WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Update contact avatar
     * 
     * @param int $id Contact ID
     * @param string $avatar Avatar path
     * @return boolean True if updated, false otherwise
     */
    public function updateAvatar($id, $avatar) {
        $this->db->query("UPDATE contacts SET avatar = :avatar WHERE id = :id");
        $this->db->bind(':avatar', $avatar);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Get contact tags
     * 
     * @param int $contactId Contact ID
     * @return array Array of tag objects
     */
    public function getContactTags($contactId) {
        $this->db->query("SELECT t.* FROM tags t 
                         JOIN contact_tags ct ON t.id = ct.tag_id 
                         WHERE ct.contact_id = :contact_id 
                         ORDER BY t.name");
        $this->db->bind(':contact_id', $contactId);
        
        return $this->db->resultSet();
    }
    
    /**
     * Add tag to contact
     * 
     * @param int $contactId Contact ID
     * @param int $tagId Tag ID
     * @return boolean True if added, false otherwise
     */
    public function addContactTag($contactId, $tagId) {
        $this->db->query("INSERT IGNORE INTO contact_tags (contact_id, tag_id) VALUES (:contact_id, :tag_id)");
        $this->db->bind(':contact_id', $contactId);
        $this->db->bind(':tag_id', $tagId);
        
        return $this->db->execute();
    }
    
    /**
     * Delete all tags from a contact
     * 
     * @param int $contactId Contact ID
     * @return boolean True if deleted, false otherwise
     */
    public function deleteContactTags($contactId) {
        $this->db->query("DELETE FROM contact_tags WHERE contact_id = :contact_id");
        $this->db->bind(':contact_id', $contactId);
        
        return $this->db->execute();
    }
    
    /**
     * Get all available tags
     * 
     * @return array Array of tag objects
     */
    public function getAllTags() {
        $this->db->query("SELECT * FROM tags ORDER BY name");
        
        return $this->db->resultSet();
    }
    
    /**
     * Add new tag
     * 
     * @param string $name Tag name
     * @param string $color Tag color
     * @param int $createdBy User ID who created the tag
     * @return int|boolean New tag ID or false if failed
     */
    public function addTag($name, $color, $createdBy) {
        $this->db->query("INSERT INTO tags (name, color, created_by) VALUES (:name, :color, :created_by)");
        $this->db->bind(':name', $name);
        $this->db->bind(':color', $color);
        $this->db->bind(':created_by', $createdBy);
        
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Get all lead sources
     * 
     * @return array Array of unique lead sources
     */
    public function getLeadSources() {
        $this->db->query("SELECT DISTINCT lead_source FROM contacts WHERE lead_source IS NOT NULL ORDER BY lead_source");
        
        $results = $this->db->resultSet();
        $sources = [];
        
        foreach($results as $row) {
            $sources[] = $row->lead_source;
        }
        
        return $sources;
    }
    
    /**
     * Get all lead statuses
     * 
     * @return array Array of unique lead statuses
     */
    public function getLeadStatuses() {
        $this->db->query("SELECT DISTINCT lead_status FROM contacts WHERE lead_status IS NOT NULL ORDER BY lead_status");
        
        $results = $this->db->resultSet();
        $statuses = [];
        
        foreach($results as $row) {
            $statuses[] = $row->lead_status;
        }
        
        return $statuses;
    }
    
    /**
     * Import contacts from CSV
     * 
     * @param array $contacts Array of contact data
     * @param int $createdBy User ID who imported the contacts
     * @return array Array with counts of imported and failed contacts
     */
    public function importContacts($contacts, $createdBy) {
        $imported = 0;
        $failed = 0;
        
        foreach($contacts as $contact) {
            $contact['created_by'] = $createdBy;
            
            if($this->addContact($contact)) {
                $imported++;
            } else {
                $failed++;
            }
        }
        
        return [
            'imported' => $imported,
            'failed' => $failed
        ];
    }
    
    /**
     * Get contacts recently added
     * 
     * @param int $limit Number of contacts to return
     * @return array Array of contact objects
     */
    public function getRecentContacts($limit = 5) {
        $this->db->query("SELECT c.*, CONCAT(c.first_name, ' ', c.last_name) as full_name 
                         FROM contacts c 
                         ORDER BY c.created_at DESC 
                         LIMIT :limit");
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }
    
    /**
     * Get contact count by lead status
     * 
     * @return array Array of counts by status
     */
    public function getContactCountByStatus() {
        $this->db->query("SELECT lead_status, COUNT(*) as count 
                         FROM contacts 
                         WHERE lead_status IS NOT NULL 
                         GROUP BY lead_status 
                         ORDER BY count DESC");
        
        return $this->db->resultSet();
    }
    
    /**
     * Get contact count by lead source
     * 
     * @return array Array of counts by source
     */
    public function getContactCountBySource() {
        $this->db->query("SELECT lead_source, COUNT(*) as count 
                         FROM contacts 
                         WHERE lead_source IS NOT NULL 
                         GROUP BY lead_source 
                         ORDER BY count DESC");
        
        return $this->db->resultSet();
    }
    
    /**
     * Get assigned contacts count by owner
     * 
     * @return array Array of counts by owner
     */
    public function getContactCountByOwner() {
        $this->db->query("SELECT u.name as owner_name, COUNT(c.id) as count 
                         FROM users u 
                         LEFT JOIN contacts c ON u.id = c.owner_id 
                         WHERE u.role_id = :role_id
                         GROUP BY u.id 
                         ORDER BY count DESC");
        $this->db->bind(':role_id', ROLE_AGENT);
        
        return $this->db->resultSet();
    }
} 
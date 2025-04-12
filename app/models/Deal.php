<?php
/**
 * Deal Model
 * 
 * This class handles all deal-related database operations.
 */
class Deal {
    private $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Get all deals with optional filtering and pagination
     * 
     * @param array $filters Filter options (search, stage, owner, etc.)
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @param string $sortBy Field to sort by
     * @param string $sortDir Sort direction (ASC, DESC)
     * @return array Array of deal objects
     */
    public function getDeals($filters = [], $limit = 10, $offset = 0, $sortBy = 'created_at', $sortDir = 'DESC') {
        $sql = "SELECT d.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name, 
                u.name as owner_name 
                FROM deals d 
                JOIN contacts c ON d.contact_id = c.id 
                LEFT JOIN users u ON d.owner_id = u.id 
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['search'])) {
            $sql .= " AND (d.title LIKE :search OR c.first_name LIKE :search OR c.last_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if(!empty($filters['stage'])) {
            $sql .= " AND d.stage = :stage";
            $params[':stage'] = $filters['stage'];
        }
        
        if(!empty($filters['owner_id'])) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['contact_id'])) {
            $sql .= " AND d.contact_id = :contact_id";
            $params[':contact_id'] = $filters['contact_id'];
        }
        
        if(!empty($filters['min_amount'])) {
            $sql .= " AND d.amount >= :min_amount";
            $params[':min_amount'] = $filters['min_amount'];
        }
        
        if(!empty($filters['max_amount'])) {
            $sql .= " AND d.amount <= :max_amount";
            $params[':max_amount'] = $filters['max_amount'];
        }
        
        if(!empty($filters['expected_close_date_start'])) {
            $sql .= " AND d.expected_close_date >= :expected_close_date_start";
            $params[':expected_close_date_start'] = $filters['expected_close_date_start'];
        }
        
        if(!empty($filters['expected_close_date_end'])) {
            $sql .= " AND d.expected_close_date <= :expected_close_date_end";
            $params[':expected_close_date_end'] = $filters['expected_close_date_end'];
        }
        
        // Apply sorting
        $allowedSortFields = ['title', 'amount', 'stage', 'probability', 'expected_close_date', 'created_at'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY d.$sortBy $sortDir";
        
        // Apply pagination
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total deals with optional filtering
     * 
     * @param array $filters Filter options
     * @return int Total number of deals
     */
    public function countDeals($filters = []) {
        $sql = "SELECT COUNT(*) as total 
                FROM deals d 
                JOIN contacts c ON d.contact_id = c.id 
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['search'])) {
            $sql .= " AND (d.title LIKE :search OR c.first_name LIKE :search OR c.last_name LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        if(!empty($filters['stage'])) {
            $sql .= " AND d.stage = :stage";
            $params[':stage'] = $filters['stage'];
        }
        
        if(!empty($filters['owner_id'])) {
            $sql .= " AND d.owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['contact_id'])) {
            $sql .= " AND d.contact_id = :contact_id";
            $params[':contact_id'] = $filters['contact_id'];
        }
        
        if(!empty($filters['min_amount'])) {
            $sql .= " AND d.amount >= :min_amount";
            $params[':min_amount'] = $filters['min_amount'];
        }
        
        if(!empty($filters['max_amount'])) {
            $sql .= " AND d.amount <= :max_amount";
            $params[':max_amount'] = $filters['max_amount'];
        }
        
        if(!empty($filters['expected_close_date_start'])) {
            $sql .= " AND d.expected_close_date >= :expected_close_date_start";
            $params[':expected_close_date_start'] = $filters['expected_close_date_start'];
        }
        
        if(!empty($filters['expected_close_date_end'])) {
            $sql .= " AND d.expected_close_date <= :expected_close_date_end";
            $params[':expected_close_date_end'] = $filters['expected_close_date_end'];
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
     * Get deal by ID
     * 
     * @param int $id Deal ID
     * @return object|boolean Deal object or false if not found
     */
    public function getDealById($id) {
        $this->db->query("SELECT d.*, CONCAT(c.first_name, ' ', c.last_name) as contact_name, 
                          u.name as owner_name 
                          FROM deals d 
                          JOIN contacts c ON d.contact_id = c.id 
                          LEFT JOIN users u ON d.owner_id = u.id 
                          WHERE d.id = :id");
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Add new deal
     * 
     * @param array $data Deal data
     * @return int|boolean New deal ID or false if failed
     */
    public function addDeal($data) {
        $this->db->query("INSERT INTO deals (
            contact_id, title, description, amount, currency, 
            stage, probability, expected_close_date, owner_id, created_by
        ) VALUES (
            :contact_id, :title, :description, :amount, :currency, 
            :stage, :probability, :expected_close_date, :owner_id, :created_by
        )");
        
        // Bind values
        $this->db->bind(':contact_id', $data['contact_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':currency', $data['currency'] ?? 'USD');
        $this->db->bind(':stage', $data['stage']);
        $this->db->bind(':probability', $data['probability'] ?? $this->getStageProbability($data['stage']));
        $this->db->bind(':expected_close_date', $data['expected_close_date'] ?? null);
        $this->db->bind(':owner_id', $data['owner_id'] ?? null);
        $this->db->bind(':created_by', $data['created_by']);
        
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Update deal
     * 
     * @param array $data Deal data
     * @return boolean True if updated, false otherwise
     */
    public function updateDeal($data) {
        $this->db->query("UPDATE deals SET 
                          contact_id = :contact_id, 
                          title = :title, 
                          description = :description, 
                          amount = :amount, 
                          currency = :currency, 
                          stage = :stage, 
                          probability = :probability, 
                          expected_close_date = :expected_close_date, 
                          owner_id = :owner_id 
                          WHERE id = :id");
        
        // Bind values
        $this->db->bind(':contact_id', $data['contact_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':amount', $data['amount']);
        $this->db->bind(':currency', $data['currency'] ?? 'USD');
        $this->db->bind(':stage', $data['stage']);
        $this->db->bind(':probability', $data['probability'] ?? $this->getStageProbability($data['stage']));
        $this->db->bind(':expected_close_date', $data['expected_close_date'] ?? null);
        $this->db->bind(':owner_id', $data['owner_id'] ?? null);
        $this->db->bind(':id', $data['id']);
        
        return $this->db->execute();
    }
    
    /**
     * Delete deal
     * 
     * @param int $id Deal ID
     * @return boolean True if deleted, false otherwise
     */
    public function deleteDeal($id) {
        $this->db->query("DELETE FROM deals WHERE id = :id");
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Update deal stage
     * 
     * @param int $id Deal ID
     * @param string $stage New stage
     * @param float $probability New probability (optional)
     * @return boolean True if updated, false otherwise
     */
    public function updateStage($id, $stage, $probability = null) {
        // If probability not provided, calculate based on stage
        if($probability === null) {
            $probability = $this->getStageProbability($stage);
        }
        
        $this->db->query("UPDATE deals SET 
                          stage = :stage, 
                          probability = :probability, 
                          actual_close_date = :actual_close_date 
                          WHERE id = :id");
        
        // Set actual close date if deal is closed
        $actualCloseDate = null;
        if($stage == 'closed-won' || $stage == 'closed-lost') {
            $actualCloseDate = date('Y-m-d');
        }
        
        $this->db->bind(':stage', $stage);
        $this->db->bind(':probability', $probability);
        $this->db->bind(':actual_close_date', $actualCloseDate);
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }
    
    /**
     * Get deals for a contact
     * 
     * @param int $contactId Contact ID
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of deal objects
     */
    public function getContactDeals($contactId, $limit = 10, $offset = 0) {
        $this->db->query("SELECT d.*, u.name as owner_name 
                         FROM deals d 
                         LEFT JOIN users u ON d.owner_id = u.id 
                         WHERE d.contact_id = :contact_id 
                         ORDER BY d.created_at DESC 
                         LIMIT :limit OFFSET :offset");
        
        $this->db->bind(':contact_id', $contactId);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total deals for a contact
     * 
     * @param int $contactId Contact ID
     * @return int Total number of deals
     */
    public function countContactDeals($contactId) {
        $this->db->query("SELECT COUNT(*) as total FROM deals WHERE contact_id = :contact_id");
        $this->db->bind(':contact_id', $contactId);
        
        $result = $this->db->single();
        
        return $result->total;
    }
    
    /**
     * Get deals by stage for pipeline view
     * 
     * @param array $filters Filter options
     * @return array Array of deals grouped by stage
     */
    public function getPipelineDeals($filters = []) {
        // Get all deals with filters
        $deals = $this->getDeals($filters, 1000, 0); // High limit to get all deals
        
        // Group by stage
        $stages = [
            'lead' => [],
            'qualified' => [],
            'proposal' => [],
            'negotiation' => [],
            'closed-won' => [],
            'closed-lost' => []
        ];
        
        foreach($deals as $deal) {
            $stages[$deal->stage][] = $deal;
        }
        
        return $stages;
    }
    
    /**
     * Get total deal value by stage
     * 
     * @param array $filters Filter options
     * @return array Array of total values by stage
     */
    public function getTotalValueByStage($filters = []) {
        $sql = "SELECT stage, SUM(amount) as total_value, COUNT(*) as count 
                FROM deals 
                WHERE 1=1";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['owner_id'])) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['expected_close_date_start'])) {
            $sql .= " AND expected_close_date >= :expected_close_date_start";
            $params[':expected_close_date_start'] = $filters['expected_close_date_start'];
        }
        
        if(!empty($filters['expected_close_date_end'])) {
            $sql .= " AND expected_close_date <= :expected_close_date_end";
            $params[':expected_close_date_end'] = $filters['expected_close_date_end'];
        }
        
        $sql .= " GROUP BY stage";
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $results = $this->db->resultSet();
        
        // Format results
        $totalByStage = [
            'lead' => ['total_value' => 0, 'count' => 0],
            'qualified' => ['total_value' => 0, 'count' => 0],
            'proposal' => ['total_value' => 0, 'count' => 0],
            'negotiation' => ['total_value' => 0, 'count' => 0],
            'closed-won' => ['total_value' => 0, 'count' => 0],
            'closed-lost' => ['total_value' => 0, 'count' => 0]
        ];
        
        foreach($results as $row) {
            $totalByStage[$row->stage] = [
                'total_value' => $row->total_value,
                'count' => $row->count
            ];
        }
        
        return $totalByStage;
    }
    
    /**
     * Get deal forecast (weighted value based on probability)
     * 
     * @param array $filters Filter options
     * @return float Forecast value
     */
    public function getForecast($filters = []) {
        $sql = "SELECT SUM(amount * probability / 100) as forecast 
                FROM deals 
                WHERE stage NOT IN ('closed-won', 'closed-lost')";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['owner_id'])) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['expected_close_date_start'])) {
            $sql .= " AND expected_close_date >= :expected_close_date_start";
            $params[':expected_close_date_start'] = $filters['expected_close_date_start'];
        }
        
        if(!empty($filters['expected_close_date_end'])) {
            $sql .= " AND expected_close_date <= :expected_close_date_end";
            $params[':expected_close_date_end'] = $filters['expected_close_date_end'];
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $result = $this->db->single();
        
        return $result->forecast ?? 0;
    }
    
    /**
     * Get won deals total value
     * 
     * @param array $filters Filter options
     * @return float Total value
     */
    public function getWonDealsValue($filters = []) {
        $sql = "SELECT SUM(amount) as total_value 
                FROM deals 
                WHERE stage = 'closed-won'";
        
        $params = [];
        
        // Apply filters
        if(!empty($filters['owner_id'])) {
            $sql .= " AND owner_id = :owner_id";
            $params[':owner_id'] = $filters['owner_id'];
        }
        
        if(!empty($filters['period'])) {
            switch($filters['period']) {
                case 'today':
                    $sql .= " AND DATE(actual_close_date) = CURDATE()";
                    break;
                case 'this_week':
                    $sql .= " AND YEARWEEK(actual_close_date) = YEARWEEK(CURDATE())";
                    break;
                case 'this_month':
                    $sql .= " AND MONTH(actual_close_date) = MONTH(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())";
                    break;
                case 'this_quarter':
                    $sql .= " AND QUARTER(actual_close_date) = QUARTER(CURDATE()) AND YEAR(actual_close_date) = YEAR(CURDATE())";
                    break;
                case 'this_year':
                    $sql .= " AND YEAR(actual_close_date) = YEAR(CURDATE())";
                    break;
            }
        }
        
        if(!empty($filters['start_date'])) {
            $sql .= " AND actual_close_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }
        
        if(!empty($filters['end_date'])) {
            $sql .= " AND actual_close_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach($params as $param => $value) {
            $this->db->bind($param, $value);
        }
        
        $result = $this->db->single();
        
        return $result->total_value ?? 0;
    }
    
    /**
     * Get default probability for a stage
     * 
     * @param string $stage Deal stage
     * @return int Probability percentage
     */
    private function getStageProbability($stage) {
        switch($stage) {
            case 'lead':
                return 10;
            case 'qualified':
                return 30;
            case 'proposal':
                return 50;
            case 'negotiation':
                return 80;
            case 'closed-won':
                return 100;
            case 'closed-lost':
                return 0;
            default:
                return 10;
        }
    }
} 
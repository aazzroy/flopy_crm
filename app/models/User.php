<?php
/**
 * User Model
 * 
 * This class handles all user-related database operations.
 */
class User {
    private $db;
    
    /**
     * Constructor - Initialize database connection
     */
    public function __construct() {
        $this->db = new Database();
    }
    
    /**
     * Register a new user
     * 
     * @param array $data User data
     * @return boolean True if registered successfully, false otherwise
     */
    public function register($data) {
        // Prepare query
        $this->db->query("INSERT INTO users (name, email, password, role_id) VALUES (:name, :email, :password, :role_id)");
        
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role_id', $data['role_id'] ?? ROLE_AGENT);
        
        // Execute
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    /**
     * Find user by email
     * 
     * @param string $email User email
     * @return object|boolean User object or false if not found
     */
    public function findUserByEmail($email) {
        $this->db->query("SELECT * FROM users WHERE email = :email");
        $this->db->bind(':email', $email);
        
        $row = $this->db->single();
        
        // Check if row exists
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return object|boolean User object or false if not found
     */
    public function getUserById($id) {
        $this->db->query("SELECT u.*, r.name as role_name 
                         FROM users u 
                         JOIN roles r ON u.role_id = r.id 
                         WHERE u.id = :id");
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        // Check if row exists
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Login user
     * 
     * @param string $email User email
     * @param string $password User password
     * @return object|boolean User object or false if login fails
     */
    public function login($email, $password) {
        $user = $this->findUserByEmail($email);
        
        if(!$user) {
            return false;
        }
        
        // Check if user is active
        if($user->status !== 'active') {
            return false;
        }
        
        // Verify password
        if(password_verify($password, $user->password)) {
            // Update last login time
            $this->updateLastLogin($user->id);
            
            // Check if password needs rehash
            if(password_needs_rehash($user->password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST])) {
                $this->updatePassword($user->id, $password);
            }
            
            return $user;
        } else {
            return false;
        }
    }
    
    /**
     * Update last login time
     * 
     * @param int $userId User ID
     * @return boolean True if updated, false otherwise
     */
    public function updateLastLogin($userId) {
        $this->db->query("UPDATE users SET last_login = NOW() WHERE id = :id");
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Update user password
     * 
     * @param int $userId User ID
     * @param string $password New password (plain text)
     * @return boolean True if updated, false otherwise
     */
    public function updatePassword($userId, $password) {
        $this->db->query("UPDATE users SET password = :password WHERE id = :id");
        $this->db->bind(':password', password_hash($password, PASSWORD_BCRYPT, ['cost' => PASSWORD_HASH_COST]));
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Update user profile
     * 
     * @param array $data User data
     * @return boolean True if updated, false otherwise
     */
    public function updateProfile($data) {
        $this->db->query("UPDATE users SET 
                          name = :name, 
                          email = :email, 
                          phone = :phone, 
                          position = :position, 
                          theme = :theme 
                          WHERE id = :id");
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':phone', $data['phone'] ?? null);
        $this->db->bind(':position', $data['position'] ?? null);
        $this->db->bind(':theme', $data['theme'] ?? DEFAULT_THEME);
        $this->db->bind(':id', $data['id']);
        
        return $this->db->execute();
    }
    
    /**
     * Update profile image
     * 
     * @param int $userId User ID
     * @param string $imagePath Path to profile image
     * @return boolean True if updated, false otherwise
     */
    public function updateProfileImage($userId, $imagePath) {
        $this->db->query("UPDATE users SET profile_image = :image WHERE id = :id");
        $this->db->bind(':image', $imagePath);
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Get all users
     * 
     * @param int $limit Limit results
     * @param int $offset Offset results
     * @return array Array of user objects
     */
    public function getUsers($limit = 10, $offset = 0) {
        $this->db->query("SELECT u.*, r.name as role_name 
                         FROM users u 
                         JOIN roles r ON u.role_id = r.id 
                         ORDER BY u.name 
                         LIMIT :limit OFFSET :offset");
        
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        $this->db->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }
    
    /**
     * Count total users
     * 
     * @return int Total number of users
     */
    public function countUsers() {
        $this->db->query("SELECT COUNT(*) as total FROM users");
        $result = $this->db->single();
        
        return $result->total;
    }
    
    /**
     * Delete user
     * 
     * @param int $userId User ID
     * @return boolean True if deleted, false otherwise
     */
    public function deleteUser($userId) {
        $this->db->query("DELETE FROM users WHERE id = :id");
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Update user status
     * 
     * @param int $userId User ID
     * @param string $status New status (active, inactive, suspended)
     * @return boolean True if updated, false otherwise
     */
    public function updateStatus($userId, $status) {
        $this->db->query("UPDATE users SET status = :status WHERE id = :id");
        $this->db->bind(':status', $status);
        $this->db->bind(':id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Generate API token for user
     * 
     * @param int $userId User ID
     * @return string|boolean API token or false if failed
     */
    public function generateApiToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + API_KEY_LIFETIME);
        
        $this->db->query("UPDATE users SET api_token = :token, api_token_expiry = :expiry WHERE id = :id");
        $this->db->bind(':token', $token);
        $this->db->bind(':expiry', $expiry);
        $this->db->bind(':id', $userId);
        
        if($this->db->execute()) {
            return $token;
        } else {
            return false;
        }
    }
    
    /**
     * Verify API token
     * 
     * @param string $token API token
     * @return object|boolean User object or false if invalid
     */
    public function verifyApiToken($token) {
        $this->db->query("SELECT * FROM users WHERE api_token = :token AND api_token_expiry > NOW()");
        $this->db->bind(':token', $token);
        
        $row = $this->db->single();
        
        if($this->db->rowCount() > 0) {
            return $row;
        } else {
            return false;
        }
    }
    
    /**
     * Get user roles
     * 
     * @return array Array of role objects
     */
    public function getRoles() {
        $this->db->query("SELECT * FROM roles ORDER BY id");
        
        return $this->db->resultSet();
    }
} 
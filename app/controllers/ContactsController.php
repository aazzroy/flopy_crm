<?php
/**
 * Contacts Controller
 * 
 * Handles contact listing, creation, editing, and deletion.
 */
class ContactsController extends Controller {
    private $contactModel;
    private $userModel;
    private $interactionModel;
    private $dealModel;
    
    /**
     * Constructor - Initialize models
     */
    public function __construct() {
        // Check if user is logged in
        if(!isset($_SESSION['user_id'])) {
            redirect('users/login');
        }
        
        // Load models
        $this->contactModel = $this->model('Contact');
        $this->userModel = $this->model('User');
        $this->interactionModel = $this->model('Interaction');
        $this->dealModel = $this->model('Deal');
    }
    
    /**
     * Contacts index page
     */
    public function index() {
        // Get filters from GET parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'owner_id' => isset($_GET['owner_id']) && !empty($_GET['owner_id']) ? (int)$_GET['owner_id'] : null,
            'lead_status' => $_GET['lead_status'] ?? '',
            'lead_source' => $_GET['lead_source'] ?? '',
            'tags' => isset($_GET['tags']) && is_array($_GET['tags']) ? $_GET['tags'] : []
        ];
        
        // Get pagination parameters
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        // Get sorting parameters
        $sortBy = $_GET['sort_by'] ?? 'created_at';
        $sortDir = $_GET['sort_dir'] ?? 'DESC';
        
        // Get contacts
        $contacts = $this->contactModel->getContacts($filters, $limit, $offset, $sortBy, $sortDir);
        $totalContacts = $this->contactModel->countContacts($filters);
        
        // Calculate total pages
        $totalPages = ceil($totalContacts / $limit);
        
        // Get users for owner filter
        $users = $this->userModel->getUsers(100, 0);
        
        // Get lead statuses and sources for filters
        $leadStatuses = $this->contactModel->getLeadStatuses();
        $leadSources = $this->contactModel->getLeadSources();
        
        // Get all tags
        $tags = $this->contactModel->getAllTags();
        
        // Prepare data for view
        $data = [
            'title' => 'Contacts',
            'contacts' => $contacts,
            'filters' => $filters,
            'users' => $users,
            'lead_statuses' => $leadStatuses,
            'lead_sources' => $leadSources,
            'tags' => $tags,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total_items' => $totalContacts,
                'total_pages' => $totalPages
            ],
            'sorting' => [
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir
            ]
        ];
        
        // Load view
        $this->view('contacts/index', $data);
    }
    
    /**
     * View contact details
     * 
     * @param string $view View name
     * @param array $data Data to pass to the view
     */
    public function view($view, $data = []) {
        // Call parent view method
        parent::view($view, $data);
    }
    
    /**
     * View a contact
     * 
     * @param int $id Contact ID
     */
    public function viewContact($id) {
        // Get contact
        $contact = $this->contactModel->getContactById($id);
        
        // Check if contact exists
        if(!$contact) {
            $this->setFlash('contact_error', 'Contact not found', 'alert alert-danger');
            $this->redirect('contacts');
            return;
        }
        
        // Get interactions
        $interactions = $this->interactionModel->getContactInteractions($id, 10, 0);
        $totalInteractions = $this->interactionModel->countContactInteractions($id);
        
        // Get deals
        $deals = $this->dealModel->getContactDeals($id, 10, 0);
        $totalDeals = $this->dealModel->countContactDeals($id);
        
        // Get users for owner select
        $users = $this->userModel->getUsers(100, 0);
        
        // Prepare data for view
        $data = [
            'title' => $contact->first_name . ' ' . $contact->last_name,
            'contact' => $contact,
            'interactions' => $interactions,
            'total_interactions' => $totalInteractions,
            'deals' => $deals,
            'total_deals' => $totalDeals,
            'users' => $users
        ];
        
        // Load view
        $this->view('contacts/view', $data);
    }
    
    /**
     * Add new contact
     */
    public function add() {
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validate CSRF token
            if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('contact_error', 'Security validation failed. Please try again.', 'alert alert-danger');
                $this->redirect('contacts/add');
                return;
            }
            
            // Init data
            $data = [
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'mobile' => trim($_POST['mobile'] ?? ''),
                'company' => trim($_POST['company'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'zip' => trim($_POST['zip'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'lead_source' => trim($_POST['lead_source'] ?? ''),
                'lead_status' => trim($_POST['lead_status'] ?? ''),
                'lead_score' => !empty($_POST['lead_score']) ? (int)$_POST['lead_score'] : null,
                'owner_id' => !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null,
                'tags' => isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [],
                'created_by' => $_SESSION['user_id'],
                'first_name_error' => '',
                'last_name_error' => '',
                'email_error' => ''
            ];
            
            // Validate first name
            if(empty($data['first_name'])) {
                $data['first_name_error'] = 'Please enter first name';
            }
            
            // Validate last name
            if(empty($data['last_name'])) {
                $data['last_name_error'] = 'Please enter last name';
            }
            
            // Validate email if provided
            if(!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_error'] = 'Please enter a valid email';
            }
            
            // Make sure errors are empty
            if(empty($data['first_name_error']) && empty($data['last_name_error']) && empty($data['email_error'])) {
                // Add contact
                $contactId = $this->contactModel->addContact($data);
                
                if($contactId) {
                    // Log the activity
                    logActivity('Added new contact: ' . $data['first_name'] . ' ' . $data['last_name']);
                    
                    $this->setFlash('contact_success', 'Contact added successfully', 'alert alert-success');
                    $this->redirect('contacts/view/' . $contactId);
                } else {
                    $this->setFlash('contact_error', 'Something went wrong', 'alert alert-danger');
                    $this->view('contacts/add', $data);
                }
            } else {
                // Load view with errors
                $this->view('contacts/add', $data);
            }
        } else {
            // Get users for owner select
            $users = $this->userModel->getUsers(100, 0);
            
            // Get all tags
            $tags = $this->contactModel->getAllTags();
            
            // Get lead statuses and sources for dropdowns
            $leadStatuses = $this->contactModel->getLeadStatuses();
            $leadSources = $this->contactModel->getLeadSources();
            
            // Init data
            $data = [
                'first_name' => '',
                'last_name' => '',
                'email' => '',
                'phone' => '',
                'mobile' => '',
                'company' => '',
                'position' => '',
                'website' => '',
                'address' => '',
                'city' => '',
                'state' => '',
                'zip' => '',
                'country' => '',
                'notes' => '',
                'lead_source' => '',
                'lead_status' => '',
                'lead_score' => '',
                'owner_id' => $_SESSION['user_id'],
                'tags' => [],
                'users' => $users,
                'tags_list' => $tags,
                'lead_statuses' => $leadStatuses,
                'lead_sources' => $leadSources,
                'first_name_error' => '',
                'last_name_error' => '',
                'email_error' => ''
            ];
            
            // Load view
            $this->view('contacts/add', $data);
        }
    }
    
    /**
     * Edit contact
     * 
     * @param int $id Contact ID
     */
    public function edit($id) {
        // Get contact
        $contact = $this->contactModel->getContactById($id);
        
        // Check if contact exists
        if(!$contact) {
            $this->setFlash('contact_error', 'Contact not found', 'alert alert-danger');
            $this->redirect('contacts');
            return;
        }
        
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validate CSRF token
            if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('contact_error', 'Security validation failed. Please try again.', 'alert alert-danger');
                $this->redirect('contacts/edit/' . $id);
                return;
            }
            
            // Init data
            $data = [
                'id' => $id,
                'first_name' => trim($_POST['first_name']),
                'last_name' => trim($_POST['last_name']),
                'email' => trim($_POST['email'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'mobile' => trim($_POST['mobile'] ?? ''),
                'company' => trim($_POST['company'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'website' => trim($_POST['website'] ?? ''),
                'address' => trim($_POST['address'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'state' => trim($_POST['state'] ?? ''),
                'zip' => trim($_POST['zip'] ?? ''),
                'country' => trim($_POST['country'] ?? ''),
                'notes' => trim($_POST['notes'] ?? ''),
                'lead_source' => trim($_POST['lead_source'] ?? ''),
                'lead_status' => trim($_POST['lead_status'] ?? ''),
                'lead_score' => !empty($_POST['lead_score']) ? (int)$_POST['lead_score'] : null,
                'owner_id' => !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null,
                'tags' => isset($_POST['tags']) && is_array($_POST['tags']) ? $_POST['tags'] : [],
                'first_name_error' => '',
                'last_name_error' => '',
                'email_error' => ''
            ];
            
            // Validate first name
            if(empty($data['first_name'])) {
                $data['first_name_error'] = 'Please enter first name';
            }
            
            // Validate last name
            if(empty($data['last_name'])) {
                $data['last_name_error'] = 'Please enter last name';
            }
            
            // Validate email if provided
            if(!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $data['email_error'] = 'Please enter a valid email';
            }
            
            // Make sure errors are empty
            if(empty($data['first_name_error']) && empty($data['last_name_error']) && empty($data['email_error'])) {
                // Update contact
                if($this->contactModel->updateContact($data)) {
                    // Log the activity
                    logActivity('Updated contact: ' . $data['first_name'] . ' ' . $data['last_name']);
                    
                    $this->setFlash('contact_success', 'Contact updated successfully', 'alert alert-success');
                    $this->redirect('contacts/view/' . $id);
                } else {
                    $this->setFlash('contact_error', 'Something went wrong', 'alert alert-danger');
                    $this->view('contacts/edit', $data);
                }
            } else {
                // Load view with errors
                $this->view('contacts/edit', $data);
            }
        } else {
            // Get users for owner select
            $users = $this->userModel->getUsers(100, 0);
            
            // Get all tags
            $tags = $this->contactModel->getAllTags();
            
            // Get contact tags
            $contactTags = $this->contactModel->getContactTags($id);
            $contactTagIds = array_map(function($tag) {
                return $tag->id;
            }, $contactTags);
            
            // Get lead statuses and sources for dropdowns
            $leadStatuses = $this->contactModel->getLeadStatuses();
            $leadSources = $this->contactModel->getLeadSources();
            
            // Init data with contact info
            $data = [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email ?? '',
                'phone' => $contact->phone ?? '',
                'mobile' => $contact->mobile ?? '',
                'company' => $contact->company ?? '',
                'position' => $contact->position ?? '',
                'website' => $contact->website ?? '',
                'address' => $contact->address ?? '',
                'city' => $contact->city ?? '',
                'state' => $contact->state ?? '',
                'zip' => $contact->zip ?? '',
                'country' => $contact->country ?? '',
                'notes' => $contact->notes ?? '',
                'lead_source' => $contact->lead_source ?? '',
                'lead_status' => $contact->lead_status ?? '',
                'lead_score' => $contact->lead_score ?? '',
                'owner_id' => $contact->owner_id ?? '',
                'tags' => $contactTagIds,
                'users' => $users,
                'tags_list' => $tags,
                'lead_statuses' => $leadStatuses,
                'lead_sources' => $leadSources,
                'first_name_error' => '',
                'last_name_error' => '',
                'email_error' => ''
            ];
            
            // Load view
            $this->view('contacts/edit', $data);
        }
    }
    
    /**
     * Delete contact
     * 
     * @param int $id Contact ID
     */
    public function delete($id) {
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->redirect('contacts');
            return;
        }
        
        // Validate CSRF token
        if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->setFlash('contact_error', 'Security validation failed. Please try again.', 'alert alert-danger');
            $this->redirect('contacts');
            return;
        }
        
        // Get contact
        $contact = $this->contactModel->getContactById($id);
        
        // Check if contact exists
        if(!$contact) {
            $this->setFlash('contact_error', 'Contact not found', 'alert alert-danger');
            $this->redirect('contacts');
            return;
        }
        
        // Delete contact
        if($this->contactModel->deleteContact($id)) {
            // Log the activity
            logActivity('Deleted contact: ' . $contact->first_name . ' ' . $contact->last_name);
            
            $this->setFlash('contact_success', 'Contact deleted successfully', 'alert alert-success');
        } else {
            $this->setFlash('contact_error', 'Unable to delete contact', 'alert alert-danger');
        }
        
        $this->redirect('contacts');
    }
    
    /**
     * Upload contact avatar
     * 
     * @param int $id Contact ID
     */
    public function uploadAvatar($id) {
        // Check if AJAX request
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->redirect('contacts/view/' . $id);
            return;
        }
        
        // Get contact
        $contact = $this->contactModel->getContactById($id);
        
        // Check if contact exists
        if(!$contact) {
            $this->json(['error' => 'Contact not found'], 404);
            return;
        }
        
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check if file was uploaded
            if(!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != 0) {
                $this->json(['error' => 'No file uploaded or upload error'], 400);
                return;
            }
            
            // Get file info
            $file = $_FILES['avatar'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            
            // Get file extension
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Allowed extensions
            $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
            
            // Check if file has valid extension
            if(!in_array($fileExt, $allowedExts)) {
                $this->json(['error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedExts)], 400);
                return;
            }
            
            // Check file size (5MB max)
            if($fileSize > MAX_UPLOAD_SIZE) {
                $this->json(['error' => 'File too large. Max size: ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB'], 400);
                return;
            }
            
            // Generate unique filename
            $newFileName = uniqid('contact_') . '.' . $fileExt;
            $uploadPath = APPROOT . '/public/uploads/contact_avatars/' . $newFileName;
            
            // Create directory if doesn't exist
            if(!is_dir(dirname($uploadPath))) {
                mkdir(dirname($uploadPath), 0777, true);
            }
            
            // Move file to uploads directory
            if(move_uploaded_file($fileTmpName, $uploadPath)) {
                // Update contact avatar in database
                $relativeImagePath = 'uploads/contact_avatars/' . $newFileName;
                
                if($this->contactModel->updateAvatar($id, $relativeImagePath)) {
                    // Success
                    $this->json([
                        'success' => true,
                        'message' => 'Avatar uploaded successfully',
                        'image_path' => $relativeImagePath
                    ]);
                } else {
                    // Database update failed
                    $this->json(['error' => 'Failed to update avatar in database'], 500);
                }
            } else {
                // File upload failed
                $this->json(['error' => 'Failed to upload file'], 500);
            }
        } else {
            // Not a POST request
            $this->json(['error' => 'Invalid request method'], 405);
        }
    }
    
    /**
     * Import contacts
     */
    public function import() {
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Validate CSRF token
            if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('contact_error', 'Security validation failed. Please try again.', 'alert alert-danger');
                $this->redirect('contacts/import');
                return;
            }
            
            // Check if file was uploaded
            if(!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] != 0) {
                $this->setFlash('contact_error', 'No file uploaded or upload error', 'alert alert-danger');
                $this->redirect('contacts/import');
                return;
            }
            
            // Get file info
            $file = $_FILES['csv_file'];
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            
            // Get file extension
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Check if file is CSV
            if($fileExt != 'csv') {
                $this->setFlash('contact_error', 'Invalid file type. Only CSV files are allowed.', 'alert alert-danger');
                $this->redirect('contacts/import');
                return;
            }
            
            // Open file
            $handle = fopen($fileTmpName, 'r');
            
            // Check if file was opened successfully
            if(!$handle) {
                $this->setFlash('contact_error', 'Unable to open file', 'alert alert-danger');
                $this->redirect('contacts/import');
                return;
            }
            
            // Read header row
            $header = fgetcsv($handle);
            
            // Convert header to lowercase for case-insensitive matching
            $header = array_map('strtolower', $header);
            
            // Required fields
            $requiredFields = ['first_name', 'last_name'];
            
            // Check if required fields exist in header
            foreach($requiredFields as $field) {
                if(!in_array($field, $header)) {
                    $this->setFlash('contact_error', 'CSV file must contain columns: ' . implode(', ', $requiredFields), 'alert alert-danger');
                    $this->redirect('contacts/import');
                    return;
                }
            }
            
            // Parse contacts
            $contacts = [];
            while(($row = fgetcsv($handle)) !== false) {
                // Create associative array using header as keys
                $contact = array_combine($header, $row);
                
                // Add created_by
                $contact['created_by'] = $_SESSION['user_id'];
                
                // Add to contacts array
                $contacts[] = $contact;
            }
            
            // Close file
            fclose($handle);
            
            // Import contacts
            $result = $this->contactModel->importContacts($contacts, $_SESSION['user_id']);
            
            // Check result
            if($result['imported'] > 0) {
                // Log the activity
                logActivity('Imported ' . $result['imported'] . ' contacts');
                
                $this->setFlash('contact_success', $result['imported'] . ' contacts imported successfully' . 
                                ($result['failed'] > 0 ? ' (' . $result['failed'] . ' failed)' : ''), 
                               'alert alert-success');
            } else {
                $this->setFlash('contact_error', 'No contacts imported', 'alert alert-danger');
            }
            
            $this->redirect('contacts');
        } else {
            // Load view
            $this->view('contacts/import');
        }
    }
    
    /**
     * Export contacts
     */
    public function export() {
        // Get export format
        $format = $_GET['format'] ?? 'csv';
        
        // Get filters from GET parameters
        $filters = [
            'search' => $_GET['search'] ?? '',
            'owner_id' => isset($_GET['owner_id']) && !empty($_GET['owner_id']) ? (int)$_GET['owner_id'] : null,
            'lead_status' => $_GET['lead_status'] ?? '',
            'lead_source' => $_GET['lead_source'] ?? '',
            'tags' => isset($_GET['tags']) && is_array($_GET['tags']) ? $_GET['tags'] : []
        ];
        
        // Get all contacts matching filters
        $contacts = $this->contactModel->getContacts($filters, 1000, 0); // High limit to get all contacts
        
        // Check if any contacts were found
        if(empty($contacts)) {
            $this->setFlash('contact_error', 'No contacts found to export', 'alert alert-danger');
            $this->redirect('contacts');
            return;
        }
        
        // Export based on format
        switch($format) {
            case 'csv':
                // Set headers for CSV download
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="contacts_export_' . date('Y-m-d') . '.csv"');
                
                // Create file pointer
                $output = fopen('php://output', 'w');
                
                // Add UTF-8 BOM to fix Excel
                fputs($output, "\xEF\xBB\xBF");
                
                // Add header row
                fputcsv($output, [
                    'First Name', 'Last Name', 'Email', 'Phone', 'Mobile', 'Company', 
                    'Position', 'Website', 'Address', 'City', 'State', 'Zip', 'Country', 
                    'Lead Source', 'Lead Status', 'Lead Score', 'Notes'
                ]);
                
                // Add contacts
                foreach($contacts as $contact) {
                    fputcsv($output, [
                        $contact->first_name,
                        $contact->last_name,
                        $contact->email ?? '',
                        $contact->phone ?? '',
                        $contact->mobile ?? '',
                        $contact->company ?? '',
                        $contact->position ?? '',
                        $contact->website ?? '',
                        $contact->address ?? '',
                        $contact->city ?? '',
                        $contact->state ?? '',
                        $contact->zip ?? '',
                        $contact->country ?? '',
                        $contact->lead_source ?? '',
                        $contact->lead_status ?? '',
                        $contact->lead_score ?? '',
                        $contact->notes ?? ''
                    ]);
                }
                
                // Close file pointer
                fclose($output);
                exit;
                
            case 'pdf':
                // TODO: Implement PDF export
                $this->setFlash('contact_error', 'PDF export not implemented yet', 'alert alert-danger');
                $this->redirect('contacts');
                break;
                
            default:
                $this->setFlash('contact_error', 'Invalid export format', 'alert alert-danger');
                $this->redirect('contacts');
                break;
        }
    }
} 
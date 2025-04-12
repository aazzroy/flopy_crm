<?php
/**
 * Users Controller
 * 
 * Handles user registration, authentication, and profile management.
 */
class UsersController extends Controller {
    private $userModel;
    
    /**
     * Constructor - Initialize models
     */
    public function __construct() {
        $this->userModel = $this->model('User');
    }
    
    /**
     * Register new user
     */
    public function register() {
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validate CSRF token
            if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('register_error', 'Security validation failed. Please try again.', 'alert alert-danger');
                $this->redirect('users/register');
                return;
            }
            
            // Check for rate limiting
            if(isRateLimited('register', 3, 300)) {
                $this->setFlash('register_error', 'Too many registration attempts. Please try again later.', 'alert alert-danger');
                $this->redirect('users/register');
                return;
            }
            
            // Init data
            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'role_id' => ROLE_AGENT, // Default role
                'name_error' => '',
                'email_error' => '',
                'password_error' => '',
                'confirm_password_error' => ''
            ];
            
            // Validate name
            if(empty($data['name'])) {
                $data['name_error'] = 'Please enter name';
            }
            
            // Validate email
            if(empty($data['email'])) {
                $data['email_error'] = 'Please enter email';
            } else {
                // Check if email already exists
                if($this->userModel->findUserByEmail($data['email'])) {
                    $data['email_error'] = 'Email is already taken';
                }
            }
            
            // Validate password
            if(empty($data['password'])) {
                $data['password_error'] = 'Please enter password';
            } elseif(strlen($data['password']) < MIN_PASSWORD_LENGTH) {
                $data['password_error'] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters';
            }
            
            // Validate confirm password
            if(empty($data['confirm_password'])) {
                $data['confirm_password_error'] = 'Please confirm password';
            } else {
                if($data['password'] != $data['confirm_password']) {
                    $data['confirm_password_error'] = 'Passwords do not match';
                }
            }
            
            // Make sure errors are empty
            if(empty($data['name_error']) && empty($data['email_error']) && 
               empty($data['password_error']) && empty($data['confirm_password_error'])) {
                
                // Hash password
                $data['password'] = hashPassword($data['password']);
                
                // Register user
                if($this->userModel->register($data)) {
                    logActivity('New user registered: ' . $data['email']);
                    
                    $this->setFlash('register_success', 'You are registered and can now log in', 'alert alert-success');
                    $this->redirect('users/login');
                } else {
                    $this->setFlash('register_error', 'Something went wrong', 'alert alert-danger');
                    $this->view('users/register', $data);
                }
            } else {
                // Load view with errors
                $this->view('users/register', $data);
            }
        } else {
            // Init data
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'name_error' => '',
                'email_error' => '',
                'password_error' => '',
                'confirm_password_error' => ''
            ];
            
            // Load view
            $this->view('users/register', $data);
        }
    }
    
    /**
     * User login
     */
    public function login() {
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validate CSRF token
            if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('login_error', 'Security validation failed. Please try again.', 'alert alert-danger');
                $this->redirect('users/login');
                return;
            }
            
            // Check for rate limiting
            if(isRateLimited('login', 5, 300)) {
                $this->setFlash('login_error', 'Too many login attempts. Please try again later.', 'alert alert-danger');
                $this->redirect('users/login');
                return;
            }
            
            // Init data
            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'remember_me' => isset($_POST['remember_me']),
                'email_error' => '',
                'password_error' => ''
            ];
            
            // Validate email
            if(empty($data['email'])) {
                $data['email_error'] = 'Please enter email';
            }
            
            // Validate password
            if(empty($data['password'])) {
                $data['password_error'] = 'Please enter password';
            }
            
            // Check for user/email
            $user = $this->userModel->findUserByEmail($data['email']);
            
            if($user) {
                // Check if user is active
                if($user->status !== 'active') {
                    $data['email_error'] = 'This account is not active';
                    $this->view('users/login', $data);
                    return;
                }
                
                // User found, check password
                if(verifyPassword($data['password'], $user->password)) {
                    // Password match, create session
                    setUserSession($user);
                    
                    // Log the activity
                    logActivity('User logged in: ' . $user->email, $user->id);
                    
                    // Set remember me cookie if requested
                    if($data['remember_me']) {
                        $token = $this->userModel->generateApiToken($user->id);
                        if($token) {
                            setcookie('remember_token', $token, time() + 30 * 86400, '/', '', false, true);
                        }
                    }
                    
                    $this->redirect('dashboard');
                } else {
                    $data['password_error'] = 'Password incorrect';
                    $this->view('users/login', $data);
                }
            } else {
                // User not found
                $data['email_error'] = 'No user found with that email';
                $this->view('users/login', $data);
            }
        } else {
            // Check for remember me cookie
            if(isset($_COOKIE['remember_token'])) {
                $user = $this->userModel->verifyApiToken($_COOKIE['remember_token']);
                if($user) {
                    // Valid token, create session
                    setUserSession($user);
                    
                    // Log the activity
                    logActivity('User logged in via remember token: ' . $user->email, $user->id);
                    
                    $this->redirect('dashboard');
                    return;
                } else {
                    // Invalid token, clear cookie
                    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
                }
            }
            
            // Init data
            $data = [
                'email' => '',
                'password' => '',
                'remember_me' => false,
                'email_error' => '',
                'password_error' => ''
            ];
            
            // Load view
            $this->view('users/login', $data);
        }
    }
    
    /**
     * User logout
     */
    public function logout() {
        // Clear session and redirect
        clearUserSession();
        
        // Clear remember me cookie if exists
        if(isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', false, true);
        }
        
        // Log the activity
        logActivity('User logged out');
        
        $this->setFlash('logout_success', 'You are now logged out', 'alert alert-success');
        $this->redirect('users/login');
    }
    
    /**
     * User profile
     */
    public function profile() {
        // Check if user is logged in
        if(!$this->isLoggedIn()) {
            $this->redirect('users/login');
            return;
        }
        
        // Get current user
        $user = $this->getUser();
        
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            
            // Sanitize POST data
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
            
            // Validate CSRF token
            if(!$this->validateCsrfToken($_POST['csrf_token'] ?? '')) {
                $this->setFlash('profile_error', 'Security validation failed. Please try again.', 'alert alert-danger');
                $this->redirect('users/profile');
                return;
            }
            
            // Init data
            $data = [
                'id' => $user->id,
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone'] ?? ''),
                'position' => trim($_POST['position'] ?? ''),
                'theme' => trim($_POST['theme'] ?? DEFAULT_THEME),
                'current_password' => trim($_POST['current_password'] ?? ''),
                'new_password' => trim($_POST['new_password'] ?? ''),
                'confirm_password' => trim($_POST['confirm_password'] ?? ''),
                'name_error' => '',
                'email_error' => '',
                'current_password_error' => '',
                'new_password_error' => '',
                'confirm_password_error' => ''
            ];
            
            // Validate name
            if(empty($data['name'])) {
                $data['name_error'] = 'Please enter name';
            }
            
            // Validate email
            if(empty($data['email'])) {
                $data['email_error'] = 'Please enter email';
            } elseif($data['email'] != $user->email) {
                // Check if email already exists
                if($this->userModel->findUserByEmail($data['email'])) {
                    $data['email_error'] = 'Email is already taken';
                }
            }
            
            // Check if changing password
            $changingPassword = !empty($data['current_password']) || !empty($data['new_password']) || !empty($data['confirm_password']);
            
            if($changingPassword) {
                // Validate current password
                if(empty($data['current_password'])) {
                    $data['current_password_error'] = 'Please enter current password';
                } elseif(!verifyPassword($data['current_password'], $user->password)) {
                    $data['current_password_error'] = 'Current password is incorrect';
                }
                
                // Validate new password
                if(empty($data['new_password'])) {
                    $data['new_password_error'] = 'Please enter new password';
                } elseif(strlen($data['new_password']) < MIN_PASSWORD_LENGTH) {
                    $data['new_password_error'] = 'Password must be at least ' . MIN_PASSWORD_LENGTH . ' characters';
                }
                
                // Validate confirm password
                if(empty($data['confirm_password'])) {
                    $data['confirm_password_error'] = 'Please confirm new password';
                } elseif($data['new_password'] != $data['confirm_password']) {
                    $data['confirm_password_error'] = 'Passwords do not match';
                }
            }
            
            // Check for errors
            $profileErrors = !empty($data['name_error']) || !empty($data['email_error']);
            $passwordErrors = $changingPassword && (!empty($data['current_password_error']) || 
                               !empty($data['new_password_error']) || !empty($data['confirm_password_error']));
            
            if(!$profileErrors && !$passwordErrors) {
                // Update profile
                $profileUpdated = $this->userModel->updateProfile($data);
                
                // Update password if changing
                $passwordUpdated = true;
                if($changingPassword) {
                    $passwordUpdated = $this->userModel->updatePassword($user->id, $data['new_password']);
                }
                
                if($profileUpdated && $passwordUpdated) {
                    // Update session with new data
                    $_SESSION['user_name'] = $data['name'];
                    $_SESSION['user_email'] = $data['email'];
                    $_SESSION['user_theme'] = $data['theme'];
                    
                    $this->setFlash('profile_success', 'Profile updated successfully', 'alert alert-success');
                    $this->redirect('users/profile');
                } else {
                    $this->setFlash('profile_error', 'Something went wrong', 'alert alert-danger');
                    $this->view('users/profile', $data);
                }
            } else {
                // Load view with errors
                $this->view('users/profile', $data);
            }
        } else {
            // Init data with current user info
            $data = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'position' => $user->position ?? '',
                'theme' => $user->theme ?? DEFAULT_THEME,
                'profile_image' => $user->profile_image,
                'current_password' => '',
                'new_password' => '',
                'confirm_password' => '',
                'name_error' => '',
                'email_error' => '',
                'current_password_error' => '',
                'new_password_error' => '',
                'confirm_password_error' => ''
            ];
            
            // Load view
            $this->view('users/profile', $data);
        }
    }
    
    /**
     * Upload profile image
     */
    public function uploadProfileImage() {
        // Check if user is logged in
        if(!$this->isLoggedIn()) {
            $this->json(['error' => 'User not logged in'], 401);
            return;
        }
        
        // Check for POST request
        if($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Check if file was uploaded
            if(!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] != 0) {
                $this->json(['error' => 'No file uploaded or upload error'], 400);
                return;
            }
            
            // Get file info
            $file = $_FILES['profile_image'];
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
            $newFileName = uniqid('profile_') . '.' . $fileExt;
            $uploadPath = APPROOT . '/public/uploads/profile_images/' . $newFileName;
            
            // Create directory if doesn't exist
            if(!is_dir(dirname($uploadPath))) {
                mkdir(dirname($uploadPath), 0777, true);
            }
            
            // Move file to uploads directory
            if(move_uploaded_file($fileTmpName, $uploadPath)) {
                // Update user profile image in database
                $user = $this->getUser();
                $relativeImagePath = 'uploads/profile_images/' . $newFileName;
                
                if($this->userModel->updateProfileImage($user->id, $relativeImagePath)) {
                    // Success
                    $this->json([
                        'success' => true,
                        'message' => 'Profile image uploaded successfully',
                        'image_path' => $relativeImagePath
                    ]);
                } else {
                    // Database update failed
                    $this->json(['error' => 'Failed to update profile image in database'], 500);
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
     * Forgot password
     */
    public function forgotPassword() {
        // TODO: Implement forgot password functionality
        $this->view('users/forgot_password');
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token = '') {
        // TODO: Implement reset password functionality
        $this->view('users/reset_password', ['token' => $token]);
    }
} 
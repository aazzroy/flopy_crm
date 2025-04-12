<?php
/**
 * Dashboard Controller
 * 
 * Handles the dashboard page with metrics and visualizations.
 */
class DashboardController extends Controller {
    private $contactModel;
    private $interactionModel;
    private $dealModel;
    private $eventModel;
    private $reminderModel;
    
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
        $this->interactionModel = $this->model('Interaction');
        $this->dealModel = $this->model('Deal');
        $this->eventModel = $this->model('Event');
        $this->reminderModel = $this->model('Reminder');
    }
    
    /**
     * Dashboard index page
     */
    public function index() {
        // Get contact stats
        $contactStats = [
            'total' => $this->contactModel->countContacts(),
            'by_status' => $this->contactModel->getContactCountByStatus(),
            'by_source' => $this->contactModel->getContactCountBySource(),
            'by_owner' => $this->contactModel->getContactCountByOwner(),
            'recent' => $this->contactModel->getRecentContacts(5)
        ];
        
        // Get interaction stats
        $userId = $_SESSION['user_id'];
        $interactionStats = [
            'by_type' => $this->interactionModel->getInteractionCountByType($userId),
            'by_status' => $this->interactionModel->getInteractionCountByStatus($userId),
            'by_month' => $this->interactionModel->getInteractionCountByMonth($userId),
            'upcoming' => $this->interactionModel->getUpcomingInteractions($userId, 5),
            'recent' => $this->interactionModel->getRecentCompletedInteractions($userId, 5)
        ];
        
        // Get deal stats
        $dealStats = [
            'pipeline' => $this->dealModel->getTotalValueByStage(),
            'forecast' => $this->dealModel->getForecast(),
            'won_this_month' => $this->dealModel->getWonDealsValue(['period' => 'this_month']),
            'won_this_quarter' => $this->dealModel->getWonDealsValue(['period' => 'this_quarter']),
            'won_this_year' => $this->dealModel->getWonDealsValue(['period' => 'this_year'])
        ];
        
        // Get calendar events
        $now = date('Y-m-d H:i:s');
        $oneWeekLater = date('Y-m-d H:i:s', strtotime('+1 week'));
        $calendarStats = [
            'upcoming_events' => $this->eventModel->getUpcomingEvents($userId, 5),
            'day_of_week_summary' => $this->eventModel->getEventCountByDayOfWeek($userId)
        ];
        
        // Get reminders
        $reminderStats = [
            'overdue' => $this->reminderModel->getOverdueReminders($userId, 5),
            'upcoming' => $this->reminderModel->getUpcomingReminders($userId, 5),
            'counts' => $this->reminderModel->countRemindersByStatus($userId)
        ];
        
        // Prepare data for view
        $data = [
            'title' => 'Dashboard',
            'contact_stats' => $contactStats,
            'interaction_stats' => $interactionStats,
            'deal_stats' => $dealStats,
            'calendar_stats' => $calendarStats,
            'reminder_stats' => $reminderStats
        ];
        
        // Load view
        $this->view('dashboard/index', $data);
    }
    
    /**
     * Get dashboard data for AJAX refresh
     */
    public function getData() {
        // Check if AJAX request
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->redirect('dashboard');
            return;
        }
        
        // Get current user ID
        $userId = $_SESSION['user_id'];
        
        // Get requested data type
        $type = $_GET['type'] ?? 'all';
        
        // Initialize response
        $response = [];
        
        // Get data based on type
        switch($type) {
            case 'contacts':
                $response = [
                    'total' => $this->contactModel->countContacts(),
                    'by_status' => $this->contactModel->getContactCountByStatus(),
                    'by_source' => $this->contactModel->getContactCountBySource(),
                    'recent' => $this->contactModel->getRecentContacts(5)
                ];
                break;
                
            case 'interactions':
                $response = [
                    'by_type' => $this->interactionModel->getInteractionCountByType($userId),
                    'by_status' => $this->interactionModel->getInteractionCountByStatus($userId),
                    'by_month' => $this->interactionModel->getInteractionCountByMonth($userId),
                    'upcoming' => $this->interactionModel->getUpcomingInteractions($userId, 5)
                ];
                break;
                
            case 'deals':
                $response = [
                    'pipeline' => $this->dealModel->getTotalValueByStage(),
                    'forecast' => $this->dealModel->getForecast(),
                    'won_this_month' => $this->dealModel->getWonDealsValue(['period' => 'this_month']),
                    'won_this_quarter' => $this->dealModel->getWonDealsValue(['period' => 'this_quarter'])
                ];
                break;
                
            case 'calendar':
                $response = [
                    'upcoming_events' => $this->eventModel->getUpcomingEvents($userId, 5),
                    'day_of_week_summary' => $this->eventModel->getEventCountByDayOfWeek($userId)
                ];
                break;
                
            case 'reminders':
                $response = [
                    'overdue' => $this->reminderModel->getOverdueReminders($userId, 5),
                    'upcoming' => $this->reminderModel->getUpcomingReminders($userId, 5),
                    'counts' => $this->reminderModel->countRemindersByStatus($userId)
                ];
                break;
                
            default:
                // Get basic stats for all sections
                $response = [
                    'contacts' => [
                        'total' => $this->contactModel->countContacts()
                    ],
                    'interactions' => [
                        'upcoming' => count($this->interactionModel->getUpcomingInteractions($userId, 5))
                    ],
                    'deals' => [
                        'forecast' => $this->dealModel->getForecast()
                    ],
                    'reminders' => [
                        'overdue' => count($this->reminderModel->getOverdueReminders($userId, 5))
                    ]
                ];
                break;
        }
        
        // Return JSON response
        $this->json($response);
    }
    
    /**
     * Theme toggle endpoint
     */
    public function toggleTheme() {
        // Check if AJAX request
        if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->redirect('dashboard');
            return;
        }
        
        // Get current user
        $user = $this->getUser();
        
        // Get current theme
        $currentTheme = $_SESSION['user_theme'] ?? DEFAULT_THEME;
        
        // Toggle theme
        $newTheme = ($currentTheme == 'dark') ? 'light' : 'dark';
        
        // Update in database
        $this->model('User')->updateProfile([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'theme' => $newTheme
        ]);
        
        // Update in session
        $_SESSION['user_theme'] = $newTheme;
        
        // Return JSON response
        $this->json(['success' => true, 'theme' => $newTheme]);
    }
} 
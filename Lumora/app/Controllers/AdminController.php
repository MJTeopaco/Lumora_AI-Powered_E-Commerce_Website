<?php 
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Admin;
use App\Models\User;
use App\Models\Notification; 
use App\Models\Shop;
use App\Models\SupportTicket;
use App\Models\AuditLog;
use App\Helpers\RedirectHelper; // Added missing helper import

class AdminController extends Controller {

    protected $adminModel;

    public function __construct() {
        $this->adminModel = new Admin();
        
        // Check if user is admin
        $this->checkAdminAccess();
    }

    /**
     * Check if user has admin access
     */
    private function checkAdminAccess() {
        if (!Session::has('user_id')) {
            Session::set('error', 'Please login to access admin panel');
            header('Location: /login');
            exit();
        }

        // Check if user has admin role (role_id = 3)
        $userId = Session::get('user_id');
        if (!$this->adminModel->isAdmin($userId)) {
            Session::set('error', 'You do not have permission to access this page');
            header('Location: /');
            exit();
        }
    }

    /**
     * Admin Dashboard
     */
    public function dashboard() {
        $userId = Session::get('user_id');
        $username = Session::get('username');
        
        $data = [
            'pageTitle' => 'Admin Dashboard - Lumora',
            'headerTitle' => 'Dashboard Overview',
            'username' => $username,
            'userRole' => 'Administrator',
            'total_users' => $this->adminModel->getTotalUsers(), 
            'total_buyers' => $this->adminModel->getTotalBuyers(),
            'total_sellers' => $this->adminModel->getTotalSellers(),
            'total_admins' => $this->adminModel->getTotalAdmins(),
            'recent_users' => $this->adminModel->getRecentUsers(10)
        ];
        
        $this->view('admin/index', $data, 'admin');
    }

    /**
     * Settings Page - Category Management
     */
    public function settings() {
        $username = Session::get('username');
        
        $data = [
            'pageTitle' => 'Configure Settings - Lumora',
            'headerTitle' => 'Configure Settings',
            'username' => $username,
            'userRole' => 'Administrator',
            'categories' => $this->adminModel->getAllCategories()
        ];

        $this->view('admin/settings', $data, 'admin');
    }

    /**
     * Add Category
     */
    public function addCategory() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/settings');
            exit();
        }

        $name = trim($_POST['category_name'] ?? '');

        // Validation
        if (empty($name)) {
            Session::set('error', 'Category name is required');
            header('Location: /admin/settings');
            exit();
        }

        // Check if category already exists
        if ($this->adminModel->categoryExists($name)) {
            Session::set('error', 'Category already exists');
            header('Location: /admin/settings');
            exit();
        }

        $slug = $this->generateSlug($name);

        if ($this->adminModel->addCategory($name, $slug)) {
            Session::set('success', 'Category added successfully');
        } else {
            Session::set('error', 'Failed to add category');
        }

        header('Location: /admin/settings');
        exit();
    }

    /**
     * Update Category
     */
    public function updateCategory() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/settings');
            exit();
        }

        $categoryId = (int)($_POST['category_id'] ?? 0);
        $name = trim($_POST['category_name'] ?? '');

        // Validation
        if (empty($name)) {
            Session::set('error', 'Category name is required');
            header('Location: /admin/settings');
            exit();
        }

        if ($categoryId <= 0) {
            Session::set('error', 'Invalid category ID');
            header('Location: /admin/settings');
            exit();
        }

        // Check if new name already exists (excluding current category)
        if ($this->adminModel->categoryExistsExcept($name, $categoryId)) {
            Session::set('error', 'Category name already exists');
            header('Location: /admin/settings');
            exit();
        }

        $slug = $this->generateSlug($name);

        if ($this->adminModel->updateCategory($categoryId, $name, $slug)) {
            Session::set('success', 'Category updated successfully');
        } else {
            Session::set('error', 'Failed to update category');
        }

        header('Location: /admin/settings');
        exit();
    }

    /**
     * Delete Category
     */
    public function deleteCategory() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/settings');
            exit();
        }

        $categoryId = (int)($_POST['category_id'] ?? 0);

        if ($categoryId <= 0) {
            Session::set('error', 'Invalid category ID');
            header('Location: /admin/settings');
            exit();
        }

        // Check if category has products
        $productCount = $this->adminModel->getCategoryProductCount($categoryId);
        
        if ($productCount > 0) {
            Session::set('error', "Cannot delete category. It has {$productCount} product(s) assigned to it.");
            header('Location: /admin/settings');
            exit();
        }

        if ($this->adminModel->deleteCategory($categoryId)) {
            Session::set('success', 'Category deleted successfully');
        } else {
            Session::set('error', 'Failed to delete category');
        }

        header('Location: /admin/settings');
        exit();
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }

    /**
     * Users Management Page
     */
    public function users() {
        $username = Session::get('username');
        
        $data = [
            'pageTitle' => 'User Management - Lumora',
            'headerTitle' => 'User Management',
            'username' => $username,
            'userRole' => 'Administrator',
            'users' => $this->adminModel->getAllUsers()
        ];

        $this->view('admin/users', $data, 'admin');
    }

    /**
     * Unlock User Account
     */
    public function unlockUser() {
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/users');
            exit();
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            Session::set('error', 'Invalid user ID');
            header('Location: /admin/users');
            exit();
        }

        $userModel = new User();
        $userModel->resetLoginAttempts($userId);
        
        Session::set('success', 'User account unlocked successfully');
        header('Location: /admin/users');
        exit();
    }

    /**
     * Sellers Management Page
     */
    public function sellers() {
        $username = Session::get('username');
        
        $data = [
            'pageTitle' => 'Seller Management - Lumora',
            'headerTitle' => 'Seller Management',
            'username' => $username,
            'userRole' => 'Administrator',
            'pending_sellers' => $this->adminModel->getPendingSellers(),
            'approved_sellers' => $this->adminModel->getApprovedSellers()
        ];

        $this->view('admin/sellers', $data, 'admin');
    }

    /**
     * Approve Seller Application
     */
    public function approveSeller() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/sellers');
            exit();
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            Session::set('error', 'Invalid user ID');
            header('Location: /admin/sellers');
            exit();
        }

        if ($this->adminModel->approveSeller($userId)) {
            // --- ENHANCED NOTIFICATION SYSTEM INTEGRATION ---
            $shopModel = new Shop();
            $shop = $shopModel->getShopByUserId($userId);
            
            if ($shop) {
                $notificationModel = new Notification();
                $notificationModel->notifyShopApproval(
                    $shop['user_id'],
                    $shop['shop_name']
                );
            }
            // ------------------------------------------------

            // --- NEW CODE: Log the approval ---
            $auditLogger = new AuditLog();
            $currentAdminId = Session::get('user_id'); // The admin who clicked the button
            
            $auditLogger->log(
                $currentAdminId,       // Who performed the action (The Admin)
                'APPROVE_SELLER',      // What they did
                ['approved_user_id' => $userId], // Details (Who they approved)
                $userId                // Target User ID (optional, helps with filtering)
            );
            // ----------------------------------

            Session::set('success', 'Seller application approved successfully! The user can now manage their shop.');
        } else {
            Session::set('error', 'Failed to approve seller. Please try again.');
        }

        header('Location: /admin/sellers');
        exit();
    }

    /**
     * Reject Seller Application
     */
    public function rejectSeller() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/sellers');
            exit();
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            Session::set('error', 'Invalid user ID');
            header('Location: /admin/sellers');
            exit();
        }

        if ($this->adminModel->rejectSeller($userId)) {
            Session::set('success', 'Seller application rejected and removed from the system.');
        } else {
            Session::set('error', 'Failed to reject seller. Please try again.');
        }

        header('Location: /admin/sellers');
        exit();
    }

    /**
     * Suspend Seller Account
     */
    public function suspendSeller() {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/sellers');
            exit();
        }

        $userId = (int)($_POST['user_id'] ?? 0);

        if ($userId <= 0) {
            Session::set('error', 'Invalid user ID');
            header('Location: /admin/sellers');
            exit();
        }

        if ($this->adminModel->suspendSeller($userId)) {
            Session::set('success', 'Seller has been suspended. Their shop is no longer visible on the marketplace.');
        } else {
            Session::set('error', 'Failed to suspend seller. Please try again.');
        }

        header('Location: /admin/sellers');
        exit();
    }

    /**
     * Support Tickets Page
     */
    public function support() {
        $username = Session::get('username');
        $ticketModel = new SupportTicket();
        
        $data = [
            'pageTitle' => 'Support Requests - Lumora',
            'headerTitle' => 'User Support',
            'username' => $username,
            'userRole' => 'Administrator',
            'tickets' => $ticketModel->getAll()
        ];

        $this->view('admin/support', $data, 'admin');
    }

    /**
     * Resolve Ticket
     */
    public function resolveTicket() {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/support');
            exit();
        }

        $ticketId = (int)($_POST['ticket_id'] ?? 0);
        $ticketModel = new SupportTicket();
        
        if ($ticketId > 0 && $ticketModel->markAsResolved($ticketId)) {
            Session::set('success', 'Ticket marked as resolved.');
        } else {
            Session::set('error', 'Failed to update ticket.');
        }
        
        header('Location: /admin/support');
        exit();
    }

    // ==================== NEW REPORTING FEATURES ====================

    /**
     * Main Reports Hub
     */
    public function reports() {
        $this->checkAdminAccess();
        
        $data = [
            'pageTitle' => 'System Reports - Lumora',
            'headerTitle' => 'Reports & Logs',
            'username' => Session::get('username'),
            'userRole' => 'Administrator'
        ];

        $this->view('admin/reports', $data, 'admin');
    }

    /**
     * Audit Logs Viewer with Integrity Check
     */
    public function auditLogs() {
        $this->checkAdminAccess();
        
        // Ensure AuditLog model exists
        $auditModel = new AuditLog();
        
        // Filters
        $filters = [];
        if (isset($_GET['action_type']) && !empty($_GET['action_type'])) {
            $filters['action_type'] = $_GET['action_type'];
        }

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $logs = $auditModel->getLogs($limit, $offset, $filters);
        $totalLogs = $auditModel->getTotalLogs();
        
        // Verify Integrity of log chain
        $integrityStatus = $auditModel->verifyChainIntegrity();

        $data = [
            'pageTitle' => 'Audit Logs - Lumora',
            'headerTitle' => 'System Audit Logs',
            'username' => Session::get('username'),
            'userRole' => 'Administrator',
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => ceil($totalLogs / $limit),
            'integrity' => $integrityStatus
        ];

        $this->view('admin/audit_logs', $data, 'admin');
    }

    /**
     * Sales Reports Viewer
     */
    public function salesReports() {
        $this->checkAdminAccess();
        
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $overview = $this->adminModel->getSalesOverview($startDate, $endDate);
        $dailySales = $this->adminModel->getDailySales($startDate, $endDate);
        $topProducts = $this->adminModel->getTopSellingProducts(5);

        $data = [
            'pageTitle' => 'Sales Reports - Lumora',
            'headerTitle' => 'Sales Analytics',
            'username' => Session::get('username'),
            'userRole' => 'Administrator',
            'overview' => $overview,
            'dailySales' => $dailySales,
            'topProducts' => $topProducts,
            'dateRange' => ['start' => $startDate, 'end' => $endDate]
        ];

        $this->view('admin/sales_reports', $data, 'admin');
    }

    // ==================== PAYOUT MANAGEMENT (NEW) ====================

    /**
     * Payout Management Page
     */
    public function payouts() {
        $this->checkAdminAccess();
        
        $data = [
            'pageTitle' => 'Payout Management - Lumora',
            'headerTitle' => 'Seller Payouts',
            'username' => Session::get('username'),
            'userRole' => 'Administrator',
            'pendingPayouts' => $this->adminModel->getPendingPayouts()
        ];

        $this->view('admin/payouts', $data, 'admin');
    }

    /**
     * Process Payout (Mark as Paid)
     */
    public function processPayout() {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/payouts');
            exit;
        }

        $shopId = (int)($_POST['shop_id'] ?? 0);
        
        if ($shopId > 0 && $this->adminModel->markPayoutAsPaid($shopId)) {
            Session::set('success', 'Payout marked as PAID successfully.');
        } else {
            Session::set('error', 'Failed to update payout status.');
        }
        
        header('Location: /admin/payouts');
        exit;
    }

    /**
     * Machine Learning Reports - Smart Search & Auto-Tagging Analytics
     * Add this method to AdminController.php
     */
    public function machineLearningReports() {
        $this->checkAdminAccess();
        
        // Date filters (default last 30 days)
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Fetch all ML-related data
        $searchVolume = $this->adminModel->getSearchVolumeTrend($startDate, $endDate);
        $missingTagsCount = $this->adminModel->getProductsMissingTags();
        $missingTagsList = $this->adminModel->getProductsMissingTagsList(20);
        $tagDensity = $this->adminModel->getTagDensityDistribution();
        $autoTagStats = $this->adminModel->getAutoTaggingStats();
        $topTags = $this->adminModel->getTopTags(20);
        $confidenceDistribution = $this->adminModel->getConfidenceDistribution();
        $taggingProgress = $this->adminModel->getTaggingProgressOverTime($startDate, $endDate);
        
        $data = [
            'pageTitle' => 'ML Reports - Lumora',
            'headerTitle' => 'Smart Search & Auto-Tagging Analytics',
            'username' => Session::get('username'),
            'userRole' => 'Administrator',
            'searchVolume' => $searchVolume,
            'missingTagsCount' => $missingTagsCount,
            'missingTagsList' => $missingTagsList,
            'tagDensity' => $tagDensity,
            'autoTagStats' => $autoTagStats,
            'topTags' => $topTags,
            'confidenceDistribution' => $confidenceDistribution,
            'taggingProgress' => $taggingProgress,
            'dateRange' => ['start' => $startDate, 'end' => $endDate]
        ];

        $this->view('admin/machine-learning-reports', $data, 'admin');
    }
}
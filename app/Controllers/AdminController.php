<?php 
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Admin;

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
}
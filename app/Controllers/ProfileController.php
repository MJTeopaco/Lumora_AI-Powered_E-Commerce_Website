<?php
// app/Controllers/ProfileController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Models\Order;
use App\Models\Transaction;
use App\Helpers\ValidationHelper;
use App\Helpers\RedirectHelper;

class ProfileController extends Controller
{

    protected $userModel;
    protected $profileModel;
    protected $addressModel;
    protected $orderModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->addressModel = new Address();
        $this->orderModel = new Order();

        // Require authentication for all profile actions
        if (!Session::has('user_id')) {
            RedirectHelper::redirect('/login');
        }
    }

    /**
     * Show user profile page
     */
    public function index()
    {
        // user data
        $isLoggedIn = Session::has('user_id');
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['full_name' => '', 'phone_number' => '', 'gender' => '', 'birth_date' => '', 'profile_pic' => ''];
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        if (!$profile) {
            $profile = [
                'full_name' => '',
                'phone_number' => '',
                'gender' => '',
                'birth_date' => '',
                'profile_pic' => ''
            ];
        }

        // Check for status messages
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        $data = [
            'user' => $user,
            'profile' => $profile,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'info',
            'pageTitle' => 'Personal Information',

            'isLoggedIn' => true
        ];

        $this->view('profile/index', $data, 'profile');
    }

    /**
     * Handle profile update (unified endpoint for all fields)
     */
    public function update()
    {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile');
        }

        $userId = Session::get('user_id');
        $updateField = $_POST['update_field'] ?? 'all';

        // Start with an EMPTY data array
        $data = [];
        $successMessage = 'Profile updated successfully!'; // Default message
        
        // Create a separate array for raw data to pass to create() if needed
        $rawDataForCreate = [
            'full_name' => ValidationHelper::sanitize($_POST['full_name'] ?? ''),
            'phone_number' => preg_replace('/\D/', '', $_POST['phone_number'] ?? ''),
            'gender' => ValidationHelper::sanitize($_POST['gender'] ?? ''),
            'birth_date' => ValidationHelper::sanitize($_POST['birth_date'] ?? ''),
        ];

        // Update only the field being changed
        switch ($updateField) {
            case 'name':
                $rawName = ValidationHelper::sanitize($_POST['full_name'] ?? '');
                $data['full_name'] = $rawName;
                $rawDataForCreate['full_name'] = $rawName;
                $successMessage = 'Full name updated successfully!';
                break;

            case 'phone':
                // Clean phone number - remove all non-digits
                $phoneRaw = preg_replace('/\D/', '', $_POST['phone_number'] ?? '');

                if (!empty($phoneRaw)) {
                    // VALIDATE using logic
                    if (strlen($phoneRaw) !== 11 || !preg_match('/^09\d{9}$/', $phoneRaw)) {
                        $this->redirectWithError('Please enter a valid 11-digit phone number starting with 09');
                        return;
                    }

                    if ($this->profileModel->isPhoneInUse($phoneRaw, $userId)) {
                        $this->redirectWithError('This phone number is already in use by another account');
                        return;
                    }
                }
                
                $data['phone_number'] = $phoneRaw; 
                $rawDataForCreate['phone_number'] = $phoneRaw;
                $successMessage = 'Phone number updated successfully!';
                break;

            case 'email':
                $email = strtolower(trim($_POST['email'] ?? ''));
                
                // Validate
                if (empty($email)) {
                    $this->redirectWithError('Email cannot be empty');
                    return;
                }
                
                if (!ValidationHelper::isValidEmail($email)) {
                    $this->redirectWithError('Please enter a valid email address');
                    return;
                }
                
                // Check if same as current
                $currentUser = $this->userModel->findById($userId);
                if ($email === $currentUser['email']) {
                    $this->redirectWithSuccess('No changes were made');
                    return;
                }
                
                // Check uniqueness
                if ($this->userModel->isEmailInUse($email, $userId)) {
                    $this->redirectWithError('This email is already registered');
                    return;
                }
                
                // Update
                if ($this->userModel->updateEmail($userId, $email)) {
                    Session::set('user_email', $email);
                    $successMessage = 'Email updated successfully!';
                } else {
                    $this->redirectWithError('Failed to update email');
                    return;
                }
                break;

            case 'username':
                $username = trim($_POST['username'] ?? '');
                
                // Validate
                if (empty($username)) {
                    $this->redirectWithError('Username cannot be empty');
                    return;
                }
                
                if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
                    $this->redirectWithError('Username must be 3-20 characters (letters, numbers, underscore only)');
                    return;
                }
                
                // Check if same as current
                $currentUser = $this->userModel->findById($userId);
                if ($username === $currentUser['username']) {
                    $this->redirectWithSuccess('No changes were made');
                    return;
                }
                
                // Check uniqueness
                if ($this->userModel->isUsernameInUse($username, $userId)) {
                    $this->redirectWithError('This username is already taken');
                    return;
                }
                
                // Update
                if ($this->userModel->updateUsername($userId, $username)) {
                    Session::set('username', $username);
                    $successMessage = 'Username updated successfully!';
                } else {
                    $this->redirectWithError('Failed to update username');
                    return;
                }
                break;

            case 'gender':
                $data['gender'] = ValidationHelper::sanitize($_POST['gender'] ?? '');
                $successMessage = 'Gender updated successfully!';
                break;

            case 'birthdate':
                $data['birth_date'] = ValidationHelper::sanitize($_POST['birth_date'] ?? '');
                $successMessage = 'Birth date updated successfully!';
                break;

            case 'picture':
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $uploadResult = $this->handleProfilePictureUpload($_FILES['profile_pic'], $userId);

                    if (!$uploadResult['success']) {
                        $this->redirectWithError($uploadResult['error']);
                        return;
                    }

                    $data['profile_pic'] = $uploadResult['path'];
                    $successMessage = 'Profile picture updated successfully!';
                }
                break;
        }

        // Skip DB update if email or username was handled above
        if (!in_array($updateField, ['email', 'username'])) {
            // Check if profile exists
            $existingProfile = $this->profileModel->getByUserId($userId);

            // Only update non-empty data fields
            $dataToUpdate = array_filter($data, function($value) {
                return $value !== '' && $value !== null;
            });

            if ($existingProfile) {
                // Update existing profile with only the non-empty fields
                if (!empty($dataToUpdate)) {
                    if ($this->profileModel->update($userId, $dataToUpdate)) {
                        $this->redirectWithSuccess($successMessage);
                    } else {
                        $this->redirectWithError('Failed to update profile');
                    }
                } else {
                    // No changes
                    $this->redirectWithSuccess('No changes were made');
                }
            } else {
                // Create new profile - use rawDataForCreate which has all fields
                if ($this->profileModel->create($userId, $rawDataForCreate)) {
                    $this->redirectWithSuccess($successMessage);
                } else {
                    $this->redirectWithError('Failed to create profile');
                }
            }
        }
    }

    private function handleProfilePictureUpload($file, $userId)
    {
        // Validate file
        $validation = ValidationHelper::validateImage($file);

        if (!$validation['valid']) {
            return ['success' => false, 'error' => $validation['error']];
        }

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../public/uploads/profiles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userId . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Delete old profile picture if exists
        $existingProfile = $this->profileModel->getByUserId($userId);
        if ($existingProfile && !empty($existingProfile['profile_pic'])) {
            $oldFile = __DIR__ . '/../../public/' . $existingProfile['profile_pic'];
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Return relative path for database storage
            return [
                'success' => true,
                'path' => 'uploads/profiles/' . $filename
            ];
        }

        return ['success' => false, 'error' => 'Failed to upload file'];
    }

    private function redirectWithError($message)
    {
        $params = [
            'status' => 'error',
            'message' => urlencode($message)
        ];
        $url = '/profile?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }

    private function redirectWithSuccess($message)
    {
        $params = [
            'status' => 'success',
            'message' => urlencode($message)
        ];
        $url = '/profile?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }

    /**
     * Show addresses page
     */
    public function addresses()
    {
        // user data
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        $addresses = $this->addressModel->getAddressesByUserId_User($userId);
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        if (!$profile) {
            $profile = ['profile_pic' => ''];
        }

        // Get addresses from database
        // These are now retrieved as plain text from the model
        $addresses = $this->addressModel->getAddressesByUserId_User($userId);

        // Check for status messages
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        $data = [
            'user' => $user,
            'profile' => $profile,
            'addresses' => $addresses,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'addresses',
            'pageTitle' => 'My Addresses'
        ];

        $this->view('profile/addresses', $data, 'profile');
    }

    // ========================================================================
    // ORDER MANAGEMENT METHODS - NEW FOR CUSTOMER SIDE
    // ========================================================================

    /**
     * Show user's order history
     */
    public function orders()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        
        // Get filter parameters
        $statusFilter = $_GET['status'] ?? 'all';
        $searchTerm = $_GET['search'] ?? '';
        
        // Get orders
        $orders = $this->orderModel->getUserOrders($userId, 50, 0, 
            ($statusFilter !== 'all' ? $statusFilter : null));
        
        // Apply search filter if provided
        if (!empty($searchTerm)) {
            $orders = array_filter($orders, function($order) use ($searchTerm) {
                return stripos((string)$order['order_id'], $searchTerm) !== false;
            });
        }
        
        // Get order statistics
        $stats = $this->orderModel->getUserOrderStats($userId);
        
        // Calculate status counts
        $statusCounts = [
            'all' => count($this->orderModel->getUserOrders($userId, 1000, 0)),
            'pending_payment' => 0,
            'processing' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'cancelled' => 0
        ];
        
        foreach ($this->orderModel->getUserOrders($userId, 1000, 0) as $order) {
            $status = strtolower($order['order_status']);
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }
        
        // Get status message
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status_type'] ?? 'success';
        
        $data = [
            'user' => $user,
            'profile' => $profile,
            'orders' => $orders,
            'stats' => $stats,
            'statusCounts' => $statusCounts,
            'statusFilter' => $statusFilter,
            'searchTerm' => $searchTerm,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'orders',
            'pageTitle' => 'My Orders'
        ];
        
        $this->view('profile/orders', $data, 'profile');
    }

    /**
     * Show order details (AJAX or page)
     */
    public function orderDetails()
    {
        $userId = Session::get('user_id');
        $orderId = $_GET['order_id'] ?? null;
        
        if (!$orderId) {
            // If called via page, redirect
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                RedirectHelper::redirect('/profile/orders');
            }
            
            // If AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order ID required']);
            exit;
        }
        
        // Get order details
        $order = $this->orderModel->getOrderById($orderId, $userId);
        
        if (!$order) {
            // If called via page, redirect with error
            if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                $this->redirectToOrders('Order not found', 'error');
                return;
            }
            
            // If AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }
        
        // Get order items
        $orderItems = $this->getOrderItems($orderId);
        
        // If AJAX request
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'order' => $order,
                'items' => $orderItems
            ]);
            exit;
        }
        
        // If regular page request
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        
        $data = [
            'user' => $user,
            'profile' => $profile,
            'order' => $order,
            'orderItems' => $orderItems,
            'activeTab' => 'orders',
            'pageTitle' => 'Order Details - #' . str_pad($orderId, 6, '0', STR_PAD_LEFT)
        ];
        
        $this->view('profile/order-details', $data, 'profile');
    }

    /**
     * Cancel an order with Refund Logic
     */
    public function cancelOrder()
    {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/orders');
        }
        
        $userId = Session::get('user_id');
        $orderId = $_POST['order_id'] ?? null;
        
        if (!$orderId) {
            $this->redirectToOrders('Invalid order', 'error');
            return;
        }

        // 1. Get Full Order Details
        $order = $this->orderModel->getOrderById($orderId, $userId);

        if (!$order) {
            $this->redirectToOrders('Order not found', 'error');
            return;
        }

        // 2. Logic Split: Paid vs Unpaid
        if ($order['order_status'] === 'PENDING_PAYMENT') {
            // Unpaid: Simple cancellation
            if ($this->orderModel->cancelOrder($orderId, $userId)) {
                $this->redirectToOrders('Order cancelled successfully', 'success');
            } else {
                $this->redirectToOrders('Unable to cancel order', 'error');
            }
        } 
        elseif (in_array($order['order_status'], ['PROCESSING', 'PAID', 'READY_TO_SHIP'])) {
            // Paid: Request Refund (Do NOT auto-refund)
            $this->handlePaidOrderCancellation($order, $userId);
        } 
        else {
            $this->redirectToOrders('Order cannot be cancelled at this stage', 'error');
        }
    }

    /**
     * Helper to handle refund process
     */
    private function handlePaidOrderCancellation($order, $userId) {
        // Mark as refund requested so Seller can review
        $success = $this->orderModel->updateOrderStatus($order['order_id'], 'REFUND_REQUESTED', $userId);

        if ($success) {
            $this->redirectToOrders('Cancellation submitted. Waiting for seller approval.', 'info');
        } else {
            $this->redirectToOrders('Failed to submit cancellation request.', 'error');
        }
    }

    /**
     * Get order items with product details
     */
    private function getOrderItems($orderId)
    {
        $conn = $this->orderModel->getConnection();
        
        $query = "SELECT 
                    oi.*,
                    p.name as product_name,
                    p.cover_picture,
                    pv.color,
                    pv.size,
                    pv.material,
                    s.shop_name
                  FROM order_items oi
                  JOIN product_variants pv ON oi.variant_id = pv.variant_id
                  JOIN products p ON pv.product_id = p.product_id
                  LEFT JOIN shops s ON p.shop_id = s.shop_id
                  WHERE oi.order_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $items = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $items;
    }

    /**
     * Redirect to orders page with message
     */
    private function redirectToOrders($message, $status = 'success')
    {
        $params = [
            'status_type' => $status,
            'message' => urlencode($message)
        ];
        $url = '/profile/orders?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }

    // ========================================================================
    // END OF ORDER MANAGEMENT METHODS
    // ========================================================================

    /**
     * Show settings page
     */
    public function settings()
    {
        // Get user data
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        if (!$profile) {
            $profile = ['profile_pic' => ''];
        }

        // Check for status messages
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        $data = [
            'user' => $user,
            'profile' => $profile,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'settings',
            'pageTitle' => 'Account Settings'
        ];

        $this->view('profile/settings', $data, 'profile');
    }

    public function addAddressForm() {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        $data = [
            'user' => $user,
            'profile' => $profile,
            'isEdit' => false,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'addresses',
            'pageTitle' => 'Add New Address'
        ];

        $this->view('profile/address-form', $data, 'profile');
    }

    public function changePassword()
    {
        // ADDED: CSRF Protection
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/settings');
        }

        $userId = Session::get('user_id');

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate inputs
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $this->redirectToSettings('All fields are required', 'error');
            return;
        }

        if (strlen($newPassword) < 8) {
            $this->redirectToSettings('New password must be at least 8 characters long', 'error');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->redirectToSettings('New passwords do not match', 'error');
            return;
        }

        // Get user
        $user = $this->userModel->findById($userId);

        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            $this->redirectToSettings('Current password is incorrect', 'error');
            return;
        }

        // Update password
        if ($this->userModel->updatePassword($userId, $newPassword)) {
            $this->redirectToSettings('Password updated successfully!', 'success');
        } else {
            $this->redirectToSettings('Failed to update password. Please try again.', 'error');
        }
    }

    private function redirectToSettings($message, $status = 'success')
    {
        $params = [
            'status' => $status,
            'message' => urlencode($message)
        ];
        $url = '/profile/settings?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }

    private function redirectToAddresses($message, $status = 'success')
    {
        $params = [
            'status' => $status,
            'message' => urlencode($message)
        ];
        $url = '/profile/addresses?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }
}
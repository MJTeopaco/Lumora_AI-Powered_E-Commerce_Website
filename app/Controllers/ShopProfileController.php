<?php

namespace App\Controllers;
use App\Core\Controller;
use App\Models\ShopProfile;

class ShopProfileController extends Controller {

    protected $shopProfileModel;

    public function __construct() {
        $this->shopProfileModel = new ShopProfile();
        
        // Ensure user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Display shop profile page
     */
    public function index() {
        // Fetch shop profile data
        $shopData = $this->shopProfileModel->getShopProfileData($_SESSION['user_id']);

        if (!$shopData) {
            // Redirect to dashboard if no shop found
            $_SESSION['error'] = 'Shop not found.';
            header('Location: /shop/dashboard');
            exit;
        }

        // Get shop statistics
        $stats = $this->shopProfileModel->getShopStats($shopData['shop_id']);

        // Merge data
        $data = array_merge($shopData, ['stats' => $stats]);

        // Load the shop profile view with the fetched data
        $this->view('shop/shop-profile', $data, 'shop');
    }

    /**
     * Update shop basic information
     */
    public function updateBasicInfo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        try {
            // Get shop data
            $shopData = $this->shopProfileModel->getShopProfileData($_SESSION['user_id']);
            
            if (!$shopData) {
                $this->jsonResponse(['success' => false, 'message' => 'Shop not found'], 404);
                return;
            }

            $shop_id = $shopData['shop_id'];

            // Validate and sanitize input
            $shop_name = trim($_POST['shop_name'] ?? '');
            $contact_email = trim($_POST['contact_email'] ?? '');
            $contact_phone = trim($_POST['contact_phone'] ?? '');
            $shop_description = trim($_POST['shop_description'] ?? '');

            // Validation
            if (empty($shop_name)) {
                $this->jsonResponse(['success' => false, 'message' => 'Shop name is required']);
                return;
            }

            if (empty($contact_email)) {
                $this->jsonResponse(['success' => false, 'message' => 'Contact email is required']);
                return;
            }

            if (!filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid email format']);
                return;
            }

            // Check if shop name is already taken
            if ($this->shopProfileModel->isShopNameTaken($shop_name, $shop_id)) {
                $this->jsonResponse(['success' => false, 'message' => 'Shop name is already taken']);
                return;
            }

            // Check if email is already in use
            if ($this->shopProfileModel->isEmailInUse($contact_email, $shop_id)) {
                $this->jsonResponse(['success' => false, 'message' => 'Email is already in use']);
                return;
            }

            // Update shop
            $updateData = [
                'shop_name' => $shop_name,
                'contact_email' => $contact_email,
                'contact_phone' => $contact_phone,
                'shop_description' => $shop_description
            ];

            $result = $this->shopProfileModel->update($shop_id, $updateData);

            if ($result) {
                $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Shop information updated successfully'
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update shop information']);
            }

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update shop address
     */
    public function updateAddress() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        try {
            // Get shop data
            $shopData = $this->shopProfileModel->getShopProfileData($_SESSION['user_id']);
            
            if (!$shopData) {
                $this->jsonResponse(['success' => false, 'message' => 'Shop not found'], 404);
                return;
            }

            $shop_id = $shopData['shop_id'];

            // Validate and sanitize input
            $addressData = [
                'address_line1' => trim($_POST['address_line1'] ?? ''),
                'address_line2' => trim($_POST['address_line2'] ?? ''),
                'barangay' => trim($_POST['barangay'] ?? ''),
                'city' => trim($_POST['city'] ?? ''),
                'province' => trim($_POST['province'] ?? ''),
                'region' => trim($_POST['region'] ?? ''),
                'postal_code' => trim($_POST['postal_code'] ?? '')
            ];

            // Validation
            if (empty($addressData['address_line1'])) {
                $this->jsonResponse(['success' => false, 'message' => 'Address line 1 is required']);
                return;
            }

            if (empty($addressData['city'])) {
                $this->jsonResponse(['success' => false, 'message' => 'City is required']);
                return;
            }

            // Update address
            $result = $this->shopProfileModel->updateAddress($shop_id, $addressData);

            if ($result) {
                $this->jsonResponse([
                    'success' => true, 
                    'message' => 'Address updated successfully'
                ]);
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to update address']);
            }

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload shop banner
     */
    public function uploadBanner() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        try {
            // Get shop data
            $shopData = $this->shopProfileModel->getShopProfileData($_SESSION['user_id']);
            
            if (!$shopData) {
                $this->jsonResponse(['success' => false, 'message' => 'Shop not found'], 404);
                return;
            }

            $shop_id = $shopData['shop_id'];

            // Check if file was uploaded
            if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
                $this->jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error']);
                return;
            }

            $file = $_FILES['banner'];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid file type. Only images are allowed']);
                return;
            }

            // Validate file size (max 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                $this->jsonResponse(['success' => false, 'message' => 'File size exceeds 5MB limit']);
                return;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/shop/banners/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'banner_' . $shop_id . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Delete old banner if exists
                if (!empty($shopData['shop_banner'])) {
                    $oldFile = $uploadDir . $shopData['shop_banner'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Update database
                $result = $this->shopProfileModel->updateBanner($shop_id, $filename);

                if ($result) {
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Banner uploaded successfully',
                        'filename' => $filename,
                        'url' => '/public/uploads/shop/banners/' . $filename
                    ]);
                } else {
                    // Delete uploaded file if database update fails
                    unlink($filepath);
                    $this->jsonResponse(['success' => false, 'message' => 'Failed to update database']);
                }
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to move uploaded file']);
            }

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload shop profile picture
     */
    public function uploadProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }

        try {
            // Get shop data
            $shopData = $this->shopProfileModel->getShopProfileData($_SESSION['user_id']);
            
            if (!$shopData) {
                $this->jsonResponse(['success' => false, 'message' => 'Shop not found'], 404);
                return;
            }

            $shop_id = $shopData['shop_id'];

            // Check if file was uploaded
            if (!isset($_FILES['profile']) || $_FILES['profile']['error'] !== UPLOAD_ERR_OK) {
                $this->jsonResponse(['success' => false, 'message' => 'No file uploaded or upload error']);
                return;
            }

            $file = $_FILES['profile'];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!in_array($file['type'], $allowedTypes)) {
                $this->jsonResponse(['success' => false, 'message' => 'Invalid file type. Only images are allowed']);
                return;
            }

            // Validate file size (max 3MB)
            if ($file['size'] > 3 * 1024 * 1024) {
                $this->jsonResponse(['success' => false, 'message' => 'File size exceeds 3MB limit']);
                return;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/public/uploads/shop/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Generate unique filename
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'profile_' . $shop_id . '_' . time() . '.' . $extension;
            $filepath = $uploadDir . $filename;

            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Delete old profile picture if exists
                if (!empty($shopData['shop_profile'])) {
                    $oldFile = $uploadDir . $shopData['shop_profile'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                // Update database
                $result = $this->shopProfileModel->updateProfilePicture($shop_id, $filename);

                if ($result) {
                    $this->jsonResponse([
                        'success' => true,
                        'message' => 'Profile picture uploaded successfully',
                        'filename' => $filename,
                        'url' => '/public/uploads/shop/profiles/' . $filename
                    ]);
                } else {
                    // Delete uploaded file if database update fails
                    unlink($filepath);
                    $this->jsonResponse(['success' => false, 'message' => 'Failed to update database']);
                }
            } else {
                $this->jsonResponse(['success' => false, 'message' => 'Failed to move uploaded file']);
            }

        } catch (\Exception $e) {
            $this->jsonResponse(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper method to send JSON response
     */
    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
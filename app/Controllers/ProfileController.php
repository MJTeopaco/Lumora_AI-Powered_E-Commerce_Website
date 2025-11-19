<?php
// app/Controllers/ProfileController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Helpers\ValidationHelper;
use App\Helpers\RedirectHelper;

class ProfileController extends Controller
{

    protected $userModel;
    protected $profileModel;
    protected $addressModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->addressModel = new Address();

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
            'pageTitle' => 'Personal Information'
        ];

        $this->view('profile/index', $data, 'profile');
    }

    /**
     * Handle profile update (unified endpoint for all fields)
     */
    public function update()
    {
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
                } else {
                    $this->redirectWithError('Please select a file to upload');
                    return;
                }
                $successMessage = 'Profile picture updated successfully!';
                break;

            default:
                $this->redirectWithError('Invalid update field');
                return;
        }

        // Update or create profile
        try {
            // Check if there is actually anything to update
            if (empty($data) && $updateField !== 'picture') {
                $this->redirectWithSuccess('No changes were made.');
                return;
            }

            if ($this->profileModel->exists($userId)) {
                // Profile exists, just update the single field with plain text data
                $success = $this->profileModel->update($userId, $data);
            } else {
                // Profile doesn't exist, create it with this new data
                
                // Build the data for create()
                $createData = $rawDataForCreate;
                
                if ($updateField === 'picture') {
                     $createData['profile_pic'] = $data['profile_pic'];
                }
                
                // Send plain text data to create()
                $success = $this->profileModel->create($userId, $createData);
            }

            if ($success) {
                $this->redirectWithSuccess($successMessage);
            } else {
                $this->redirectWithError('Failed to update profile. Please try again.');
            }
        } catch (\Exception $e) {
            $this->redirectWithError($e->getMessage());
        }
    }

    // ... (keep handleProfilePictureUpload, redirectWithError, redirectWithSuccess) ...

    private function handleProfilePictureUpload($file, $userId)
    {
        // Validate file
        $validation = ValidationHelper::validateProfilePicture($file);

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
        $addresses = $this->addressModel->getAddressesByUserId($userId);
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        if (!$profile) {
            $profile = ['profile_pic' => ''];
        }

        // Get addresses from database
        // These are now retrieved as plain text from the model
        $addresses = $this->addressModel->getAddressesByUserId($userId);

        // Check for status messages
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';

        $data = [
            'user' => $user,
            'profile' => $profile,
            'addresses' => $addresses,
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'addresses', // <--- IMPORTANT for sidebar highlight
            'pageTitle' => 'My Addresses'
        ];

        $this->view('profile/addresses', $data, 'profile');
    }

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
            'activeTab' => 'settings', // <--- IMPORTANT for sidebar highlight
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
            // $address variable is not set here for 'add' form
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'addresses',
            'pageTitle' => 'Add New Address'
        ];

        // FIX: Ensure the view is wrapped by the profile layout
        $this->view('profile/address-form', $data, 'profile');
    }


    public function changePassword()
    {
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
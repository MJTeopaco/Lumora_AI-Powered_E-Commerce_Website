<?php
// app/Controllers/AddressController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\Address;
use App\Helpers\ValidationHelper;
use App\Helpers\RedirectHelper;

class AddressController extends Controller
{
    protected $userModel;
    protected $profileModel;
    protected $addressModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->profileModel = new UserProfile();
        $this->addressModel = new Address();

        // Require authentication for all address actions
        if (!Session::has('user_id')) {
            RedirectHelper::redirect('/login');
        }
    }

    /**
     * Show all user addresses
     */
    public function index()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        
        // Get all addresses for this user
        $addresses = $this->addressModel->getAddressesByUserId_User($userId);
        
        // Get status message
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

    /**
     * Show add address form
     */
    public function add()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        
        // Get status message
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';
        
        $data = [
            'user' => $user,
            'profile' => $profile,
            'isEdit' => false,
            'address' => [],
            'regions' => $this->getRegions(),
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'addresses',
            'pageTitle' => 'Add New Address'
        ];
        
        $this->view('profile/address-form', $data, 'profile');
    }

    /**
     * Store new address
     */
    public function store()
    {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses');
        }
        
        $userId = Session::get('user_id');
        
        // Validate required fields
        $requiredFields = ['address_line_1', 'region', 'province', 'city', 'barangay'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $this->redirectToAddresses('Please fill in all required fields', 'error');
                return;
            }
        }
        
        // Prepare data
        $data = [
            'user_id' => $userId,
            'address_type' => 'shipping', // Default type
            'address_line_1' => ValidationHelper::sanitize($_POST['address_line_1']),
            'address_line_2' => ValidationHelper::sanitize($_POST['address_line_2'] ?? ''),
            'region' => ValidationHelper::sanitize($_POST['region']),
            'province' => ValidationHelper::sanitize($_POST['province']),
            'city' => ValidationHelper::sanitize($_POST['city']),
            'barangay' => ValidationHelper::sanitize($_POST['barangay']),
            'postal_code' => ValidationHelper::sanitize($_POST['postal_code'] ?? ''),
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];
        
        // If this is set as default, unset other defaults first
        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }
        
        // Create address
        if ($this->addressModel->createAddress($data)) {
            $this->redirectToAddresses('Address added successfully', 'success');
        } else {
            $this->redirectToAddresses('Failed to add address', 'error');
        }
    }

    /**
     * Show edit address form
     * FIXED: Accepts $id from route parameter
     */
    public function edit($id = null)
    {
        // Use ID from URL path or query param as fallback
        $addressId = $id ?? $_GET['id'] ?? null;
        
        if (!$addressId) {
            $this->redirectToAddresses('Invalid address ID', 'error');
            return;
        }
        
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->profileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        
        // Get address and verify ownership
        $address = $this->addressModel->getAddressById($addressId, $userId);
        
        if (!$address) {
            $this->redirectToAddresses('Address not found', 'error');
            return;
        }
        
        // Get status message
        $statusMessage = isset($_GET['message']) ? urldecode($_GET['message']) : null;
        $statusType = $_GET['status'] ?? 'success';
        
        $data = [
            'user' => $user,
            'profile' => $profile,
            'isEdit' => true,
            'address' => $address,
            'regions' => $this->getRegions(),
            'statusMessage' => $statusMessage,
            'statusType' => $statusType,
            'activeTab' => 'addresses',
            'pageTitle' => 'Edit Address'
        ];
        
        $this->view('profile/address-form', $data, 'profile');
    }

    /**
     * Update existing address
     */
    public function update()
    {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses');
        }
        
        // Get address ID from POST
        $addressId = $_POST['address_id'] ?? null;
        
        if (!$addressId) {
            $this->redirectToAddresses('Invalid address', 'error');
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Verify ownership
        $existingAddress = $this->addressModel->getAddressById($addressId, $userId);
        if (!$existingAddress) {
            $this->redirectToAddresses('Address not found', 'error');
            return;
        }
        
        // Validate required fields
        $requiredFields = ['address_line_1', 'region', 'province', 'city', 'barangay'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                RedirectHelper::redirect('/profile/addresses/edit/' . $addressId . '?status=error&message=' . urlencode('Please fill in all required fields'));
                return;
            }
        }
        
        // Prepare data
        $data = [
            'address_line_1' => ValidationHelper::sanitize($_POST['address_line_1']),
            'address_line_2' => ValidationHelper::sanitize($_POST['address_line_2'] ?? ''),
            'region' => ValidationHelper::sanitize($_POST['region']),
            'province' => ValidationHelper::sanitize($_POST['province']),
            'city' => ValidationHelper::sanitize($_POST['city']),
            'barangay' => ValidationHelper::sanitize($_POST['barangay']),
            'postal_code' => ValidationHelper::sanitize($_POST['postal_code'] ?? ''),
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];
        
        // If this is set as default, unset other defaults first
        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId, $addressId);
        }
        
        // Update address
        if ($this->addressModel->updateAddress($addressId, $userId, $data)) {
            $this->redirectToAddresses('Address updated successfully', 'success');
        } else {
            RedirectHelper::redirect('/profile/addresses/edit/' . $addressId . '?status=error&message=' . urlencode('Failed to update address'));
        }
    }

    /**
     * Delete address
     */
    public function delete()
    {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses');
        }
        
        $addressId = $_POST['address_id'] ?? null;
        
        if (!$addressId) {
            $this->redirectToAddresses('Invalid address', 'error');
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Verify ownership
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $this->redirectToAddresses('Address not found', 'error');
            return;
        }
        
        // Check if user has other addresses
        $userAddresses = $this->addressModel->getAddressesByUserId_User($userId);
        if (count($userAddresses) <= 1) {
            $this->redirectToAddresses('Cannot delete your only address', 'error');
            return;
        }
        
        // Delete address
        if ($this->addressModel->deleteAddress($addressId, $userId)) {
            $this->redirectToAddresses('Address deleted successfully', 'success');
        } else {
            $this->redirectToAddresses('Failed to delete address', 'error');
        }
    }

    /**
     * Set address as default
     */
    public function setDefault()
    {
        $this->verifyCsrfToken();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses');
        }
        
        $addressId = $_POST['address_id'] ?? null;
        
        if (!$addressId) {
            $this->redirectToAddresses('Invalid address', 'error');
            return;
        }
        
        $userId = Session::get('user_id');
        
        // Verify ownership
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $this->redirectToAddresses('Address not found', 'error');
            return;
        }
        
        // Unset all defaults for this user
        $this->addressModel->unsetDefaultAddresses($userId);
        
        // Set this address as default
        if ($this->addressModel->setAsDefault($addressId, $userId)) {
            $this->redirectToAddresses('Default address updated successfully', 'success');
        } else {
            $this->redirectToAddresses('Failed to update default address', 'error');
        }
    }

    /**
     * Helper to redirect to addresses page with message
     */
    private function redirectToAddresses($message, $status = 'success')
    {
        $params = [
            'status' => $status,
            'message' => urlencode($message)
        ];
        $url = '/profile/addresses?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }

    /**
     * Get list of Philippine regions
     */
    private function getRegions()
    {
        return [
            'NCR' => 'National Capital Region',
            'CAR' => 'Cordillera Administrative Region',
            'Region I' => 'Region I (Ilocos Region)',
            'Region II' => 'Region II (Cagayan Valley)',
            'Region III' => 'Region III (Central Luzon)',
            'Region IV-A' => 'Region IV-A (CALABARZON)',
            'Region IV-B' => 'Region IV-B (MIMAROPA)',
            'Region V' => 'Region V (Bicol Region)',
            'Region VI' => 'Region VI (Western Visayas)',
            'Region VII' => 'Region VII (Central Visayas)',
            'Region VIII' => 'Region VIII (Eastern Visayas)',
            'Region IX' => 'Region IX (Zamboanga Peninsula)',
            'Region X' => 'Region X (Northern Mindanao)',
            'Region XI' => 'Region XI (Davao Region)',
            'Region XII' => 'Region XII (SOCCSKSARGEN)',
            'Region XIII' => 'Region XIII (Caraga)',
            'BARMM' => 'Bangsamoro Autonomous Region in Muslim Mindanao'
        ];
    }
}
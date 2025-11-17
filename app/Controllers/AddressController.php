<?php
// app/Controllers/AddressController.php

namespace App\Controllers;

use App\Models\Address;
use App\Models\UserProfile;

class AddressController
{
    private $addressModel;
    private $userProfileModel;

    public function __construct()
    {
        $this->addressModel = new Address();
        // Only initialize UserProfile if the class exists
        if (class_exists('App\Models\UserProfile')) {
            $this->userProfileModel = new UserProfile();
        }
    }

    /**
     * Display all addresses for the logged-in user
     */
    public function index()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get user data
        $user = $this->getUserData($userId);
        
        // Get user profile (if available)
        $profile = $this->getUserProfile($userId);

        // Get all addresses for the user
        $addresses = $this->addressModel->getAddressesByUserId($userId);

        // Get status message from session if exists
        $statusMessage = $_SESSION['status_message'] ?? null;
        $statusType = $_SESSION['status_type'] ?? null;
        unset($_SESSION['status_message'], $_SESSION['status_type']);

        // Load the view
        require_once __DIR__ . '/../Views/layouts/profile/addresses.view.php';
    }

    /**
     * Show add address form
     */
    public function add()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->getUserData($userId);
        $profile = $this->getUserProfile($userId);

        $isEdit = false;

        // Get status message from session if exists
        $statusMessage = $_SESSION['status_message'] ?? null;
        $statusType = $_SESSION['status_type'] ?? null;
        unset($_SESSION['status_message'], $_SESSION['status_type']);

        // Load the add form view
        require_once __DIR__ . '/../Views/layouts/profile/address-form.view.php';
    }

    /**
     * Handle add address form submission
     */
    public function store()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile/addresses/add');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Get form data
        $data = [
            'user_id' => $userId,
            'address_line_1' => trim($_POST['address_line_1'] ?? ''),
            'address_line_2' => trim($_POST['address_line_2'] ?? ''),
            'barangay' => trim($_POST['barangay'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'region' => trim($_POST['region'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['address_line_1']) || empty($data['barangay']) || 
            empty($data['city']) || empty($data['province']) || empty($data['region'])) {
            $_SESSION['status_message'] = 'Please fill in all required fields.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses/add');
            exit;
        }

        // If this address is set as default, unset other defaults
        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }

        // Create the address
        $result = $this->addressModel->createAddress($data);

        if ($result) {
            $_SESSION['status_message'] = 'Address added successfully!';
            $_SESSION['status_type'] = 'success';
            header('Location: /profile/addresses');
        } else {
            $_SESSION['status_message'] = 'Failed to add address. Please try again.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses/add');
        }
        exit;
    }

    /**
     * Show edit address form
     */
    public function edit($addressId)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->getUserData($userId);
        $profile = $this->getUserProfile($userId);

        // Get the address
        $address = $this->addressModel->getAddressById($addressId, $userId);

        if (!$address) {
            $_SESSION['status_message'] = 'Address not found.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses');
            exit;
        }

        $isEdit = true;

        // Get status message from session if exists
        $statusMessage = $_SESSION['status_message'] ?? null;
        $statusType = $_SESSION['status_type'] ?? null;
        unset($_SESSION['status_message'], $_SESSION['status_type']);

        // Load the edit form view
        require_once __DIR__ . '/../Views/layouts/profile/address-form.view.php';
    }

    /**
     * Handle edit address form submission
     */
    public function update($addressId)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile/addresses/edit/' . $addressId);
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Verify the address belongs to the user
        $existingAddress = $this->addressModel->getAddressById($addressId, $userId);
        if (!$existingAddress) {
            $_SESSION['status_message'] = 'Address not found.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses');
            exit;
        }

        // Get form data
        $data = [
            'address_line_1' => trim($_POST['address_line_1'] ?? ''),
            'address_line_2' => trim($_POST['address_line_2'] ?? ''),
            'barangay' => trim($_POST['barangay'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'province' => trim($_POST['province'] ?? ''),
            'region' => trim($_POST['region'] ?? ''),
            'postal_code' => trim($_POST['postal_code'] ?? ''),
            'is_default' => isset($_POST['is_default']) ? 1 : 0
        ];

        // Validate required fields
        if (empty($data['address_line_1']) || empty($data['barangay']) || 
            empty($data['city']) || empty($data['province']) || empty($data['region'])) {
            $_SESSION['status_message'] = 'Please fill in all required fields.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses/edit/' . $addressId);
            exit;
        }

        // If this address is set as default, unset other defaults
        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }

        // Update the address
        $result = $this->addressModel->updateAddress($addressId, $userId, $data);

        if ($result) {
            $_SESSION['status_message'] = 'Address updated successfully!';
            $_SESSION['status_type'] = 'success';
            header('Location: /profile/addresses');
        } else {
            $_SESSION['status_message'] = 'Failed to update address. Please try again.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses/edit/' . $addressId);
        }
        exit;
    }

    /**
     * Delete an address
     */
    public function delete($addressId)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile/addresses');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Verify the address belongs to the user
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $_SESSION['status_message'] = 'Address not found.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses');
            exit;
        }

        // Delete the address
        $result = $this->addressModel->deleteAddress($addressId, $userId);

        if ($result) {
            $_SESSION['status_message'] = 'Address deleted successfully!';
            $_SESSION['status_type'] = 'success';
        } else {
            $_SESSION['status_message'] = 'Failed to delete address. Please try again.';
            $_SESSION['status_type'] = 'error';
        }

        header('Location: /profile/addresses');
        exit;
    }

    /**
     * Set an address as default
     */
    public function setDefault($addressId)
    {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /profile/addresses');
            exit;
        }

        $userId = $_SESSION['user_id'];

        // Verify the address belongs to the user
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $_SESSION['status_message'] = 'Address not found.';
            $_SESSION['status_type'] = 'error';
            header('Location: /profile/addresses');
            exit;
        }

        // Unset all default addresses for this user
        $this->addressModel->unsetDefaultAddresses($userId);

        // Set this address as default
        $result = $this->addressModel->setAsDefault($addressId, $userId);

        if ($result) {
            $_SESSION['status_message'] = 'Default address updated successfully!';
            $_SESSION['status_type'] = 'success';
        } else {
            $_SESSION['status_message'] = 'Failed to set default address. Please try again.';
            $_SESSION['status_type'] = 'error';
        }

        header('Location: /profile/addresses');
        exit;
    }

    /**
     * Helper method to get user data
     */
    private function getUserData($userId)
    {
        // You'll need to implement this based on your User model
        // For now, returning basic session data
        return [
            'user_id' => $userId,
            'username' => $_SESSION['username'] ?? 'User',
            'email' => $_SESSION['email'] ?? ''
        ];
    }

    /**
     * Helper method to get user profile
     */
    private function getUserProfile($userId)
    {
        // FIXED: Changed 'getProfileByUserId' to 'getByUserId'
        if ($this->userProfileModel && method_exists($this->userProfileModel, 'getByUserId')) {
            return $this->userProfileModel->getByUserId($userId);
        }
        
        // Return empty profile if no model available
        return [
            'profile_pic' => '',
            'full_name' => '',
            'phone_number' => '',
            'gender' => '',
            'birth_date' => ''
        ];
    }
}
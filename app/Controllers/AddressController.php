<?php
// app/Controllers/AddressController.php

namespace App\Controllers;

use App\Core\Controller;  
use App\Core\Session;     
use App\Models\Address;
use App\Models\User;      
use App\Models\UserProfile;
use App\Helpers\RedirectHelper;

class AddressController extends Controller  
{
    private $addressModel;
    private $userProfileModel;
    private $userModel;  

    public function __construct()
    {
        
        if (!Session::has('user_id')) {
            RedirectHelper::redirect('/login');
        }

        $this->addressModel = new Address();
        $this->userModel = new User();
        $this->userProfileModel = new UserProfile();
    }

    /**
     * Display all addresses for the logged-in user
     */
    public function index()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->userProfileModel->getByUserId($userId) ?: ['profile_pic' => ''];
        $addresses = $this->addressModel->getAddressesByUserId_User($userId);

        $data = [
            'user' => $user,
            'profile' => $profile,
            'addresses' => $addresses,
            'statusMessage' => $_GET['message'] ?? null,
            'statusType' => $_GET['status'] ?? 'success',
            'activeTab' => 'addresses',
            'pageTitle' => 'My Addresses'
        ];

        
        $this->view('profile/addresses', $data, '/profile');
    }

    /**
     * Show add address form
     */
    public function add()
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->userProfileModel->getByUserId($userId) ?: ['profile_pic' => ''];

        $data = [
            'user' => $user,
            'profile' => $profile,
            'isEdit' => false,
            'statusMessage' => $_GET['message'] ?? null,
            'statusType' => $_GET['status'] ?? 'success',
            'activeTab' => 'addresses',
            'pageTitle' => 'Add New Address'
        ];

        
        $this->view('profile/address-form', $data, '/profile');
    }

    /**
     * Handle add address form submission
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('profile/addresses/add');
        }

        $userId = Session::get('user_id');

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
            $this->redirectWithError('Please fill in all required fields.');
            return;
        }

        // If this address is set as default, unset other defaults
        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }

        // Create the address
        $result = $this->addressModel->createAddress($data);

        if ($result) {
            $this->redirectWithSuccess('Address added successfully!');
        } else {
            $this->redirectWithError('Failed to add address. Please try again.');
        }
    }

    /**
     * Show edit address form
     */
    public function edit($addressId)
    {
        $userId = Session::get('user_id');
        $user = $this->userModel->findById($userId);
        $profile = $this->userProfileModel->getByUserId($userId) ?: ['profile_pic' => ''];

        // Get the address
        $address = $this->addressModel->getAddressById($addressId, $userId);

        if (!$address) {
            $this->redirectWithError('Address not found.');
            return;
        }

        $data = [
            'user' => $user,
            'profile' => $profile,
            'address' => $address,
            'isEdit' => true,
            'statusMessage' => $_GET['message'] ?? null,
            'statusType' => $_GET['status'] ?? 'success',
            'activeTab' => 'addresses',
            'pageTitle' => 'Edit Address'
        ];

        
        $this->view('profile/address-form', $data, '/profile');
    }

    /**
     * Handle edit address form submission
     */
    public function update($addressId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('profile/addresses/edit/' . $addressId);
        }

        $userId = Session::get('user_id');

        // Verify the address belongs to the user
        $existingAddress = $this->addressModel->getAddressById($addressId, $userId);
        if (!$existingAddress) {
            $this->redirectWithError('Address not found.');
            return;
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
            $this->redirectWithError('Please fill in all required fields.');
            return;
        }

        // If this address is set as default, unset other defaults
        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }

        // Update the address
        $result = $this->addressModel->updateAddress($addressId, $userId, $data);

        if ($result) {
            $this->redirectWithSuccess('Address updated successfully!');
        } else {
            $this->redirectWithError('Failed to update address. Please try again.');
        }
    }

    /**
     * Delete an address
     */
    public function delete($addressId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('profile/addresses');
        }

        $userId = Session::get('user_id');

        // Verify the address belongs to the user
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $this->redirectWithError('Address not found.');
            return;
        }

        // Delete the address
        $result = $this->addressModel->deleteAddress($addressId, $userId);

        if ($result) {
            $this->redirectWithSuccess('Address deleted successfully!');
        } else {
            $this->redirectWithError('Failed to delete address. Please try again.');
        }
    }

    /**
     * Set an address as default
     */
    public function setDefault($addressId)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('profile/addresses');
        }

        $userId = Session::get('user_id');

        // Verify the address belongs to the user
        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $this->redirectWithError('Address not found.');
            return;
        }

        // Unset all default addresses for this user
        $this->addressModel->unsetDefaultAddresses($userId);

        // Set this address as default
        $result = $this->addressModel->setAsDefault($addressId, $userId);

        if ($result) {
            $this->redirectWithSuccess('Default address updated successfully!');
        } else {
            $this->redirectWithError('Failed to set default address. Please try again.');
        }
    }

    
    private function redirectWithError($message)
    {
        $params = [
            'status' => 'error',
            'message' => urlencode($message)
        ];
        $url = 'profile/addresses?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }

    private function redirectWithSuccess($message)
    {
        $params = [
            'status' => 'success',
            'message' => urlencode($message)
        ];
        $url = 'profile/addresses?' . http_build_query($params);
        RedirectHelper::redirect($url);
    }
}
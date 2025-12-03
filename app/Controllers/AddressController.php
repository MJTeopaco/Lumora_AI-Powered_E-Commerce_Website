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
            'pageTitle' => 'Add New Address',
            'regions' => $this->getRegions() // Added missing regions
        ];

        $this->view('profile/address-form', $data, '/profile');
    }

    /**
     * Handle add address form submission
     */
    public function store()
    {
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses/add'); // Fixed: Added leading slash
        }

        $userId = Session::get('user_id');

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

        if (empty($data['address_line_1']) || empty($data['barangay']) || 
            empty($data['city']) || empty($data['province']) || empty($data['region'])) {
            $this->redirectWithError('Please fill in all required fields.');
            return;
        }

        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }

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
            'pageTitle' => 'Edit Address',
            'regions' => $this->getRegions() // Added missing regions
        ];

        $this->view('profile/address-form', $data, '/profile');
    }

    /**
     * Handle edit address form submission
     */
    public function update($addressId)
    {
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses/edit/' . $addressId); // Fixed: Added leading slash
        }

        $userId = Session::get('user_id');

        $existingAddress = $this->addressModel->getAddressById($addressId, $userId);
        if (!$existingAddress) {
            $this->redirectWithError('Address not found.');
            return;
        }

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

        if (empty($data['address_line_1']) || empty($data['barangay']) || 
            empty($data['city']) || empty($data['province']) || empty($data['region'])) {
            $this->redirectWithError('Please fill in all required fields.');
            return;
        }

        if ($data['is_default']) {
            $this->addressModel->unsetDefaultAddresses($userId);
        }

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
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses'); // Fixed: Added leading slash
        }

        $userId = Session::get('user_id');

        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $this->redirectWithError('Address not found.');
            return;
        }

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
        $this->verifyCsrfToken();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/profile/addresses'); // Fixed: Added leading slash
        }

        $userId = Session::get('user_id');

        $address = $this->addressModel->getAddressById($addressId, $userId);
        if (!$address) {
            $this->redirectWithError('Address not found.');
            return;
        }

        $this->addressModel->unsetDefaultAddresses($userId);
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
        $url = '/profile/addresses?' . http_build_query($params); // Fixed: Added leading slash
        RedirectHelper::redirect($url);
    }

    private function redirectWithSuccess($message)
    {
        $params = [
            'status' => 'success',
            'message' => urlencode($message)
        ];
        $url = '/profile/addresses?' . http_build_query($params); // Fixed: Added leading slash
        RedirectHelper::redirect($url);
    }

    /**
     * Get list of regions in Philippines
     */
    private function getRegions() {
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
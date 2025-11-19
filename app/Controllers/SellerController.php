<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Seller;
use App\Models\User;


class SellerController extends Controller {

    protected $sellerModel;
    protected $userModel;

    public function __construct() {
        $this->sellerModel = new Seller(); 
        $this->userModel = new User;
    }

    # Seller Registration Controller
    public function registerForm() {
        $data = [

        ];

        $this->view('seller/register', $data);
    }

    public function registerSubmit() {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'You must be logged in to register as a seller';
        header('Location: /login');
        exit();
    }

    // Check if user is already a seller
    $userId = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($_POST['shop_name']) || empty($_POST['contact_email']) || 
        empty($_POST['contact_phone']) || empty($_POST['address_line_1']) || 
        empty($_POST['barangay']) || empty($_POST['city']) || 
        empty($_POST['province']) || empty($_POST['region']) || 
        empty($_POST['postal_code'])) {
        
        $_SESSION['error'] = 'Please fill in all required fields';
        header('Location: /seller/register');
        exit();
    }

    // Validate terms checkbox
    if (!isset($_POST['terms'])) {
        $_SESSION['error'] = 'You must agree to the Terms and Conditions';
        header('Location: /seller/register');
        exit();
    }

    // Sanitize inputs
    $shopName = trim($_POST['shop_name']);
    $contactEmail = trim($_POST['contact_email']);
    $contactPhone = trim($_POST['contact_phone']);
    $shopDesc = trim($_POST['shop_description'] ?? '');
    $addressLine1 = trim($_POST['address_line_1']);
    $addressLine2 = trim($_POST['address_line_2'] ?? '');
    $barangay = trim($_POST['barangay']);
    $city = trim($_POST['city']);
    $province = trim($_POST['province']);
    $region = trim($_POST['region']);
    $postalCode = trim($_POST['postal_code']);

    // Validate email format
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Invalid email format';
        header('Location: /seller/register');
        exit();
    }

    // Validate postal code format (4 digits)
    if (!preg_match('/^\d{4}$/', $postalCode)) {
        $_SESSION['error'] = 'Postal code must be 4 digits';
        header('Location: /seller/register');
        exit();
    }

    // Generate slug
    $slug = strtolower(str_replace(' ', '-', $shopName));

    // Save shop information
    $shopSaved = $this->sellerModel->saveRegister(
        $userId, 
        $shopName, 
        $contactEmail, 
        $contactPhone, 
        $shopDesc, 
        $slug
    );

    if (!$shopSaved) {
        $_SESSION['error'] = 'Failed to register shop. Please try again.';
        header('Location: /seller/register');
        exit();
    }

    // Save shop address (using user_id and address_type='shop')
    $addressSaved = $this->sellerModel->saveShopAddress(
        $userId,
        $addressLine1,
        $addressLine2,
        $barangay,
        $city,
        $province,
        $region,
        $postalCode
    );

    if (!$addressSaved) {
        $_SESSION['error'] = 'Shop created but failed to save address';
        header('Location: /seller/register');
        exit();
    }

    // Request seller role approval
    $approvalRequested = $this->sellerModel->requestApproval($userId);

    if (!$approvalRequested) {
        $_SESSION['error'] = 'Shop created but failed to request approval';
        header('Location: /seller/register');
        exit();
    }

    // Success - set session message for popup
    $_SESSION['seller_registration_success'] = 'Your seller application has been submitted successfully! Our team will review your application within 24-48 hours. You will receive a confirmation email once approved.';
    
    header('Location: /');
    exit();
}

}

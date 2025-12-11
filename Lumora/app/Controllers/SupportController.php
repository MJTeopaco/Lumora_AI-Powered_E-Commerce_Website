<?php
// app/Controllers/SupportController.php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Session;
use App\Helpers\RedirectHelper;
use App\Models\SupportTicket;

class SupportController extends Controller {

    public function submit() {
        // Simple validation
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            RedirectHelper::redirect('/login');
        }

        // Verify CSRF (assuming you have the token in the form)
        $this->verifyCsrfToken();

        $identifier = trim($_POST['identifier'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $subject = "Account Lockout Assistance"; // Default subject for this specific form

        if (empty($identifier) || empty($message)) {
            RedirectHelper::redirectWithError('Please provide your email/username and a message.', 'login', 'support');
        }

        $ticketModel = new SupportTicket();
        if ($ticketModel->create($identifier, $subject, $message)) {
            RedirectHelper::redirectWithSuccess('Support request sent! Admin will review it shortly.', 'login', 'credentials');
        } else {
            RedirectHelper::redirectWithError('Failed to send request. Please try again.', 'login', 'credentials');
        }
    }
}
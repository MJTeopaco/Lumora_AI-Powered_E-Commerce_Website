<?php 
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Admin;       

class AdminController extends Controller {

    protected $adminModel;

    public function __construct() {
        $this->adminModel = $this->model('Admin');
    }

    public function dashboard() {
        // Fetch necessary data for the dashboard
        $data = [
            'total_users' => $this->adminModel->getTotalUsers(), 
            'total_buyers' => $this->adminModel->getTotalBuyers(),
            'total_sellers' => $this->adminModel->getTotalSellers(),
            'total_admins' => $this->adminModel->getTotalAdmins(),
        ];
        
        // Render the admin dashboard view
        $this->view('admin/dashboard', $data);
    }
}






?>
<?php 
namespace App\Controllers;
use App\Core\Controller;
use App\Models\Admin;       
use App\Helpers\Category;

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
        $this->view('admin/index', $data);
    }


    // category

    public function settings() {
        $data = [
            'categories' => $this->adminModel->getAllCategories()
        ];

        $this->view('admin/settings', $data);
    }


    public function addCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['category_name'] ?? '');

            if (empty($name)) {
                $_SESSION['error'] = 'Category name is required';
                header('Location: /admin/settings');
                exit;
            }

            $slug = $this->generateSlug($name);

            if ($this->adminModel->addCategory($name, $slug)) {
                $_SESSION['success'] = 'Category added successfully';
            } else {
                $_SESSION['error'] = 'Failed to add category';
            }

            header('Location: /admin/settings');
            exit;
        }
    }

    public function updateCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_id = (int)$_POST['category_id'];
            $name = trim($_POST['category_name'] ?? '');

            if (empty($name)) {
                $_SESSION['error'] = 'Category name is required';
                header('Location: /admin/settings');
                exit;
            }

            $slug = $this->generateSlug($name);

            if ($this->adminModel->updateCategory($category_id, $name, $slug)) {
                $_SESSION['success'] = 'Category updated successfully';
            } else {
                $_SESSION['error'] = 'Failed to update category';
            }

            header('Location: /admin/settings');
            exit;
        }
    }


    public function deleteCategory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category_id = (int)$_POST['category_id'];

            if ($this->adminModel->deleteCategory($category_id)) {
                $_SESSION['success'] = 'Category deleted successfully';
            } else {
                $_SESSION['error'] = 'Cannot delete category because it is assigned to products.';
            }

            header('Location: /admin/settings');
            exit;
        }
    }


    private function generateSlug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }

}






?>
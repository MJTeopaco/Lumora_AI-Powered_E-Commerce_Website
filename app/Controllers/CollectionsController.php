<?php
// app/Controllers/CollectionsController.php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Controller;
use App\Models\Collections;
use App\Models\User;

class CollectionsController extends Controller {

    protected $collectionsModel;
    protected $userModel;

    public function __construct() {
        $this->collectionsModel = new Collections();
        $this->userModel = new User();
    }
    
    // Display the collections index page
    public function index() {
        // Get all categories
        $categories = $this->collectionsModel->getAllCategories();
        
        // Get all products (default view)
        $products = $this->collectionsModel->getAllProducts(24);
        
        // Pass data to view
        $data = [
            'categories' => $categories,
            'products' => $products,
            'selectedCategory' => 'all',
            'pageTitle' => 'Collections - Lumora',
            'pageStyle' => 'collections', // Load collections.css
            'pageScript' => 'collections' // Load collections.js
        ];
        
        $this->view('collections/index', $data);
    }
    
    // Get products by category (AJAX endpoint)
    public function getByCategory() {
        header('Content-Type: application/json');
        
        if (!isset($_GET['category_id'])) {
            echo json_encode(['error' => 'Category ID is required']);
            return;
        }
        
        $categoryId = $_GET['category_id'];
        
        if ($categoryId === 'all') {
            $products = $this->collectionsModel->getAllProducts(24);
        } else {
            $products = $this->collectionsModel->getProductsByCategory($categoryId);
        }
        
        echo json_encode(['products' => $products]);
    }

}
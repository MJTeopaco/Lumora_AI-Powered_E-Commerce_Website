<?php
// app/Controllers/HomeController.php

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
        $this->view('collections/index');
    }
    

}
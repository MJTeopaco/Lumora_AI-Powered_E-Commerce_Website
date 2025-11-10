<?php
namespace app\Controllers;

class HomeController extends \app\Core\Controller {
    public function __construct() {
        parent::__construct();
        echo "Home Controller Loaded";
    }

    function index() {
        $data = ['title' => 'Home Page', 'content' => 'Welcome to the Home Page!'];
        $this->view('home/index', $data);
    }






}

$home = new HomeController();
call_user_func([$home, 'index']);

?>
<?php

namespace app\Controllers;

class _404 extends \app\Core\Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->view('app/Views/errors/_404.view');
    }
}


?>

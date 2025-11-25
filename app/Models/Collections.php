<?php
// app/Models/Collections.php

namespace App\Models;

use App\Core\Database;

class Collections {
    
    protected $conn;

    public function __construct() {
        // FIX: Use the static getConnection() method for mysqli
        $this->conn = Database::getConnection();
    }

    public function getConnection() {
        return $this->conn;
    }


}
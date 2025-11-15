<?php 

namespace App\Models;


use App\Core\Database;

Class Admin {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function getTotalUsers() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM users");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    public function getTotalBuyers() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM user_roles WHERE role_id = 1");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    public function getTotalSellers() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM user_roles WHERE role_id = 2");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

    public function getTotalAdmins() {
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM user_roles WHERE role_id = 3");
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();
        return $data['total'];
    }

}
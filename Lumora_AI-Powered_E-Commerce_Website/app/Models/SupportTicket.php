<?php
// app/Models/SupportTicket.php

namespace App\Models;

use App\Core\Database;

class SupportTicket {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function create($identifier, $subject, $message) {
        $stmt = $this->conn->prepare("INSERT INTO support_tickets (user_identifier, subject, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $identifier, $subject, $message);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }

    public function getAll() {
        $result = $this->conn->query("SELECT * FROM support_tickets ORDER BY created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getOpenCount() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM support_tickets WHERE status = 'OPEN'");
        $data = $result->fetch_assoc();
        return $data['count'];
    }

    public function markAsResolved($id) {
        $stmt = $this->conn->prepare("UPDATE support_tickets SET status = 'RESOLVED' WHERE ticket_id = ?");
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
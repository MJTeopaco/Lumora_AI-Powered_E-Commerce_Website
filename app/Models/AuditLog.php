<?php
namespace App\Models;

use App\Core\Database;

class AuditLog {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Create a new tamper-evident audit log
     */
    public function log($userId, $actionType, $details = [], $targetUserId = null) {
        // 1. Get the hash of the last inserted log
        $lastLog = $this->getLastLog();
        $prevHash = $lastLog ? $lastLog['hash'] : 'GENESIS_HASH';

        // 2. Prepare data
        $detailsJson = json_encode($details);
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $timestamp = date('Y-m-d H:i:s');

        // 3. Generate current hash (SHA-256)
        // Hash = SHA256(prevHash + userId + action + timestamp + details)
        $dataString = $prevHash . $userId . $actionType . $timestamp . $detailsJson;
        $currentHash = hash('sha256', $dataString);

        // 4. Insert Record
        $stmt = $this->conn->prepare("
            INSERT INTO user_audit_logs 
            (user_id, target_user_id, action_type, details, ip_address, user_agent, previous_hash, hash, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iisssssss", 
            $userId, 
            $targetUserId, 
            $actionType, 
            $detailsJson, 
            $ipAddress, 
            $userAgent, 
            $prevHash, 
            $currentHash,
            $timestamp
        );
        
        return $stmt->execute();
    }

    /**
     * Get recent logs with integrity check status
     */
    public function getLogs($limit = 50, $offset = 0, $filters = []) {
        $sql = "SELECT l.*, u.username as user_name, t.username as target_name 
                FROM user_audit_logs l 
                LEFT JOIN users u ON l.user_id = u.user_id 
                LEFT JOIN users t ON l.target_user_id = t.user_id 
                WHERE 1=1";
        
        $params = [];
        $types = "";

        if (!empty($filters['user_id'])) {
            $sql .= " AND l.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['action_type'])) {
            $sql .= " AND l.action_type = ?";
            $params[] = $filters['action_type'];
            $types .= "s";
        }

        $sql .= " ORDER BY l.log_id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Verify the integrity of the entire log chain
     * Returns true if chain is valid, or the ID of the first compromised log
     */
    public function verifyChainIntegrity() {
        $stmt = $this->conn->prepare("SELECT * FROM user_audit_logs ORDER BY log_id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        
        $prevHash = 'GENESIS_HASH';
        
        while ($row = $result->fetch_assoc()) {
            // Re-calculate hash
            $dataString = $prevHash . $row['user_id'] . $row['action_type'] . $row['created_at'] . $row['details'];
            $calculatedHash = hash('sha256', $dataString);

            // Check if matches stored hash
            if ($calculatedHash !== $row['hash']) {
                return ['status' => 'COMPROMISED', 'log_id' => $row['log_id']];
            }
            
            // Check if previous hash links correctly
            if ($row['previous_hash'] !== $prevHash) {
                return ['status' => 'BROKEN_CHAIN', 'log_id' => $row['log_id']];
            }

            $prevHash = $row['hash'];
        }

        return ['status' => 'VALID'];
    }

    private function getLastLog() {
        $result = $this->conn->query("SELECT hash FROM user_audit_logs ORDER BY log_id DESC LIMIT 1");
        return $result->fetch_assoc();
    }
    
    public function getTotalLogs() {
        $result = $this->conn->query("SELECT COUNT(*) as count FROM user_audit_logs");
        $data = $result->fetch_assoc();
        return $data['count'];
    }
}
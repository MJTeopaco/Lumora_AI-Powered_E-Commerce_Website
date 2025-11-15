<?php
// app/Models/User.php

namespace App\Models;

use App\Core\Database;
use DateTime;

class User {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function findByIdentifier($identifier) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function findByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }
    
    public function getUserRoles($user_id) {
        $stmt = $this->conn->prepare("SELECT r.name FROM roles r JOIN user_roles ur ON r.role_id = ur.role_id WHERE ur.user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row['name'];
        }
        $stmt->close();
        return $roles;
    }

    public function findByUsername($username) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function findById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return $user;
    }

    public function resetLoginAttempts($user_id) {
        $stmt = $this->conn->prepare("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
    }

    public function incrementLoginAttempts($user_id, $new_attempts) {
        define('MAX_LOGIN_ATTEMPTS', 3);
        define('LOCKOUT_TIME_MINUTES', 15);

        if ($new_attempts >= MAX_LOGIN_ATTEMPTS) {
            $lockout_expiry = (new DateTime())->add(new \DateInterval('PT' . LOCKOUT_TIME_MINUTES . 'M'))->format('Y-m-d H:i:s');
            $stmt = $this->conn->prepare("UPDATE users SET failed_login_attempts = ?, lockout_until = ? WHERE user_id = ?");
            $stmt->bind_param("isi", $new_attempts, $lockout_expiry, $user_id);
            $stmt->execute();
            $stmt->close();
            return true; // Account is locked
        } else {
            $stmt = $this->conn->prepare("UPDATE users SET failed_login_attempts = ? WHERE user_id = ?");
            $stmt->bind_param("ii", $new_attempts, $user_id);
            $stmt->execute();
            $stmt->close();
            return false; // Account not locked
        }
    }

// app/Models/User.php

    public function create($username, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        $user_result = $stmt->execute();
        $stmt->close();

        if ($user_result) {
            $new_user_id = $this->conn->insert_id;
            $buyer_role_id = 1; 
            $stmt_role = $this->conn->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $stmt_role->bind_param("ii", $new_user_id, $buyer_role_id);
            $role_result = $stmt_role->execute();
            $stmt_role->close();
            return $role_result;
        }
        
        return false;
    }

    public function updatePassword($user_id, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}
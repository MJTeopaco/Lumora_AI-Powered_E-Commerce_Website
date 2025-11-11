<?php
// app/Models/RememberMeToken.php

namespace App\Models;

use App\Core\Database;
use App\Core\Session;
use DateTime;
use Exception;

class RememberMeToken {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    public function create($user_id) {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));
        $token_hash = hash('sha256', $validator);
        $expires_at = (new DateTime())->add(new \DateInterval('P30D'))->format('Y-m-d H:i:s');
        
        try {
            $stmt = $this->conn->prepare("INSERT INTO user_remember_tokens (user_id, selector, token_hash, expires_at) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $selector, $token_hash, $expires_at);
            $stmt->execute();
            $stmt->close();
            
            $cookie_value = $selector . ':' . $validator;
            setcookie('remember_me', $cookie_value, [
                'expires' => time() + (86400 * 30),
                'path' => '/',
                'domain' => '', 
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        } catch (Exception $e) {
            error_log('Failed to create remember me token: ' . $e->getMessage());
        }
    }

    public function validate() {
        if (empty($_COOKIE['remember_me'])) {
            return false;
        }
        
        list($selector, $validator) = explode(':', $_COOKIE['remember_me'], 2);
        
        if (!$selector || !$validator) {
            return false;
        }
        
        try {
            $stmt = $this->conn->prepare("SELECT * FROM user_remember_tokens WHERE selector = ? AND expires_at > NOW()");
            $stmt->bind_param("s", $selector);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $token_data = $result->fetch_assoc();
                $token_hash = hash('sha256', $validator);
                
                if (hash_equals($token_data['token_hash'], $token_hash)) {
                    Session::regenerate();
                    
                    $user_id = $token_data['user_id'];
                    $userModel = new User();
                    $user = $userModel->findById($user_id);
                    
                    Session::set('user_id', $user_id);
                    Session::set('username', $user['username']);
                    
                    $this->delete($selector, false);
                    $this->create($user_id); // Issue new token
                    
                    return true;
                }
            }
            $stmt->close();
            
            $this->delete($selector, true); // Invalid token, delete
            return false;
            
        } catch (Exception $e) {
            error_log('Failed to validate remember me token: ' . $e->getMessage());
            return false;
        }
    }

    public function delete($selector, $clear_cookie_only = false) {
        if (!$clear_cookie_only) {
            try {
                $stmt = $this->conn->prepare("DELETE FROM user_remember_tokens WHERE selector = ?");
                $stmt->bind_param("s", $selector);
                $stmt->execute();
                $stmt->close();
            } catch (Exception $e) {
                error_log('Failed to delete remember me token: ' . $e->getMessage());
            }
        }
        
        if (isset($_COOKIE['remember_me'])) {
            unset($_COOKIE['remember_me']);
            setcookie('remember_me', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'domain' => '',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
    }
}
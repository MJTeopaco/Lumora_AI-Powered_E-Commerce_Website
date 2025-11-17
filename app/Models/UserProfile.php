<?php
// app/Models/UserProfile.php

namespace App\Models;

use App\Core\Database;

class UserProfile
{
    protected $conn;
    
    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Get user profile by user ID
     */
    public function getByUserId($user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT * FROM user_profiles 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $profile = $result->fetch_assoc();
        $stmt->close();
        
        return $profile;
    }

    /**
     * Create a new user profile
     */
    public function create($user_id, $data)
    {
        $full_name = $data['full_name'] ?? null;
        $phone_number = $data['phone_number'] ?? null;
        $gender = $data['gender'] ?? null;
        $birth_date = $data['birth_date'] ?? null;
        $profile_pic = $data['profile_pic'] ?? null;
        
        $stmt = $this->conn->prepare("
            INSERT INTO user_profiles 
            (user_id, full_name, phone_number, gender, birth_date, profile_pic, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
        ");
        
        $stmt->bind_param(
            "isssss",
            $user_id,
            $full_name,
            $phone_number,
            $gender,
            $birth_date,
            $profile_pic
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Update user profile (Partial updates)
     */
    public function update($user_id, $data)
    {
        $fieldsToUpdate = [];
        $params = [];
        $types = "";

        // Handle full name
        if (isset($data['full_name'])) {
            $fieldsToUpdate[] = "full_name = ?";
            $params[] = $data['full_name'];
            $types .= "s";
        }

        // Handle phone number
        if (isset($data['phone_number'])) {
            $fieldsToUpdate[] = "phone_number = ?";
            $params[] = $data['phone_number'];
            $types .= "s";
        }
        
        // Handle gender
        if (isset($data['gender'])) {
            $fieldsToUpdate[] = "gender = ?";
            $params[] = $data['gender'] ?: null;
            $types .= "s";
        }

        // Handle birth date
        if (isset($data['birth_date'])) {
            $fieldsToUpdate[] = "birth_date = ?";
            $params[] = $data['birth_date'] ?: null;
            $types .= "s";
        }
        
        // Handle profile pic
        if (isset($data['profile_pic'])) {
            $fieldsToUpdate[] = "profile_pic = ?";
            $params[] = $data['profile_pic'] ?: null;
            $types .= "s";
        }

        // Check if there is anything to update
        if (empty($fieldsToUpdate)) {
            return true; // Nothing to do
        }

        // Build the query
        $sql = "UPDATE user_profiles SET ";
        $sql .= implode(", ", $fieldsToUpdate);
        $sql .= ", updated_at = NOW() WHERE user_id = ?";
        
        // Add user_id to params
        $types .= "i";
        $params[] = $user_id;

        $stmt = $this->conn->prepare($sql);
        
        if (!$stmt) {
             throw new \Exception("Failed to prepare statement: " . $this->conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        
        $result = $stmt->execute();
        
        if (!$result) {
            throw new \Exception("Failed to execute statement: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $result;
    }

    /**
     * Check if phone number is in use (by another user)
     */
    public function isPhoneInUse($phone_number, $exclude_user_id = null)
    {
        if ($exclude_user_id) {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM user_profiles 
                WHERE phone_number = ? AND user_id != ?
            ");
            $stmt->bind_param("si", $phone_number, $exclude_user_id);
        } else {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count FROM user_profiles 
                WHERE phone_number = ?
            ");
            $stmt->bind_param("s", $phone_number);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }
    
    public function exists($user_id)
    {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) as count FROM user_profiles 
            WHERE user_id = ?
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row['count'] > 0;
    }

    public function updateProfilePicture($user_id, $filename)
    {
        return $this->update($user_id, ['profile_pic' => $filename]);
    }
}
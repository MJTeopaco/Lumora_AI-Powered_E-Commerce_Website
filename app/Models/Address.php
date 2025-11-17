<?php
// app/Models/Address.php

namespace App\Models;

use PDO;

class Address
{
    private $db;
    
    public function __construct()
    {
        // Initialize database connection
        $this->db = $this->getConnection();
    }

    /**
     * Get database connection
     */
    private function getConnection()
    {
        try {
            $host = 'localhost';
            $dbname = 'lumora_db';
            $username = 'root';
            $password = '';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            return new PDO($dsn, $username, $password, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * Get all addresses for a user
     */
    public function getAddressesByUserId($userId)
    {
        $sql = "SELECT * FROM addresses 
                WHERE user_id = :user_id 
                ORDER BY is_default DESC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $addresses = $stmt->fetchAll();
        
        return $addresses;
    }

    /**
     * Get a single address by ID and user ID
     */
    public function getAddressById($addressId, $userId)
    {
        $sql = "SELECT * FROM addresses 
                WHERE address_id = :address_id 
                AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'address_id' => $addressId,
            'user_id' => $userId
        ]);
        
        $address = $stmt->fetch();
        
        return $address;
    }

    /**
     * Get default address for a user
     */
    public function getDefaultAddress($userId)
    {
        $sql = "SELECT * FROM addresses 
                WHERE user_id = :user_id 
                AND is_default = 1 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $address = $stmt->fetch();
        
        return $address;
    }

    /**
     * Create a new address
     */
    public function createAddress($data)
    {
        $sql = "INSERT INTO addresses (
                    user_id, 
                    address_type,
                    address_line_1, 
                    address_line_2, 
                    barangay, 
                    city, 
                    province, 
                    region, 
                    postal_code, 
                    is_default
                ) VALUES (
                    :user_id, 
                    :address_type,
                    :address_line_1, 
                    :address_line_2, 
                    :barangay, 
                    :city, 
                    :province, 
                    :region, 
                    :postal_code, 
                    :is_default
                )";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'user_id' => $data['user_id'],
            'address_type' => $data['address_type'] ?? 'shipping',
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'barangay' => $data['barangay'],
            'city' => $data['city'],
            'province' => $data['province'],
            'region' => $data['region'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => $data['is_default'] ?? 0
        ]);
    }

    /**
     * Update an existing address
     */
    public function updateAddress($addressId, $userId, $data)
    {
        $sql = "UPDATE addresses SET 
                    address_line_1 = :address_line_1,
                    address_line_2 = :address_line_2,
                    barangay = :barangay,
                    city = :city,
                    province = :province,
                    region = :region,
                    postal_code = :postal_code,
                    is_default = :is_default,
                    updated_at = CURRENT_TIMESTAMP
                WHERE address_id = :address_id 
                AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'address_id' => $addressId,
            'user_id' => $userId,
            'address_line_1' => $data['address_line_1'],
            'address_line_2' => $data['address_line_2'] ?? null,
            'barangay' => $data['barangay'],
            'city' => $data['city'],
            'province' => $data['province'],
            'region' => $data['region'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => $data['is_default'] ?? 0
        ]);
    }

    /**
     * Delete an address
     */
    public function deleteAddress($addressId, $userId)
    {
        $sql = "DELETE FROM addresses 
                WHERE address_id = :address_id 
                AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'address_id' => $addressId,
            'user_id' => $userId
        ]);
    }

    /**
     * Unset all default addresses for a user
     */
    public function unsetDefaultAddresses($userId)
    {
        $sql = "UPDATE addresses 
                SET is_default = 0 
                WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute(['user_id' => $userId]);
    }

    /**
     * Set an address as default
     */
    public function setAsDefault($addressId, $userId)
    {
        $sql = "UPDATE addresses 
                SET is_default = 1,
                    updated_at = CURRENT_TIMESTAMP
                WHERE address_id = :address_id 
                AND user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            'address_id' => $addressId,
            'user_id' => $userId
        ]);
    }

    /**
     * Count addresses for a user
     */
    public function countUserAddresses($userId)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM addresses 
                WHERE user_id = :user_id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $result = $stmt->fetch();
        return $result['count'];
    }

    /**
     * Get addresses by type
     */
    public function getAddressesByType($userId, $type)
    {
        $sql = "SELECT * FROM addresses 
                WHERE user_id = :user_id 
                AND address_type = :address_type
                ORDER BY is_default DESC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'address_type' => $type
        ]);
        
        $addresses = $stmt->fetchAll();
        
        return $addresses;
    }
}
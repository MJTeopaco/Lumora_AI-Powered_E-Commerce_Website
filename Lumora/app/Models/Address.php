<?php
// app/Models/Address.php

namespace App\Models;

use App\Core\Database;

class Address
{
    private $conn;
    private $table = 'addresses';

    public function __construct()
    {
        $this->conn = Database::getConnection();
    }

    /**
     * Get all addresses for a user (excluding shop addresses)
     */
    public function getAddressesByUserId_User($userId)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = ? 
                  AND address_type != 'shop'
                  ORDER BY is_default DESC, created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $addresses;
    }

    /**
     * Get address by ID and user ID
     */
    public function getAddressById($addressId, $userId)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE address_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $addressId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $address = $result->fetch_assoc();
        $stmt->close();
        
        return $address;
    }

    /**
     * Get default address for a user
     */
    public function getDefaultAddress($userId)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = ? AND is_default = 1 
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $address = $result->fetch_assoc();
        $stmt->close();
        
        return $address;
    }

    /**
     * Create new address
     */
    public function createAddress($data)
    {
        $query = "INSERT INTO {$this->table} 
                  (user_id, address_type, address_line_1, address_line_2, 
                   region, province, city, barangay, postal_code, is_default) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        $stmt->bind_param(
            "issssssssi",
            $data['user_id'],
            $data['address_type'],
            $data['address_line_1'],
            $data['address_line_2'],
            $data['region'],
            $data['province'],
            $data['city'],
            $data['barangay'],
            $data['postal_code'],
            $data['is_default']
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Update existing address
     */
    public function updateAddress($addressId, $userId, $data)
    {
        $query = "UPDATE {$this->table} 
                  SET address_line_1 = ?,
                      address_line_2 = ?,
                      region = ?,
                      province = ?,
                      city = ?,
                      barangay = ?,
                      postal_code = ?,
                      is_default = ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE address_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Fixed bind_param string: 7 strings + 3 integers = "sssssssiii"
        $stmt->bind_param(
            "sssssssiii",
            $data['address_line_1'],
            $data['address_line_2'],
            $data['region'],
            $data['province'],
            $data['city'],
            $data['barangay'],
            $data['postal_code'],
            $data['is_default'],
            $addressId,
            $userId
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Delete address
     */
    public function deleteAddress($addressId, $userId)
    {
        $query = "DELETE FROM {$this->table} 
                  WHERE address_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $addressId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Unset all default addresses for a user (except specified address)
     */
    public function unsetDefaultAddresses($userId, $exceptAddressId = null)
    {
        if ($exceptAddressId) {
            $query = "UPDATE {$this->table} 
                      SET is_default = 0 
                      WHERE user_id = ? AND address_id != ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $userId, $exceptAddressId);
        } else {
            $query = "UPDATE {$this->table} 
                      SET is_default = 0 
                      WHERE user_id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $userId);
        }
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Set an address as default
     */
    public function setAsDefault($addressId, $userId)
    {
        $query = "UPDATE {$this->table} 
                  SET is_default = 1,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE address_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $addressId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Count addresses for a user
     */
    public function countUserAddresses($userId)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return (int)$row['count'];
    }

    /**
     * Get addresses by type
     */
    public function getAddressesByType($userId, $type)
    {
        $query = "SELECT * FROM {$this->table} 
                  WHERE user_id = ? AND address_type = ?
                  ORDER BY is_default DESC, created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $userId, $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $addresses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $addresses;
    }

    /**
     * Get connection (for compatibility with existing code)
     */
    public function getConnection()
    {
        return $this->conn;
    }
}
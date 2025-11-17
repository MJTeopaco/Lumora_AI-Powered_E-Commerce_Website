<?php 

namespace App\Models;


use App\Core\Database;

Class Admin {
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }


    // Dashboard 
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



    // Category Management Methods
    public function getAllCategories() {
        $stmt = $this->conn->prepare("
            SELECT c.category_id, c.name, c.slug,
                COUNT(DISTINCT pcl.product_id) AS product_count,
                c.created_at 
            FROM product_categories c
            LEFT JOIN product_category_links pcl 
                ON pcl.category_id = c.category_id
            GROUP BY c.category_id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $categories;
    }

    public function addCategory($name, $slug) {
        $stmt = $this->conn->prepare("
            INSERT INTO product_categories (name, slug)
            VALUES (?, ?)
        ");
        $stmt->bind_param("ss", $name, $slug);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function updateCategory($category_id, $name, $slug) {
        $stmt = $this->conn->prepare("
            UPDATE product_categories
            SET name = ?, slug = ?
            WHERE category_id = ?
        ");
        $stmt->bind_param("ssi", $name, $slug, $category_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function deleteCategory($category_id) {
        // Check if category is used by products
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) AS products
            FROM product_category_links
            WHERE category_id = ?
        ");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        $stmt->close();

        if ($data['products'] > 0) {
            return false;
        }

        // Safe to delete
        $stmt = $this->conn->prepare("
            DELETE FROM product_categories
            WHERE category_id = ?
        ");
        $stmt->bind_param("i", $category_id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }


    public function assignCategoriesToProduct($product_id, $categories) {

        // Remove old links
        $stmt = $this->conn->prepare("
            DELETE FROM product_category_links
            WHERE product_id = ?
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->close();

        // Insert new links
        $stmt = $this->conn->prepare("
            INSERT INTO product_category_links (product_id, category_id)
            VALUES (?, ?)
        ");

        foreach ($categories as $cat_id) {
            $stmt->bind_param("ii", $product_id, $cat_id);
            $stmt->execute();
        }

        $stmt->close();
        return true;
    }


    public function getCategoriesByProduct($product_id) {
        $stmt = $this->conn->prepare("
            SELECT c.*
            FROM product_category_links pcl
            JOIN product_categories c ON c.category_id = pcl.category_id
            WHERE pcl.product_id = ?
        ");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $categories;
    }


}
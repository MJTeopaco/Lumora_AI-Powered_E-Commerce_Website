<?php
// app/Models/ProductReview.php

namespace App\Models;

use App\Core\Database;

class ProductReview {
    
    protected $conn;

    public function __construct() {
        $this->conn = Database::getConnection();
    }

    /**
     * Get all reviews for a product with user details
     */
    public function getProductReviews($productId, $limit = 10, $offset = 0, $sortBy = 'newest') {
        $orderClause = match($sortBy) {
            'oldest' => 'pr.created_at ASC',
            'highest' => 'pr.rating DESC',
            'lowest' => 'pr.rating ASC',
            'helpful' => 'pr.helpful_count DESC',
            default => 'pr.created_at DESC'
        };

        $query = "SELECT 
                    pr.review_id,
                    pr.user_id,
                    pr.product_id,
                    pr.order_id,
                    pr.variant_id,
                    pr.title,
                    pr.rating,
                    pr.comment,
                    pr.helpful_count,
                    pr.is_verified_purchase,
                    pr.status,
                    pr.created_at,
                    pr.updated_at,
                    u.username,
                    up.profile_pic,
                    pv.variant_name,
                    pv.color,
                    pv.size,
                    pv.material
                  FROM product_reviews pr
                  INNER JOIN users u ON pr.user_id = u.user_id
                  LEFT JOIN user_profiles up ON u.user_id = up.user_id
                  LEFT JOIN product_variants pv ON pr.variant_id = pv.variant_id
                  WHERE pr.product_id = ? AND pr.status = 'APPROVED'
                  ORDER BY {$orderClause}
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $productId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get images for each review
        foreach ($reviews as &$review) {
            $review['images'] = $this->getReviewImages($review['review_id']);
            $review['response'] = $this->getSellerResponse($review['review_id']);
        }
        
        return $reviews;
    }

    /**
     * Get review statistics for a product
     */
    public function getProductReviewStats($productId) {
        $query = "SELECT 
                    COUNT(*) as total_reviews,
                    AVG(rating) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star,
                    SUM(CASE WHEN is_verified_purchase = 1 THEN 1 ELSE 0 END) as verified_purchases
                  FROM product_reviews
                  WHERE product_id = ? AND status = 'APPROVED'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc();
        $stmt->close();
        
        return $stats;
    }

    /**
     * Get review images
     */
    public function getReviewImages($reviewId) {
        $query = "SELECT image_path, display_order 
                  FROM product_review_images 
                  WHERE review_id = ? 
                  ORDER BY display_order ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $result = $stmt->get_result();
        $images = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $images;
    }

    /**
     * Get seller response
     */
    public function getSellerResponse($reviewId) {
        $query = "SELECT 
                    prr.response_id,
                    prr.response_text,
                    prr.created_at,
                    prr.updated_at,
                    s.shop_name
                  FROM product_review_responses prr
                  INNER JOIN shops s ON prr.shop_id = s.shop_id
                  WHERE prr.review_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $reviewId);
        $stmt->execute();
        $result = $stmt->get_result();
        $response = $result->fetch_assoc();
        $stmt->close();
        
        return $response;
    }

    /**
     * Create a new review
     */
    public function createReview($data) {
        $query = "INSERT INTO product_reviews 
                  (user_id, product_id, order_id, variant_id, title, rating, comment, is_verified_purchase, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'APPROVED')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "iiiisisi",
            $data['user_id'],
            $data['product_id'],
            $data['order_id'],
            $data['variant_id'],
            $data['title'],
            $data['rating'],
            $data['comment'],
            $data['is_verified_purchase']
        );
        
        if ($stmt->execute()) {
            $reviewId = $this->conn->insert_id;
            $stmt->close();
            return $reviewId;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Update a review
     */
    public function updateReview($reviewId, $userId, $data) {
        $query = "UPDATE product_reviews 
                  SET title = ?, 
                      rating = ?, 
                      comment = ?,
                      updated_at = CURRENT_TIMESTAMP
                  WHERE review_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sisii",
            $data['title'],
            $data['rating'],
            $data['comment'],
            $reviewId,
            $userId
        );
        
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Delete a review
     */
    public function deleteReview($reviewId, $userId) {
        $query = "DELETE FROM product_reviews WHERE review_id = ? AND user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $reviewId, $userId);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Add review image
     */
    public function addReviewImage($reviewId, $imagePath, $displayOrder = 0) {
        $query = "INSERT INTO product_review_images (review_id, image_path, display_order)
                  VALUES (?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("isi", $reviewId, $imagePath, $displayOrder);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Mark review as helpful
     */
    public function markHelpful($reviewId, $userId) {
        // Check if already voted
        $checkQuery = "SELECT helpful_id FROM product_review_helpful 
                       WHERE review_id = ? AND user_id = ?";
        $stmt = $this->conn->prepare($checkQuery);
        $stmt->bind_param("ii", $reviewId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $stmt->close();
            return false; // Already voted
        }
        $stmt->close();

        // Add vote
        $insertQuery = "INSERT INTO product_review_helpful (review_id, user_id) VALUES (?, ?)";
        $stmt = $this->conn->prepare($insertQuery);
        $stmt->bind_param("ii", $reviewId, $userId);
        
        if ($stmt->execute()) {
            $stmt->close();
            
            // Update helpful_count
            $updateQuery = "UPDATE product_reviews 
                           SET helpful_count = helpful_count + 1 
                           WHERE review_id = ?";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param("i", $reviewId);
            $stmt->execute();
            $stmt->close();
            
            return true;
        }
        
        $stmt->close();
        return false;
    }

    /**
     * Check if user has already reviewed a product
     */
    public function hasUserReviewed($userId, $productId) {
        $query = "SELECT review_id FROM product_reviews 
                  WHERE user_id = ? AND product_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasReviewed = $result->num_rows > 0;
        $stmt->close();
        
        return $hasReviewed;
    }

    /**
     * Check if user has purchased the product
     */
    public function hasUserPurchased($userId, $productId) {
        $query = "SELECT oi.order_item_id 
                  FROM order_items oi
                  INNER JOIN orders o ON oi.order_id = o.order_id
                  INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id
                  WHERE o.user_id = ? 
                    AND pv.product_id = ?
                    AND o.order_status IN ('DELIVERED', 'COMPLETED')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $hasPurchased = $result->num_rows > 0;
        $stmt->close();
        
        return $hasPurchased;
    }

    /**
     * Get user's purchase details for review
     */
    public function getUserPurchaseDetails($userId, $productId) {
        $query = "SELECT 
                    o.order_id,
                    oi.variant_id,
                    pv.variant_name,
                    o.order_status
                  FROM order_items oi
                  INNER JOIN orders o ON oi.order_id = o.order_id
                  INNER JOIN product_variants pv ON oi.variant_id = pv.variant_id
                  WHERE o.user_id = ? 
                    AND pv.product_id = ?
                    AND o.order_status IN ('DELIVERED', 'COMPLETED')
                  ORDER BY o.created_at DESC
                  LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $userId, $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $details = $result->fetch_assoc();
        $stmt->close();
        
        return $details;
    }

    /**
     * Get user's reviews
     */
    public function getUserReviews($userId, $limit = 10, $offset = 0) {
        $query = "SELECT 
                    pr.review_id,
                    pr.product_id,
                    pr.title,
                    pr.rating,
                    pr.comment,
                    pr.helpful_count,
                    pr.is_verified_purchase,
                    pr.created_at,
                    p.name as product_name,
                    p.cover_picture,
                    p.slug as product_slug
                  FROM product_reviews pr
                  INNER JOIN products p ON pr.product_id = p.product_id
                  WHERE pr.user_id = ?
                  ORDER BY pr.created_at DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $userId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($reviews as &$review) {
            $review['images'] = $this->getReviewImages($review['review_id']);
        }
        
        return $reviews;
    }

    /**
     * Add seller response to review
     */
    public function addSellerResponse($reviewId, $shopId, $responseText) {
        $query = "INSERT INTO product_review_responses (review_id, shop_id, response_text)
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE 
                  response_text = VALUES(response_text),
                  updated_at = CURRENT_TIMESTAMP";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iis", $reviewId, $shopId, $responseText);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    }

    /**
     * Get shop's product reviews (for seller dashboard)
     */
    public function getShopProductReviews($shopId, $limit = 20, $offset = 0) {
        $query = "SELECT 
                    pr.review_id,
                    pr.rating,
                    pr.title,
                    pr.comment,
                    pr.helpful_count,
                    pr.is_verified_purchase,
                    pr.created_at,
                    u.username,
                    p.name as product_name,
                    p.product_id,
                    p.slug as product_slug,
                    prr.response_text,
                    prr.created_at as response_date
                  FROM product_reviews pr
                  INNER JOIN products p ON pr.product_id = p.product_id
                  INNER JOIN users u ON pr.user_id = u.user_id
                  LEFT JOIN product_review_responses prr ON pr.review_id = prr.review_id
                  WHERE p.shop_id = ? AND pr.status = 'APPROVED'
                  ORDER BY pr.created_at DESC
                  LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $shopId, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $reviews = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $reviews;
    }
}
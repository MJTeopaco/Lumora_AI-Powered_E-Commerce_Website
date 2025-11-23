<?php
// app/Helpers/AddProductHelper.php

namespace App\Helpers;

class AddProductHelper {
    
    /**
     * Validate product form data
     * @param array $postData
     * @param array $files
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateProductData($postData, $files) {
        $errors = [];
        
        // Validate required fields
        $requiredFields = [
            'product_name' => 'Product name',
            'short_description' => 'Short description',
            'description' => 'Full description',
            'category_id' => 'Category',
            'status' => 'Product status'
        ];
        
        foreach ($requiredFields as $field => $label) {
            if (empty($postData[$field])) {
                $errors[] = "{$label} is required";
            }
        }
        
        // Validate cover picture
        if (!isset($files['cover_picture']) || $files['cover_picture']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Cover image is required';
        }
        
        // Validate variants
        if (empty($postData['variants']) || !is_array($postData['variants'])) {
            $errors[] = 'At least one product variant is required';
        } else {
            // Validate each variant has required fields
            $hasValidVariant = false;
            foreach ($postData['variants'] as $index => $variant) {
                if (!empty($variant['price']) && !empty($variant['quantity'])) {
                    $hasValidVariant = true;
                    
                    // Validate price is numeric and positive
                    if (!is_numeric($variant['price']) || floatval($variant['price']) < 0) {
                        $errors[] = "Variant #" . ($index + 1) . ": Invalid price";
                    }
                    
                    // Validate quantity is numeric and non-negative
                    if (!is_numeric($variant['quantity']) || intval($variant['quantity']) < 0) {
                        $errors[] = "Variant #" . ($index + 1) . ": Invalid quantity";
                    }
                }
            }
            
            if (!$hasValidVariant) {
                $errors[] = 'At least one variant must have price and quantity';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Upload product image
     * @param array $file
     * @param int $shopId
     * @param string $type ('product' or 'variant')
     * @return array ['success' => bool, 'filename' => string|null, 'message' => string]
     */
    public static function uploadProductImage($file, $shopId, $type = 'product') {
        $uploadDir = 'uploads/products/' . $shopId . '/' . $type . 's/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                return [
                    'success' => false, 
                    'filename' => null,
                    'message' => 'Failed to create upload directory'
                ];
            }
        }
        
        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return [
                'success' => false,
                'filename' => null,
                'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP allowed.'
            ];
        }
        
        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            return [
                'success' => false,
                'filename' => null,
                'message' => 'File size exceeds 5MB limit.'
            ];
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $type . '_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return [
                'success' => true,
                'filename' => $filepath,
                'message' => 'Upload successful'
            ];
        }
        
        return [
            'success' => false,
            'filename' => null,
            'message' => 'Failed to move uploaded file.'
        ];
    }
    
    /**
     * Process variant images from $_FILES array
     * @param array $files
     * @param int $shopId
     * @return array Associative array [variantIndex => ['success' => bool, 'filename' => string|null]]
     */
    public static function processVariantImages($files, $shopId) {
        $results = [];
        
        if (!isset($files['variants']['name']) || !is_array($files['variants']['name'])) {
            return $results;
        }
        
        foreach ($files['variants']['name'] as $variantIndex => $variantFiles) {
            if (isset($variantFiles['product_picture']) && 
                $files['variants']['error'][$variantIndex]['product_picture'] === UPLOAD_ERR_OK) {
                
                // Restructure the file array for easier handling
                $variantFile = [
                    'name' => $files['variants']['name'][$variantIndex]['product_picture'],
                    'type' => $files['variants']['type'][$variantIndex]['product_picture'],
                    'tmp_name' => $files['variants']['tmp_name'][$variantIndex]['product_picture'],
                    'error' => $files['variants']['error'][$variantIndex]['product_picture'],
                    'size' => $files['variants']['size'][$variantIndex]['product_picture']
                ];
                
                $uploadResult = self::uploadProductImage($variantFile, $shopId, 'variant');
                $results[$variantIndex] = $uploadResult;
            }
        }
        
        return $results;
    }
    
    /**
     * Generate URL-friendly slug
     * @param string $text
     * @return string
     */
    public static function generateSlug($text) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
        $slug = $slug . '-' . time();
        return $slug;
    }
    
    /**
     * Generate SKU
     * @param int $productId
     * @param int $variantNumber
     * @return string
     */
    public static function generateSKU($productId, $variantNumber = 1) {
        return 'PRD-' . str_pad($productId, 6, '0', STR_PAD_LEFT) . '-V' . str_pad($variantNumber, 2, '0', STR_PAD_LEFT);
    }
    
    /**
     * Prepare product data for database insertion
     * @param array $postData
     * @param int $shopId
     * @param string $coverPicture
     * @return array
     */
    public static function prepareProductData($postData, $shopId, $coverPicture) {
        return [
            'shop_id' => $shopId,
            'name' => trim($postData['product_name']),
            'slug' => self::generateSlug($postData['product_name']),
            'short_description' => trim($postData['short_description']),
            'description' => trim($postData['description']),
            'cover_picture' => $coverPicture,
            'status' => $postData['status']
        ];
    }
    
    /**
     * Prepare variant data for database insertion
     * @param array $variantData
     * @param int $productId
     * @param int $variantNumber
     * @param string|null $variantImage
     * @param object $productModel
     * @return array
     */
    public static function prepareVariantData($variantData, $productId, $variantNumber, $variantImage, $productModel) {
        // Generate SKU if not provided
        $sku = !empty($variantData['sku']) ? trim($variantData['sku']) : self::generateSKU($productId, $variantNumber);
        
        // Check for duplicate SKU and regenerate if needed
        if ($productModel->skuExists($sku)) {
            $sku = self::generateSKU($productId, $variantNumber . time());
        }
        
        return [
            'product_id' => $productId,
            'variant_name' => !empty($variantData['name']) ? trim($variantData['name']) : null,
            'sku' => $sku,
            'price' => floatval($variantData['price']),
            'quantity' => intval($variantData['quantity']),
            'color' => !empty($variantData['color']) ? trim($variantData['color']) : null,
            'size' => !empty($variantData['size']) ? trim($variantData['size']) : null,
            'material' => !empty($variantData['material']) ? trim($variantData['material']) : null,
            'product_picture' => $variantImage,
            'is_active' => isset($variantData['status']) ? intval($variantData['status']) : 1
        ];
    }
    
    /**
     * Process and link tags to product
     * NOTE: This now accepts a model that has getOrCreateTag and linkProductToTag methods
     * Can be either Shop model or Product model
     * @param string $tagsString
     * @param int $productId
     * @param object $model (Shop or Product model with tag methods)
     * @return int Number of tags linked
     */
    public static function processTags($tagsString, $productId, $model) {
        if (empty($tagsString)) {
            return 0;
        }
        
        $tags = array_map('trim', explode(',', $tagsString));
        $linkedCount = 0;
        
        foreach ($tags as $tagName) {
            if (!empty($tagName)) {
                $tagId = $model->getOrCreateTag($tagName);
                if ($tagId && $model->linkProductToTag($productId, $tagId)) {
                    $linkedCount++;
                }
            }
        }
        
        return $linkedCount;
    }
    
    /**
     * Clean up uploaded files (used on error)
     * @param array $filePaths
     * @return void
     */
    public static function cleanupFiles($filePaths) {
        foreach ($filePaths as $filePath) {
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}
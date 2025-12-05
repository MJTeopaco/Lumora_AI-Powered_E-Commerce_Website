<?php
// app/Models/Services/SmartSearchService.php

namespace App\Models\Services;

class SmartSearchService {
    private $pythonApiUrl;
    private $timeout;
    
    public function __construct() {
        // Python Flask API URL
        $this->pythonApiUrl = 'http://localhost:5000';
        $this->timeout = 30; // seconds
    }

    /**
     * Check if the Python service is running
     * @return bool
     */
    public function isServiceHealthy() {
        try {
            $ch = curl_init($this->pythonApiUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return isset($data['smart_search_loaded']) && $data['smart_search_loaded'] === true;
            }
            
            return false;
        } catch (\Exception $e) {
            error_log("Search service health check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform smart search using ML similarity
     * 
     * @param string $query Search query
     * @param int $topK Number of results to return
     * @param float $minSimilarity Minimum similarity threshold (0.0 to 1.0)
     * @return array Search results with success status
     */
    public function search($query, $topK = 20, $minSimilarity = 0.15) {
        try {
            $ch = curl_init($this->pythonApiUrl . '/search');
            
            $payload = json_encode([
                'query' => $query,
                'top_k' => (int)$topK,
                'min_similarity' => (float)$minSimilarity
            ]);
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload)
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                error_log("Smart search cURL error: " . $curlError);
                return [
                    'success' => false,
                    'error' => 'Connection to search service failed: ' . $curlError
                ];
            }
            
            if ($httpCode !== 200) {
                error_log("Smart search HTTP error: " . $httpCode);
                return [
                    'success' => false,
                    'error' => 'Search service returned error code: ' . $httpCode
                ];
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Smart search JSON decode error: " . json_last_error_msg());
                return [
                    'success' => false,
                    'error' => 'Invalid response from search service'
                ];
            }
            
            return $data;
            
        } catch (\Exception $e) {
            error_log("Smart search exception: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get search suggestions/autocomplete
     * 
     * @param string $query Partial search query
     * @param int $limit Number of suggestions
     * @return array Suggestions with success status
     */
    public function getSuggestions($query, $limit = 5) {
        try {
            // For now, use the regular search with lower threshold
            // You can implement a separate suggestions endpoint in Python later
            $results = $this->search($query, $limit, 0.1);
            
            if (!$results['success']) {
                return [
                    'success' => false,
                    'suggestions' => []
                ];
            }
            
            // Extract product names as suggestions
            $suggestions = [];
            foreach ($results['results'] as $result) {
                if (isset($result['name'])) {
                    $suggestions[] = $result['name'];
                }
            }
            
            return [
                'success' => true,
                'suggestions' => $suggestions
            ];
            
        } catch (\Exception $e) {
            error_log("Get suggestions exception: " . $e->getMessage());
            return [
                'success' => false,
                'suggestions' => []
            ];
        }
    }

    /**
     * Get service statistics
     * 
     * @return array Service stats
     */
    public function getStats() {
        try {
            $ch = curl_init($this->pythonApiUrl . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                return json_decode($response, true);
            }
            
            return [
                'status' => 'unhealthy',
                'smart_search_loaded' => false
            ];
            
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Find similar products based on product ID
     * 
     * @param int $productId Product ID
     * @param string $productText Product name and description
     * @param int $limit Number of similar products
     * @return array Similar products
     */
    public function findSimilar($productId, $productText, $limit = 5) {
        try {
            // Use search with product text
            $results = $this->search($productText, $limit + 1, 0.2);
            
            if (!$results['success']) {
                return [
                    'success' => false,
                    'products' => []
                ];
            }
            
            // Filter out the current product and limit results
            $similarProducts = [];
            foreach ($results['results'] as $result) {
                if (isset($result['product_id']) && $result['product_id'] != $productId) {
                    $similarProducts[] = $result;
                    if (count($similarProducts) >= $limit) {
                        break;
                    }
                }
            }
            
            return [
                'success' => true,
                'products' => $similarProducts
            ];
            
        } catch (\Exception $e) {
            error_log("Find similar exception: " . $e->getMessage());
            return [
                'success' => false,
                'products' => []
            ];
        }
    }
}
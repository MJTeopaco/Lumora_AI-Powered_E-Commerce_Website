<?php
// app/Models/Services/SmartSearchService.php

namespace App\Models\Services;

class SmartSearchService {
    private $pythonApiUrl;
    private $timeout;
    
    public function __construct() {
        // Python Flask API URL
        $this->pythonApiUrl = 'https://lumora-ai-api-production.up.railway.app';
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
     * Perform hybrid smart search using ML similarity + tag matching
     * 
     * @param string $query Search query
     * @param int $topK Number of results to return
     * @param float $minSimilarity Minimum similarity threshold (0.0 to 1.0)
     * @param float $tagBoostWeight Weight for tag matching boost (0.0 to 1.0, default 0.3)
     * @return array Search results with success status
     */
    public function search($query, $topK = 20, $minSimilarity = 0.15, $tagBoostWeight = 0.3) {
        try {
            $ch = curl_init($this->pythonApiUrl . '/search');
            
            $payload = json_encode([
                'query' => $query,
                'top_k' => (int)$topK,
                'min_similarity' => (float)$minSimilarity,
                'tag_boost_weight' => (float)$tagBoostWeight
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
     * Perform standard search (TF-IDF only, no tag boosting)
     * Useful when you want pure similarity matching
     * 
     * @param string $query Search query
     * @param int $topK Number of results
     * @return array Search results
     */
    public function searchSimilarityOnly($query, $topK = 20) {
        return $this->search($query, $topK, 0.15, 0.0); // tag_boost_weight = 0
    }

    /**
     * Perform tag-focused search (more weight on tag matching)
     * Useful for category-like searches
     * 
     * @param string $query Search query
     * @param int $topK Number of results
     * @return array Search results
     */
    public function searchTagFocused($query, $topK = 20) {
        return $this->search($query, $topK, 0.10, 0.6); // Higher tag weight
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
            // Use the regular search with lower threshold and limit
            $results = $this->search($query, $limit, 0.1, 0.3);
            
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
                    $suggestions[] = [
                        'name' => $result['name'],
                        'slug' => $result['slug'] ?? '',
                        'tags' => $result['matched_tags'] ?? []
                    ];
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
     * Uses the /similar/<product_id> endpoint with tag boosting
     * 
     * @param int $productId Product ID
     * @param int $limit Number of similar products
     * @return array Similar products
     */
    public function findSimilar($productId, $limit = 5) {
        try {
            $url = $this->pythonApiUrl . '/similar/' . $productId . '?top_k=' . $limit;
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            if ($curlError) {
                error_log("Find similar cURL error: " . $curlError);
                return [
                    'success' => false,
                    'products' => []
                ];
            }
            
            if ($httpCode !== 200) {
                error_log("Find similar HTTP error: " . $httpCode);
                return [
                    'success' => false,
                    'products' => []
                ];
            }
            
            $data = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'products' => []
                ];
            }
            
            return [
                'success' => $data['success'] ?? false,
                'products' => $data['results'] ?? []
            ];
            
        } catch (\Exception $e) {
            error_log("Find similar exception: " . $e->getMessage());
            return [
                'success' => false,
                'products' => []
            ];
        }
    }

    /**
     * Get detailed search analytics
     * Returns information about search performance including tag matching
     * 
     * @param array $searchResults Results from search() method
     * @return array Analytics data
     */
    public function getSearchAnalytics($searchResults) {
        if (!isset($searchResults['results']) || !is_array($searchResults['results'])) {
            return [
                'total_results' => 0,
                'has_tag_matches' => false,
                'avg_similarity' => 0,
                'avg_tag_boost' => 0,
                'search_method' => $searchResults['search_method'] ?? 'unknown'
            ];
        }
        
        $results = $searchResults['results'];
        $totalResults = count($results);
        
        if ($totalResults === 0) {
            return [
                'total_results' => 0,
                'has_tag_matches' => false,
                'avg_similarity' => 0,
                'avg_tag_boost' => 0,
                'search_method' => $searchResults['search_method'] ?? 'unknown'
            ];
        }
        
        $totalSimilarity = 0;
        $totalTagBoost = 0;
        $hasTagMatches = false;
        $tagMatchedProducts = 0;
        
        foreach ($results as $result) {
            $totalSimilarity += $result['similarity_score'] ?? 0;
            $totalTagBoost += $result['tag_boost_score'] ?? 0;
            
            if (isset($result['matched_tags']) && !empty($result['matched_tags'])) {
                $hasTagMatches = true;
                $tagMatchedProducts++;
            }
        }
        
        return [
            'total_results' => $totalResults,
            'has_tag_matches' => $hasTagMatches,
            'tag_matched_products' => $tagMatchedProducts,
            'tag_match_percentage' => ($tagMatchedProducts / $totalResults) * 100,
            'avg_similarity' => $totalSimilarity / $totalResults,
            'avg_tag_boost' => $totalTagBoost / $totalResults,
            'search_method' => $searchResults['search_method'] ?? 'unknown',
            'query' => $searchResults['query'] ?? ''
        ];
    }
}
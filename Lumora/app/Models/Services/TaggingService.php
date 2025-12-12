<?php
// app/Models/Services/TaggingService.php
namespace App\Models\Services;

class TaggingService {
    private $pythonApiUrl;
    private $timeout;
    
    public function __construct() {
        // Python Flask API URL
        $this->pythonApiUrl = 'https://lumora-ai-api-production.up.railway.app';
        $this->timeout = 60; // seconds
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
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200) {
                $data = json_decode($response, true);
                return isset($data['status']) && $data['status'] === 'healthy';
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Predict tags for a single product
     * @param string $productName
     * @param string $description
     * @param string $shortDescription (optional)
     * @return array ['success' => bool, 'tags' => array, 'error' => string]
     */
    public function predictTags($productName, $description, $shortDescription = '') {
        try {
            // Prepare data
            $data = [
                'product_name' => $productName,
                'description' => $description,
                'short_description' => $shortDescription
            ];

            // Make API request
            $ch = curl_init($this->pythonApiUrl . '/predict-tags');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            // Check for curl errors
            if ($curlError) {
                return [
                    'success' => false,
                    'tags' => [],
                    'error' => 'Connection error: ' . $curlError
                ];
            }

            // Parse response
            if ($httpCode === 200) {
                $result = json_decode($response, true);
                
                if ($result && isset($result['success']) && $result['success']) {
                    return [
                        'success' => true,
                        'tags' => $result['tags'] ?? [],
                        'confidences' => $result['confidences'] ?? [],
                        'error' => null
                    ];
                } else {
                    return [
                        'success' => false,
                        'tags' => [],
                        'error' => $result['error'] ?? 'Unknown error from ML service'
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'tags' => [],
                    'error' => "HTTP Error: $httpCode"
                ];
            }

        } catch (\Exception $e) {
            return [
                'success' => false,
                'tags' => [],
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Predict tags for multiple products
     * @param array $products Array of ['product_name' => '', 'description' => '']
     * @return array
     */
    public function batchPredictTags($products) {
        try {
            $data = ['products' => $products];

            $ch = curl_init($this->pythonApiUrl . '/batch-predict-tags');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return $result;
            }

            return [
                'success' => false,
                'error' => "HTTP Error: $httpCode"
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Convert tags array to comma-separated string
     * @param array $tags
     * @return string
     */
    public function tagsToString($tags) {
        return implode(', ', $tags);
    }

    /**
     * Merge user-provided tags with predicted tags
     * @param string $userTags Comma-separated tags from user
     * @param array $predictedTags Array of predicted tags
     * @return string Comma-separated merged tags
     */
    public function mergeTags($userTags, $predictedTags) {
        // Parse user tags
        $userTagsArray = array_map('trim', explode(',', $userTags));
        $userTagsArray = array_filter($userTagsArray); // Remove empty values
        
        // Merge and remove duplicates (case-insensitive)
        $allTags = array_merge($userTagsArray, $predictedTags);
        $allTags = array_unique(array_map('strtolower', $allTags));
        
        return implode(', ', $allTags);
    }
}
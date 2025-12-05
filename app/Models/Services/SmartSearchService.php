<?php
// app/Models/Services/TaggingService.php
namespace App\Models\Services;

class SmartSearchService {
    private $pythonApiUrl;
    private $timeout;
    
    public function __construct() {
        // Python Flask API URL
        $this->pythonApiUrl = 'http://localhost:5000';
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



    

}

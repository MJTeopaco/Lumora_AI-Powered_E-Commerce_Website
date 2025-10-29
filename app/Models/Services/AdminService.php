<?php
namespace App\Models\Services;
use App\Models\DataAccess\AdminQueries;

class AdminService {
    protected $adminQueries;

    public function __construct(AdminQueries $adminQueries) {
        $this->adminQueries = $adminQueries;
    }

    // Add your service methods here



    
}

?>
<?php
// generate_key.php
// Run this file ONCE to get your encryption key
// Then delete this file for security

require_once __DIR__ . '/app/Helpers/EncryptionHelper.php';

use App\Helpers\EncryptionHelper;

echo "===========================================\n";
echo "   LUMORA ENCRYPTION KEY GENERATOR\n";
echo "===========================================\n\n";

$key = EncryptionHelper::generateKey();

echo "Your encryption key is:\n\n";
echo "    " . $key . "\n\n";
echo "===========================================\n";
echo "NEXT STEPS:\n";
echo "===========================================\n";
echo "1. Copy the key above\n";
echo "2. Add to your .env file:\n";
echo "   ENCRYPTION_KEY=" . $key . "\n\n";
echo "3. DELETE this file (generate_key.php)\n";
echo "===========================================\n";
?>
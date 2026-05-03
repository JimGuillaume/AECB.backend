<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Infrastructure\Persistence\DatabaseConnection;

(function () {
    // Load environment variables
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    
    // Initialize database connection
    $db = DatabaseConnection::getInstance();
    
    // Now you can use $db for queries
})();
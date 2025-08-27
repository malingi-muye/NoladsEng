<?php
// Environment setting (development/production)
define('ENVIRONMENT', 'development');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'noladseng_user');
define('DB_PASS', 'noladseng@25##');
define('DB_NAME', 'noladseng_db');

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Common response format for all API endpoints
function apiResponse($data = null, $success = true, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    
    $response = [
        'success' => $success
    ];
    
    if ($success) {
        $response['data'] = $data;
    } else {
        $response['error'] = $data;
    }
    
    return json_encode($response);
}

// Database connection function
function getDbConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed");
        }
        
        // Set UTF-8 encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        // Log the real error but don't expose it
        error_log($e->getMessage());
        throw new Exception("Database connection error");
    }
}

// Input validation helper
function validateInput($data, $required = []) {
    $errors = [];
    
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            $errors[] = "Missing required field: {$field}";
        }
    }
    
    if (!empty($errors)) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ]);
        exit;
    }
    
    return array_map('trim', $data);
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: same-origin');
header('Content-Security-Policy: default-src \'self\'');

if (ENVIRONMENT === 'production') {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'noladseng_user');
define('DB_PASS', 'noladseng@25##');
define('DB_NAME', 'noladseng_db');

// API configuration
define('API_VERSION', '1.0.0');
define('API_BASE_PATH', '/api');

// File upload configuration
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', [
    'image/jpeg',
    'image/png',
    'image/webp',
    'image/gif'
]);

// Create database connection
function createDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        throw new Exception("Database connection error: " . $e->getMessage());
    }
}

// Helper function to return JSON response
function jsonResponse($data = null, $success = true, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    
    $response = [
        'success' => $success,
        'data' => $data
    ];
    
    if (!$success && $data) {
        $response['error'] = $data;
        unset($response['data']);
    }
    
    echo json_encode($response);
    exit;
}

// Helper function to validate request method
function validateMethod($methods) {
    $requestMethod = $_SERVER['REQUEST_METHOD'];
    $methods = (array) $methods;
    
    if (!in_array($requestMethod, $methods)) {
        jsonResponse("Method not allowed", false, 405);
    }
}

// Helper function to get JSON input
function getJsonInput() {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        jsonResponse("Invalid JSON input", false, 400);
    }
    
    return $data;
}

// Helper function to sanitize input
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Helper function to validate required fields
function validateRequiredFields($data, $required) {
    foreach ($required as $field) {
        if (!isset($data[$field]) || empty($data[$field])) {
            jsonResponse("Missing required field: $field", false, 400);
        }
    }
}

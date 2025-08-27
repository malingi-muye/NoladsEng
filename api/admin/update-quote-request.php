<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../utils/auth.php';

if (!function_exists('get_db_connection')) {
    function get_db_connection() {
        // Example PDO connection, replace with your actual DB credentials
        $host = 'localhost';
        $db   = 'your_database';
        $user = 'your_username';
        $pass = 'your_password';
        $charset = 'utf8mb4';
        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            return new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }
}

// Verify admin authentication
if (!function_exists('verify_admin_auth')) {
    function verify_admin_auth() {
        // Example implementation, replace with your actual authentication logic
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
    }
}
verify_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['id']) || !isset($data['status'])) {
        throw new Exception('Missing required fields');
    }
    
    if (!in_array($data['status'], ['new', 'viewed', 'contacted', 'completed'])) {
        throw new Exception('Invalid status');
    }
    
    $pdo = get_db_connection();
    
    $updates = ['status = :status'];
    $params = [
        'id' => $data['id'],
        'status' => $data['status']
    ];

    // Add optional updates
    if (isset($data['notes'])) {
        $updates[] = 'notes = :notes';
        $params['notes'] = $data['notes'];
    }
    if (isset($data['assigned_to'])) {
        $updates[] = 'assigned_to = :assigned_to';
        $params['assigned_to'] = $data['assigned_to'];
    }
    if (isset($data['total_amount'])) {
        $updates[] = 'total_amount = :total_amount';
        $params['total_amount'] = $data['total_amount'];
    }

    $stmt = $pdo->prepare("
        UPDATE quote_requests 
        SET " . implode(', ', $updates) . "
        WHERE id = :id
    ");
    
    $stmt->execute($params);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception('Quote request not found');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

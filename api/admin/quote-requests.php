<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../utils/auth.php';

// Verify admin authentication
verify_admin_auth();

try {
    $pdo = get_db_connection();
    
    $stmt = $pdo->query("
        SELECT * FROM quote_requests 
        ORDER BY created_at DESC
    ");
    
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Decode JSON items for each request
    foreach ($requests as &$request) {
        $request['items'] = json_decode($request['items'], true);
    }
    
    echo json_encode($requests);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Failed to fetch quote requests'
    ]);
}

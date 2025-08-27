<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../utils/auth.php';

// Verify admin authentication
verify_admin_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Quote request ID is required');
    }
    
    $pdo = get_db_connection();
    
    $stmt = $pdo->prepare("
        SELECT 
            qr.*,
            u.first_name as assigned_first_name,
            u.last_name as assigned_last_name
        FROM quote_requests qr
        LEFT JOIN users u ON qr.assigned_to = u.id
        WHERE qr.id = :id
    ");
    
    $stmt->execute(['id' => $_GET['id']]);
    $quote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$quote) {
        throw new Exception('Quote request not found');
    }
    
    // Decode JSON items
    $quote['items'] = json_decode($quote['items'], true);
    
    echo json_encode([
        'success' => true,
        'data' => $quote
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

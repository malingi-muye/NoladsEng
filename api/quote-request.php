<?php
header('Content-Type: application/json');
require_once '../config/database.php';
require_once '../utils/email.php';
// Ensure send_email function is defined in utils/email.php or define it below if missing

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Get POST data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    $required = ['name', 'email', 'items', 'type'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Missing required field: {$field}");
        }
    }
    
    // Validate email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    // Validate type
    if (!in_array($data['type'], ['service', 'product'])) {
        throw new Exception('Invalid type. Must be either service or product');
    }
    
    // Connect to database
    $pdo = get_db_connection();
    
    // Insert quote request
    $stmt = $pdo->prepare("
        INSERT INTO quote_requests (
            name, email, phone, company_name, message, 
            items, type, total_amount
        ) VALUES (
            :name, :email, :phone, :company_name, :message, 
            :items, :type, :total_amount
        )
    ");
    
    $stmt->execute([
        'name' => $data['name'],
        'email' => $data['email'],
        'phone' => $data['phone'] ?? null,
        'company_name' => $data['company_name'] ?? null,
        'message' => $data['message'] ?? null,
        'items' => json_encode($data['items']),
        'type' => $data['type'],
        'total_amount' => $data['total_amount'] ?? null
    ]);
    
    // Send email notification
    $to = getenv('ADMIN_EMAIL');
    $subject = "New Quote Request - {$data['type']}";
    
    $items_list = '';
    foreach ($data['items'] as $item) {
        $items_list .= "- {$item['name']}\n";
    }
    
    $message = "New quote request received:\n\n"
             . "Name: {$data['name']}\n"
             . "Email: {$data['email']}\n"
             . "Phone: " . ($data['phone'] ?? 'Not provided') . "\n"
             . "Company: " . ($data['company_name'] ?? 'Not provided') . "\n"
             . "Message: " . ($data['message'] ?? 'Not provided') . "\n"
             . "Total Amount: " . ($data['total_amount'] ? '$' . number_format($data['total_amount'], 2) : 'Not provided') . "\n\n"
             . "Selected {$data['type']}s:\n{$items_list}";
    if (!function_exists('send_email')) {
        /**
         * Simple email sending function as a fallback.
         */
        function send_email($to, $subject, $message) {
            $headers = "From: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n" .
                       "Reply-To: no-reply@" . $_SERVER['SERVER_NAME'] . "\r\n" .
                       "Content-Type: text/plain; charset=UTF-8\r\n";
            return mail($to, $subject, $message, $headers);
        }
    }
    send_email($to, $subject, $message);
    send_email($to, $subject, $message);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Quote request submitted successfully'
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}

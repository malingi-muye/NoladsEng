<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/ContactModel.php';

class ContactController {
    private $auth;
    private $contactModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->contactModel = new ContactModel();
    }
    
    public function list() {
        $this->auth->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        
        $where = ['1'];
        $params = [];
        
        if ($search) {
            $where[] = '(name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?)';
            $params = array_fill(0, 4, "%{$search}%");
        }
        
        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        $result = $this->contactModel->findAll([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function statistics() {
        $this->auth->requireAdmin();
        
        $stats = $this->contactModel->getStatistics();
        Response::json($stats);
    }
    
    public function get($id) {
        $this->auth->requireAdmin();
        
        $message = $this->contactModel->findById($id);
        
        if (!$message) {
            Response::error('Message not found', 404);
        }
        
        Response::json($message);
    }
    
    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['email']) || !isset($data['message'])) {
            Response::error('Name, email and message are required');
        }
        
        // Validate email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            Response::error('Invalid email address');
        }
        
        // Set initial status
        $data['status'] = 'unread';
        
        $message = $this->contactModel->create($data);
        
        // Send email notification to admin (implement this later)
        // $this->sendNotification($message);
        
        Response::json($message);
    }
    
    public function update($id) {
        $this->auth->requireAdmin();
        
        $message = $this->contactModel->findById($id);
        
        if (!$message) {
            Response::error('Message not found', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $message = $this->contactModel->update($id, $data);
        Response::json($message);
    }
    
    public function markAsRead($id) {
        $this->auth->requireAdmin();
        
        $message = $this->contactModel->findById($id);
        
        if (!$message) {
            Response::error('Message not found', 404);
        }
        
        $message = $this->contactModel->update($id, ['status' => 'read']);
        Response::json($message);
    }
    
    public function markAsReplied($id) {
        $this->auth->requireAdmin();
        
        $message = $this->contactModel->findById($id);
        
        if (!$message) {
            Response::error('Message not found', 404);
        }
        
        $message = $this->contactModel->update($id, ['status' => 'replied']);
        Response::json($message);
    }
    
    public function delete($id) {
        $this->auth->requireAdmin();
        
        $message = $this->contactModel->findById($id);
        
        if (!$message) {
            Response::error('Message not found', 404);
        }
        
        $this->contactModel->delete($id);
        Response::json(['message' => 'Message deleted successfully']);
    }
    
    private function sendNotification($message) {
        // Implement email notification here
        // You can use PHPMailer or other email libraries
        // This will be implemented later
    }
}

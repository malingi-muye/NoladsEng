<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/QuoteModel.php';

class QuotesController {
    private $auth;
    private $quoteModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->quoteModel = new QuoteModel();
    }
    
    public function list() {
        $user = $this->auth->requireAuth();
        $isAdmin = $user['role'] === 'admin';
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
        
        $where = ['1'];
        $params = [];
        
        // Non-admins can only see their own quotes
        if (!$isAdmin) {
            $where[] = 'user_id = ?';
            $params[] = $user['id'];
        } elseif ($userId) {
            $where[] = 'user_id = ?';
            $params[] = $userId;
        }
        
        if ($search) {
            $where[] = '(project_name LIKE ? OR description LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($status) {
            $where[] = 'status = ?';
            $params[] = $status;
        }
        
        $result = $this->quoteModel->findAllWithDetails([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function statistics() {
        $this->auth->requireAdmin();
        
        $stats = $this->quoteModel->getStatistics();
        Response::json($stats);
    }
    
    public function get($id) {
        $user = $this->auth->requireAuth();
        $quote = $this->quoteModel->findByIdWithDetails($id);
        
        if (!$quote) {
            Response::error('Quote not found', 404);
        }
        
        // Only admins or quote owners can view quotes
        if ($user['role'] !== 'admin' && $quote['user_id'] !== $user['id']) {
            Response::error('Access denied', 403);
        }
        
        Response::json($quote);
    }
    
    public function create() {
        $user = $this->auth->requireAuth();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['project_name'])) {
            Response::error('Project name is required');
        }
        
        // Set user_id for non-admin users
        if ($user['role'] !== 'admin') {
            $data['user_id'] = $user['id'];
        }
        
        // Default status is 'pending'
        if (!isset($data['status'])) {
            $data['status'] = 'pending';
        }
        
        $quote = $this->quoteModel->create($data);
        Response::json($quote);
    }
    
    public function update($id) {
        $user = $this->auth->requireAuth();
        $isAdmin = $user['role'] === 'admin';
        
        $quote = $this->quoteModel->findById($id);
        
        if (!$quote) {
            Response::error('Quote not found', 404);
        }
        
        // Only admins can update quotes
        if (!$isAdmin) {
            Response::error('Access denied', 403);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $quote = $this->quoteModel->update($id, $data);
        Response::json($quote);
    }
    
    public function delete($id) {
        $user = $this->auth->requireAuth();
        $isAdmin = $user['role'] === 'admin';
        
        $quote = $this->quoteModel->findById($id);
        
        if (!$quote) {
            Response::error('Quote not found', 404);
        }
        
        // Only admins can delete quotes
        if (!$isAdmin) {
            Response::error('Access denied', 403);
        }
        
        $this->quoteModel->delete($id);
        Response::json(['message' => 'Quote deleted successfully']);
    }
}

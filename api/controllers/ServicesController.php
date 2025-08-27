<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/ServiceModel.php';

class ServicesController {
    private $auth;
    private $serviceModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->serviceModel = new ServiceModel();
    }
    
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $active = isset($_GET['active']) ? filter_var($_GET['active'], FILTER_VALIDATE_BOOLEAN) : null;
        
        $where = ['1'];
        $params = [];
        
        if ($search) {
            $where[] = '(name LIKE ? OR description LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        
        if ($active !== null) {
            $where[] = 'is_active = ?';
            $params[] = $active;
        }
        
        $result = $this->serviceModel->findAll([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function featured() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 4;
        
        $result = $this->serviceModel->findAll([
            'limit' => $limit,
            'where' => 'is_active = ? AND featured = ?',
            'params' => [true, true]
        ]);
        
        Response::json($result['items']);
    }
    
    public function get($id) {
        $service = $this->serviceModel->findById($id);
        
        if (!$service) {
            Response::error('Service not found', 404);
        }
        
        Response::json($service);
    }
    
    public function create() {
        $this->auth->requireAdmin();
        
        $rawData = file_get_contents('php://input');
        $data = json_decode($rawData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('Invalid JSON format: ' . json_last_error_msg(), 400);
        }
        
        if (!isset($data['name'])) {
            Response::error('Name is required');
        }
        
        $service = $this->serviceModel->create($data);
        Response::json($service);
    }
    
    public function update($id) {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $service = $this->serviceModel->findById($id);
        
        if (!$service) {
            Response::error('Service not found', 404);
        }
        
        $service = $this->serviceModel->update($id, $data);
        Response::json($service);
    }
    
    public function delete($id) {
        $this->auth->requireAdmin();
        
        $service = $this->serviceModel->findById($id);
        
        if (!$service) {
            Response::error('Service not found', 404);
        }
        
        $this->serviceModel->delete($id);
        Response::json(['message' => 'Service deleted successfully']);
    }
}

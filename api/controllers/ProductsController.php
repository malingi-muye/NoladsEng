<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ProductsController {
    private $auth;
    private $productModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->productModel = new ProductModel();
    }
    
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $active = isset($_GET['active']) ? filter_var($_GET['active'], FILTER_VALIDATE_BOOLEAN) : null;
        $minPrice = isset($_GET['minPrice']) ? (float)$_GET['minPrice'] : null;
        $maxPrice = isset($_GET['maxPrice']) ? (float)$_GET['maxPrice'] : null;
        $inStock = isset($_GET['inStock']) ? filter_var($_GET['inStock'], FILTER_VALIDATE_BOOLEAN) : null;
        $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'created_at';
        $sortOrder = isset($_GET['sortOrder']) ? strtoupper($_GET['sortOrder']) : 'DESC';
        
        $where = ['1'];
        $params = [];
        
        if ($search) {
            $where[] = '(name LIKE ? OR description LIKE ? OR specifications LIKE ?)';
            array_push($params, "%{$search}%", "%{$search}%", "%{$search}%");
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

        if ($minPrice !== null) {
            $where[] = 'price >= ?';
            $params[] = $minPrice;
        }

        if ($maxPrice !== null) {
            $where[] = 'price <= ?';
            $params[] = $maxPrice;
        }

        if ($inStock !== null) {
            $where[] = 'stock_quantity > 0';
        }

        // Validate and sanitize sort parameters
        $allowedSortFields = ['name', 'price', 'created_at', 'stock_quantity', 'views'];
        $sortBy = in_array($sortBy, $allowedSortFields) ? $sortBy : 'created_at';
        $sortOrder = in_array($sortOrder, ['ASC', 'DESC']) ? $sortOrder : 'DESC';
        
        $result = $this->productModel->findAll([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params,
            'orderBy' => "$sortBy $sortOrder"
        ]);
        
        Response::json($result);
    }
    
    public function featured() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;
        
        $result = $this->productModel->findAll([
            'limit' => $limit,
            'where' => 'is_active = ? AND featured = ?',
            'params' => [true, true]
        ]);
        
        Response::json($result['items']);
    }
    
    public function lowStock() {
        $this->auth->requireAdmin();
        
        $threshold = isset($_GET['threshold']) ? (int)$_GET['threshold'] : 10;
        
        $result = $this->productModel->findAll([
            'where' => 'stock_quantity <= ?',
            'params' => [$threshold]
        ]);
        
        Response::json($result['items']);
    }
    
    public function get($id) {
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            Response::error('Product not found', 404);
        }
        
        Response::json($product);
    }
    
    public function create() {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'])) {
            Response::error('Name is required');
        }
        
        $product = $this->productModel->create($data);
        Response::json($product);
    }
    
    public function update($id) {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            Response::error('Product not found', 404);
        }
        
        $product = $this->productModel->update($id, $data);
        Response::json($product);
    }
    
    public function updateStock($id) {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['quantity'])) {
            Response::error('Quantity is required');
        }
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            Response::error('Product not found', 404);
        }
        
        $product = $this->productModel->update($id, [
            'stock_quantity' => (int)$data['quantity']
        ]);
        
        Response::json($product);
    }
    
    public function delete($id) {
        $this->auth->requireAdmin();
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            Response::error('Product not found', 404);
        }
        
        $this->productModel->delete($id);
        Response::json(['message' => 'Product deleted successfully']);
    }

    public function subscribeToStock($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email'])) {
            Response::error('Email is required');
        }
        
        $product = $this->productModel->findById($id);
        
        if (!$product) {
            Response::error('Product not found', 404);
        }
        
        if ($product['stock_quantity'] > 0) {
            Response::error('Product is in stock');
        }
        
        $this->productModel->addStockAlert($id, $data['email']);
        Response::json(['message' => 'Successfully subscribed to stock alerts']);
    }

    private function generateSKU($name) {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $name), 0, 3));
        $timestamp = time();
        return $prefix . substr($timestamp, -6);
    }
}

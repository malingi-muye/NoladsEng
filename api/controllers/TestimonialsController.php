<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/TestimonialModel.php';

class TestimonialsController {
    private $auth;
    private $testimonialModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->testimonialModel = new TestimonialModel();
    }
    
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $rating = isset($_GET['rating']) ? (int)$_GET['rating'] : null;
        $userId = isset($_GET['userId']) ? (int)$_GET['userId'] : null;
        $activeOnly = isset($_GET['activeOnly']) ? filter_var($_GET['activeOnly'], FILTER_VALIDATE_BOOLEAN) : true;
        
        $where = ['1'];
        $params = [];
        
        if ($search) {
            $where[] = '(name LIKE ? OR company LIKE ? OR position LIKE ? OR content LIKE ?)';
            $params = array_fill(0, 4, "%{$search}%");
        }
        
        if ($rating) {
            $where[] = 'rating = ?';
            $params[] = $rating;
        }
        
        if ($userId) {
            $where[] = 'user_id = ?';
            $params[] = $userId;
        }
        
        if ($activeOnly) {
            $where[] = 'is_active = ?';
            $params[] = true;
        }
        
        $result = $this->testimonialModel->findAll([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function featured() {
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 6;
        
        $testimonials = $this->testimonialModel->featured($limit);
        Response::json($testimonials);
    }
    
    public function statistics() {
        $this->auth->requireAdmin();
        
        $stats = $this->testimonialModel->getStatistics();
        Response::json($stats);
    }
    
    public function get($id) {
        $testimonial = $this->testimonialModel->findById($id);
        
        if (!$testimonial) {
            Response::error('Testimonial not found', 404);
        }
        
        if (!$testimonial['is_active'] && !isset($_GET['preview'])) {
            $this->auth->requireAdmin();
        }
        
        Response::json($testimonial);
    }
    
    public function create() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['content']) || !isset($data['rating'])) {
            Response::error('Name, content and rating are required');
        }
        
        // Validate rating
        if ($data['rating'] < 1 || $data['rating'] > 5) {
            Response::error('Rating must be between 1 and 5');
        }
        
        // If not admin, set is_active to false (requires approval)
        try {
            $user = $this->auth->requireAuth();
            if ($user['role'] !== 'admin') {
                $data['is_active'] = false;
            }
        } catch (Exception $e) {
            // If no auth, set is_active to false
            $data['is_active'] = false;
        }
        
        $testimonial = $this->testimonialModel->create($data);
        Response::json($testimonial);
    }
    
    public function update($id) {
        $this->auth->requireAdmin();
        
        $testimonial = $this->testimonialModel->findById($id);
        
        if (!$testimonial) {
            Response::error('Testimonial not found', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['rating']) && ($data['rating'] < 1 || $data['rating'] > 5)) {
            Response::error('Rating must be between 1 and 5');
        }
        
        $testimonial = $this->testimonialModel->update($id, $data);
        Response::json($testimonial);
    }
    
    public function setFeatured($id) {
        $this->auth->requireAdmin();
        
        $testimonial = $this->testimonialModel->findById($id);
        
        if (!$testimonial) {
            Response::error('Testimonial not found', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['featured'])) {
            Response::error('Featured status is required');
        }
        
        $testimonial = $this->testimonialModel->update($id, [
            'is_featured' => (bool)$data['featured']
        ]);
        
        Response::json($testimonial);
    }
    
    public function delete($id) {
        $this->auth->requireAdmin();
        
        $testimonial = $this->testimonialModel->findById($id);
        
        if (!$testimonial) {
            Response::error('Testimonial not found', 404);
        }
        
        $this->testimonialModel->delete($id);
        Response::json(['message' => 'Testimonial deleted successfully']);
    }
}

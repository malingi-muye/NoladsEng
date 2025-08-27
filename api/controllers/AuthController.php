<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/UserModel.php';

class AuthController {
    private $auth;
    private $userModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->userModel = new UserModel();
    }
    
    public function login() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            Response::error('Email and password are required');
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user || !$this->userModel->verifyPassword($data['password'], $user['password_hash'])) {
            Response::error('Invalid credentials', 401);
        }
        
        if (!$user['is_active']) {
            Response::error('Account is inactive', 403);
        }
        
        $token = $this->auth->generateToken($user['id']);
        
        unset($user['password_hash']);
        
        Response::json([
            'user' => $user,
            'session' => [
                'id' => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60)
            ]
        ]);
    }
    
    public function logout() {
        $user = $this->auth->requireAuth();
        $headers = getallheaders();
        $auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            $token = $matches[1];
            $stmt = Database::getInstance()->getConnection()->prepare('
                DELETE FROM sessions WHERE id = ?
            ');
            $stmt->execute([$token]);
        }
        
        Response::json(['message' => 'Logged out successfully']);
    }
    
    public function verify() {
        $user = $this->auth->requireAuth();
        unset($user['password_hash']);
        Response::json(['user' => $user]);
    }
}

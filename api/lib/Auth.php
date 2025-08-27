<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../lib/Response.php';

class Auth {
    private $db;
    private $config;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->config = require __DIR__ . '/../config/config.php';
    }

    public function generateToken($userId) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + $this->config['jwt']['expiry']);
        
        $stmt = $this->db->prepare('
            INSERT INTO sessions (id, user_id, expires_at)
            VALUES (?, ?, ?)
        ');
        
        $stmt->execute([$token, $userId, $expiresAt]);
        return $token;
    }

    public function validateToken($token) {
        $stmt = $this->db->prepare('
            SELECT s.*, u.* 
            FROM sessions s
            JOIN users u ON s.user_id = u.id
            WHERE s.id = ? AND s.expires_at > NOW()
        ');
        
        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function getUser($token) {
        $session = $this->validateToken($token);
        if (!$session) {
            return null;
        }
        return $session;
    }

    public function requireAuth() {
        $headers = getallheaders();
        $auth = isset($headers['Authorization']) ? $headers['Authorization'] : '';
        
        if (!preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
            Response::error('Unauthorized', 401);
        }
        
        $token = $matches[1];
        $user = $this->getUser($token);
        
        if (!$user) {
            Response::error('Invalid or expired token', 401);
        }
        
        return $user;
    }

    public function requireAdmin() {
        $user = $this->requireAuth();
        if ($user['role'] !== 'admin') {
            Response::error('Access denied', 403);
        }
        return $user;
    }
}

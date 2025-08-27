<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../lib/Image.php';
require_once __DIR__ . '/../models/ImageModel.php';

class ImagesController {
    private $auth;
    private $imageModel;
    private $config;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->imageModel = new ImageModel();
        $this->config = require __DIR__ . '/../config/config.php';
    }
    
    public function list() {
        $this->auth->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $entityType = isset($_GET['entity_type']) ? $_GET['entity_type'] : '';
        
        $where = ['1'];
        $params = [];
        
        if ($search) {
            $where[] = '(filename LIKE ? OR original_name LIKE ? OR alt_text LIKE ?)';
            $params = array_fill(0, 3, "%{$search}%");
        }
        
        if ($entityType) {
            $where[] = 'entity_type = ?';
            $params[] = $entityType;
        }
        
        $result = $this->imageModel->findAll([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function stats() {
        $this->auth->requireAdmin();
        
        $stats = $this->imageModel->getStorageStats();
        Response::json($stats);
    }
    
    public function get($id) {
        $image = $this->imageModel->findById($id);
        
        if (!$image) {
            Response::error('Image not found', 404);
        }
        
        Response::json($image);
    }
    
    public function getByEntity($entityType, $entityId) {
        $images = $this->imageModel->findByEntity($entityType, $entityId);
        Response::json($images);
    }
    
    public function upload() {
        $this->auth->requireAdmin();
        
        if (!isset($_FILES['image'])) {
            Response::error('No image file uploaded');
        }
        
        $file = $_FILES['image'];
        $entityType = $_POST['entity_type'] ?? null;
        $entityId = $_POST['entity_id'] ?? null;
        $altText = $_POST['alt_text'] ?? null;
        
        try {
            $imageHandler = new Image($this->config['upload']);
            $result = $imageHandler->process($file);
            
            $imageData = [
                'filename' => $result['filename'],
                'original_name' => $file['name'],
                'mime_type' => $file['type'],
                'size' => $file['size'],
                'path' => $result['path'],
                'url' => $result['url'],
                'alt_text' => $altText,
                'entity_type' => $entityType,
                'entity_id' => $entityId
            ];
            
            $image = $this->imageModel->create($imageData);
            Response::json($image);
            
        } catch (Exception $e) {
            Response::error($e->getMessage());
        }
    }
    
    public function uploadMultiple() {
        $this->auth->requireAdmin();
        
        if (!isset($_FILES['images'])) {
            Response::error('No image files uploaded');
        }
        
        $files = $_FILES['images'];
        $entityType = $_POST['entity_type'] ?? null;
        $entityId = $_POST['entity_id'] ?? null;
        
        $results = [];
        $errors = [];
        
        $imageHandler = new Image($this->config['upload']);
        
        for ($i = 0; $i < count($files['name']); $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            try {
                $result = $imageHandler->process($file);
                
                $imageData = [
                    'filename' => $result['filename'],
                    'original_name' => $file['name'],
                    'mime_type' => $file['type'],
                    'size' => $file['size'],
                    'path' => $result['path'],
                    'url' => $result['url'],
                    'entity_type' => $entityType,
                    'entity_id' => $entityId
                ];
                
                $image = $this->imageModel->create($imageData);
                $results[] = $image;
                
            } catch (Exception $e) {
                $errors[] = [
                    'filename' => $file['name'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        Response::json([
            'images' => $results,
            'errors' => $errors
        ]);
    }
    
    public function update($id) {
        $this->auth->requireAdmin();
        
        $image = $this->imageModel->findById($id);
        
        if (!$image) {
            Response::error('Image not found', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $image = $this->imageModel->update($id, $data);
        Response::json($image);
    }
    
    public function delete($id) {
        $this->auth->requireAdmin();
        
        $image = $this->imageModel->findById($id);
        
        if (!$image) {
            Response::error('Image not found', 404);
        }
        
        // Delete the physical file
        $filePath = $image['path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        $this->imageModel->delete($id);
        Response::json(['message' => 'Image deleted successfully']);
    }
    
    public function cleanup() {
        $this->auth->requireAdmin();
        
        $orphanedImages = $this->imageModel->findOrphaned();
        $deleted = [];
        $errors = [];
        
        foreach ($orphanedImages as $image) {
            try {
                if (file_exists($image['path'])) {
                    unlink($image['path']);
                }
                $this->imageModel->delete($image['id']);
                $deleted[] = $image['filename'];
            } catch (Exception $e) {
                $errors[] = [
                    'filename' => $image['filename'],
                    'error' => $e->getMessage()
                ];
            }
        }
        
        Response::json([
            'message' => count($deleted) . ' orphaned images cleaned up',
            'deleted' => $deleted,
            'errors' => $errors
        ]);
    }
}

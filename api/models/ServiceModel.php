<?php
require_once __DIR__ . '/Model.php';

class ServiceModel extends Model {
    protected $table = 'services';
    
    public function featured($limit = 4) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE is_active = ? AND featured = ?
                ORDER BY created_at DESC
                LIMIT ?
            ");
            
            $stmt->execute([true, true, $limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Database error in featured services: " . $e->getMessage());
            Response::error('Database error occurred', 500);
        } catch (Exception $e) {
            error_log("Error in featured services: " . $e->getMessage());
            Response::error('An error occurred while processing your request', 500);
        }
    }
    
    public function getCategories() {
        $stmt = $this->db->prepare("
            SELECT DISTINCT category 
            FROM {$this->table}
            WHERE category IS NOT NULL
            ORDER BY category
        ");
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function create($data) {
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        return parent::create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        
        return parent::update($id, $data);
    }
}

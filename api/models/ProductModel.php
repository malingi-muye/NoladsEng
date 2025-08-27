<?php
require_once __DIR__ . '/Model.php';

class ProductModel extends Model {
    protected $table = 'products';
    
    public function featured($limit = 8) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE is_active = ? AND featured = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([true, true, $limit]);
        return $stmt->fetchAll();
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
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }
        if (isset($data['specifications']) && is_array($data['specifications'])) {
            $data['specifications'] = json_encode($data['specifications']);
        }
        
        return parent::create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = json_encode($data['images']);
        }
        if (isset($data['specifications']) && is_array($data['specifications'])) {
            $data['specifications'] = json_encode($data['specifications']);
        }
        
        return parent::update($id, $data);
    }
    
    public function getLowStock($threshold = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE stock_quantity <= ?
            ORDER BY stock_quantity ASC
        ");
        
        $stmt->execute([$threshold]);
        return $stmt->fetchAll();
    }

    public function addStockAlert($productId, $email) {
        $stmt = $this->db->prepare("
            INSERT INTO product_stock_alerts (product_id, email, created_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE updated_at = NOW()
        ");
        
        return $stmt->execute([$productId, $email]);
    }

    public function getStockAlerts($productId) {
        $stmt = $this->db->prepare("
            SELECT * FROM product_stock_alerts
            WHERE product_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$productId]);
        return $stmt->fetchAll();
    }

    public function incrementViews($id) {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET views = views + 1
            WHERE id = ?
        ");
        
        return $stmt->execute([$id]);
    }

    public function findByCategory($category, $limit = 4, $excludeId = null) {
        $sql = "
            SELECT * FROM {$this->table}
            WHERE category = ? AND is_active = 1
        ";
        
        $params = [$category];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $sql .= " ORDER BY views DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}

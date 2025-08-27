<?php
require_once __DIR__ . '/Model.php';

class ImageModel extends Model {
    protected $table = 'images';
    
    public function findByEntity($entityType, $entityId) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE entity_type = ? AND entity_id = ?
            ORDER BY created_at DESC
        ");
        
        $stmt->execute([$entityType, $entityId]);
        return $stmt->fetchAll();
    }
    
    public function getStorageStats() {
        // Get total stats
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total_images,
                COALESCE(SUM(size), 0) as total_size
            FROM {$this->table}
        ");
        
        $stmt->execute();
        $stats = $stmt->fetch();
        
        // Get stats by entity type
        $stmt = $this->db->prepare("
            SELECT
                entity_type,
                COUNT(*) as count,
                COALESCE(SUM(size), 0) as size
            FROM {$this->table}
            WHERE entity_type IS NOT NULL
            GROUP BY entity_type
        ");
        
        $stmt->execute();
        $stats['by_type'] = $stmt->fetchAll();
        
        return $stats;
    }
    
    public function findOrphaned() {
        $stmt = $this->db->prepare("
            SELECT i.* 
            FROM {$this->table} i
            LEFT JOIN users u ON i.entity_type = 'user' AND i.entity_id = u.id
            LEFT JOIN services s ON i.entity_type = 'service' AND i.entity_id = s.id
            LEFT JOIN products p ON i.entity_type = 'product' AND i.entity_id = p.id
            LEFT JOIN quotes q ON i.entity_type = 'quote' AND i.entity_id = q.id
            WHERE 
                (i.entity_type = 'user' AND u.id IS NULL) OR
                (i.entity_type = 'service' AND s.id IS NULL) OR
                (i.entity_type = 'product' AND p.id IS NULL) OR
                (i.entity_type = 'quote' AND q.id IS NULL)
        ");
        
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

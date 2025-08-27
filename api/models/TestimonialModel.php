<?php
require_once __DIR__ . '/Model.php';

class TestimonialModel extends Model {
    protected $table = 'testimonials';
    
    public function featured($limit = 6) {
        $stmt = $this->db->prepare("
            SELECT t.*, CONCAT(u.first_name, ' ', u.last_name) as user_name
            FROM {$this->table} t
            LEFT JOIN users u ON t.user_id = u.id
            WHERE t.is_active = ? AND t.is_featured = ?
            ORDER BY t.created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([true, true, $limit]);
        return $stmt->fetchAll();
    }
    
    public function getStatistics() {
        // Get basic stats
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN is_featured = 1 THEN 1 ELSE 0 END) as featured,
                AVG(rating) as average_rating
            FROM {$this->table}
        ");
        
        $stmt->execute();
        $stats = $stmt->fetch();
        
        // Get rating distribution
        $stmt = $this->db->prepare("
            SELECT 
                rating,
                COUNT(*) as count
            FROM {$this->table}
            GROUP BY rating
            ORDER BY rating
        ");
        
        $stmt->execute();
        $distribution = $stmt->fetchAll();
        
        $stats['rating_distribution'] = $distribution;
        
        return $stats;
    }
}

<?php
require_once __DIR__ . '/Model.php';

class ContactModel extends Model {
    protected $table = 'contact_messages';
    
    public function getStatistics() {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'unread' THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN status = 'read' THEN 1 ELSE 0 END) as read,
                SUM(CASE WHEN status = 'replied' THEN 1 ELSE 0 END) as replied
            FROM {$this->table}
        ");
        
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function getRecentUnread($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT *
            FROM {$this->table}
            WHERE status = 'unread'
            ORDER BY created_at DESC
            LIMIT ?
        ");
        
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}

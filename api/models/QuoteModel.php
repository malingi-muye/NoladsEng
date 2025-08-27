<?php
require_once __DIR__ . '/Model.php';

class QuoteModel extends Model {
    protected $table = 'quotes';
    
    public function findAllWithDetails($options = []) {
        $page = isset($options['page']) ? (int)$options['page'] : 1;
        $limit = isset($options['limit']) ? (int)$options['limit'] : 10;
        $offset = ($page - 1) * $limit;
        
        $where = isset($options['where']) ? $options['where'] : '1';
        $params = isset($options['params']) ? $options['params'] : [];
        
        // Count total
        $countStmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM {$this->table} q
            WHERE {$where}
        ");
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Get data with user and service details
        $stmt = $this->db->prepare("
            SELECT 
                q.*,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                u.email as user_email,
                s.name as service_name
            FROM {$this->table} q
            LEFT JOIN users u ON q.user_id = u.id
            LEFT JOIN services s ON q.service_id = s.id
            WHERE {$where}
            ORDER BY q.created_at DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->execute(array_merge($params, [$limit, $offset]));
        $items = $stmt->fetchAll();
        
        return [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];
    }
    
    public function findByIdWithDetails($id) {
        $stmt = $this->db->prepare("
            SELECT 
                q.*,
                CONCAT(u.first_name, ' ', u.last_name) as user_name,
                u.email as user_email,
                s.name as service_name
            FROM {$this->table} q
            LEFT JOIN users u ON q.user_id = u.id
            LEFT JOIN services s ON q.service_id = s.id
            WHERE q.id = ?
        ");
        
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getStatistics() {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
            FROM {$this->table}
        ");
        
        $stmt->execute();
        return $stmt->fetch();
    }
    
    public function create($data) {
        if (isset($data['requirements']) && is_array($data['requirements'])) {
            $data['requirements'] = json_encode($data['requirements']);
        }
        
        return parent::create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['requirements']) && is_array($data['requirements'])) {
            $data['requirements'] = json_encode($data['requirements']);
        }
        
        return parent::update($id, $data);
    }
}

<?php
require_once __DIR__ . '/../config/Database.php';

abstract class BaseModel {
    protected $pdo;
    protected $table;
    protected $fillable = [];
    
    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }
    
    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function findBySlug($slug) {
        $sql = "SELECT * FROM {$this->table} WHERE slug = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function create($data) {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        $columns = implode(', ', array_keys($fields));
        $values = implode(', ', array_fill(0, count($fields), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$values})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_values($fields));
        
        $id = $this->pdo->lastInsertId();
        return $this->findById($id);
    }
    
    public function update($id, $data) {
        $fields = array_intersect_key($data, array_flip($this->fillable));
        $set = implode(', ', array_map(function($field) {
            return "{$field} = ?";
        }, array_keys($fields)));
        
        $sql = "UPDATE {$this->table} SET {$set} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([...array_values($fields), $id]);
        
        return $this->findById($id);
    }
    
    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function findAll($options = []) {
        $defaultOptions = [
            'page' => 1,
            'limit' => 10,
            'where' => '1',
            'params' => []
        ];
        
        $options = array_merge($defaultOptions, $options);
        $offset = ($options['page'] - 1) * $options['limit'];
        
        $sql = "SELECT * FROM {$this->table} 
                WHERE {$options['where']} 
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params = array_merge($options['params'], [$options['limit'], $offset]);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$options['where']}";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($options['params']);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get records
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $records,
            'pagination' => [
                'total' => (int)$total,
                'page' => $options['page'],
                'limit' => $options['limit'],
                'total_pages' => ceil($total / $options['limit'])
            ]
        ];
    }
}

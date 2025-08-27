<?php
require_once __DIR__ . '/../config/Database.php';

abstract class Model {
    protected $db;
    protected $table;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function findAll($options = []) {
        try {
            $page = isset($options['page']) ? (int)$options['page'] : 1;
            $limit = isset($options['limit']) ? (int)$options['limit'] : 10;
            $offset = ($page - 1) * $limit;
            
            $where = isset($options['where']) ? $options['where'] : '1';
            $params = isset($options['params']) ? $options['params'] : [];
            
            // Count total
            $countStmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE {$where}");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            
            // Get data
            $stmt = $this->db->prepare("
                SELECT * FROM {$this->table}
                WHERE {$where}
                ORDER BY created_at DESC
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
        } catch (PDOException $e) {
            error_log("Database error in findAll: " . $e->getMessage());
            Response::error('Database error occurred', 500);
        } catch (Exception $e) {
            error_log("Error in findAll: " . $e->getMessage());
            Response::error('An error occurred while processing your request', 500);
        }
    }
    
    public function create($data) {
        $fields = array_keys($data);
        $placeholders = str_repeat('?,', count($fields) - 1) . '?';
        $columns = implode(',', $fields);
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} ({$columns})
            VALUES ({$placeholders})
        ");
        
        $stmt->execute(array_values($data));
        return $this->findById($this->db->lastInsertId());
    }
    
    public function update($id, $data) {
        $fields = array_keys($data);
        $set = implode('=?,', $fields) . '=?';
        
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET {$set}
            WHERE id = ?
        ");
        
        $values = array_values($data);
        $values[] = $id;
        
        $stmt->execute($values);
        return $this->findById($id);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

<?php
require_once __DIR__ . '/Model.php';

class UserModel extends Model {
    protected $table = 'users';
    
    public function findByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    public function create($data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        return parent::create($data);
    }
    
    public function update($id, $data) {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }
        return parent::update($id, $data);
    }
    
    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}

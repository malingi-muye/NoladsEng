<?php
require_once __DIR__ . '/BaseModel.php';

class BlogCategoryModel extends BaseModel {
    protected $table = 'blog_categories';
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active'
    ];

    public function getAllWithPostCount() {
        $sql = "SELECT c.*, COUNT(p.id) as post_count 
                FROM {$this->table} c 
                LEFT JOIN blog_posts p ON c.slug = p.category AND p.is_published = true 
                WHERE c.is_active = true 
                GROUP BY c.id 
                ORDER BY c.name ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

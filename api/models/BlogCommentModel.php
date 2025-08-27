<?php
require_once __DIR__ . '/BaseModel.php';

class BlogCommentModel extends BaseModel {
    protected $table = 'blog_comments';
    protected $fillable = [
        'post_id',
        'user_id',
        'author_name',
        'author_email',
        'content',
        'is_approved'
    ];

    public function findByPost($postId) {
        $sql = "SELECT c.*, 
                COALESCE(u.name, c.author_name) as commenter_name,
                COALESCE(u.email, c.author_email) as commenter_email
                FROM {$this->table} c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = ? AND c.is_approved = true 
                ORDER BY c.created_at DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$postId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

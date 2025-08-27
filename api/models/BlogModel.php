<?php
require_once __DIR__ . '/BaseModel.php';

class BlogModel extends BaseModel {
    protected $table = 'blog_posts';
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'category',
        'author_id',
        'is_published',
        'is_featured',
        'published_at',
        'meta_title',
        'meta_description',
        'views'
    ];

    public function findAllWithAuthor($options = []) {
        $defaultOptions = [
            'page' => 1,
            'limit' => 10,
            'where' => '1',
            'params' => []
        ];
        
        $options = array_merge($defaultOptions, $options);
        $offset = ($options['page'] - 1) * $options['limit'];
        
        $sql = "SELECT p.*, u.name as author_name, u.email as author_email 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE {$options['where']} 
                ORDER BY p.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $params = array_merge($options['params'], [$options['limit'], $offset]);
        
        // Get total count for pagination
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} p WHERE {$options['where']}";
        $stmt = $this->pdo->prepare($countSql);
        $stmt->execute($options['params']);
        $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get posts
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'data' => $posts,
            'pagination' => [
                'total' => (int)$total,
                'page' => $options['page'],
                'limit' => $options['limit'],
                'total_pages' => ceil($total / $options['limit'])
            ]
        ];
    }
    
    public function findBySlugWithAuthor($slug) {
        $sql = "SELECT p.*, u.name as author_name, u.email as author_email 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.author_id = u.id 
                WHERE p.slug = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function incrementViews($id) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function countByCategory($category) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE category = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$category]);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['count'];
    }

    public function getPopularTags($limit = 10) {
        $sql = "
            SELECT 
                JSON_UNQUOTE(JSON_EXTRACT(tags, '$[*]')) as tag,
                COUNT(*) as count
            FROM {$this->table},
                JSON_TABLE(
                    tags,
                    '$[*]' COLUMNS(tag VARCHAR(255) PATH '$')
                ) tags
            WHERE is_published = 1
            GROUP BY tag
            ORDER BY count DESC
            LIMIT ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRelatedPosts($postId, $limit = 3) {
        $sql = "
            WITH post_tags AS (
                SELECT tags
                FROM {$this->table}
                WHERE id = ?
            )
            SELECT p.*, COUNT(*) as tag_matches
            FROM {$this->table} p, post_tags pt,
                JSON_TABLE(
                    p.tags,
                    '$[*]' COLUMNS(tag VARCHAR(255) PATH '$')
                ) tags
            WHERE p.id != ?
                AND p.is_published = 1
                AND JSON_CONTAINS(pt.tags, JSON_ARRAY(tags.tag))
            GROUP BY p.id
            ORDER BY tag_matches DESC, p.views DESC
            LIMIT ?
        ";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$postId, $postId, $limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePostStats($id, $readTime = null) {
        $updates = [];
        $params = [];
        
        if ($readTime !== null) {
            $updates[] = 'read_time = ?';
            $params[] = $readTime;
        }
        
        if (empty($updates)) {
            return true;
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
}

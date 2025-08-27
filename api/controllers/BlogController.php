<?php
require_once __DIR__ . '/../lib/Response.php';
require_once __DIR__ . '/../lib/Auth.php';
require_once __DIR__ . '/../models/BlogModel.php';
require_once __DIR__ . '/../models/BlogCategoryModel.php';
require_once __DIR__ . '/../models/BlogCommentModel.php';

class BlogController {
    private $auth;
    private $blogModel;
    private $categoryModel;
    private $commentModel;
    
    public function __construct() {
        $this->auth = new Auth();
        $this->blogModel = new BlogModel();
        $this->categoryModel = new BlogCategoryModel();
        $this->commentModel = new BlogCommentModel();
    }
    
    public function list() {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        $featured = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null;
        $tag = isset($_GET['tag']) ? $_GET['tag'] : '';
        $sortBy = isset($_GET['sortBy']) ? $_GET['sortBy'] : 'published_at';
        $sortOrder = isset($_GET['sortOrder']) ? strtoupper($_GET['sortOrder']) : 'DESC';
        
        $where = ['is_published = ?'];
        $params = [true];
        
        if ($search) {
            $where[] = '(title LIKE ? OR excerpt LIKE ? OR content LIKE ? OR tags LIKE ?)';
            $params = array_merge($params, array_fill(0, 4, "%{$search}%"));
        }
        
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        
        if ($tag) {
            $where[] = 'JSON_CONTAINS(tags, ?)';
            $params[] = json_encode($tag);
        }
        
        if ($featured !== null) {
            $where[] = 'is_featured = ?';
            $params[] = $featured;
        }
        
        $result = $this->blogModel->findAllWithAuthor([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function listAll() {
        $this->auth->requireAdmin();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        
        $where = ['1'];
        $params = [];
        
        if ($search) {
            $where[] = '(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)';
            $params = array_fill(0, 3, "%{$search}%");
        }
        
        if ($category) {
            $where[] = 'category = ?';
            $params[] = $category;
        }
        
        $result = $this->blogModel->findAllWithAuthor([
            'page' => $page,
            'limit' => $limit,
            'where' => implode(' AND ', $where),
            'params' => $params
        ]);
        
        Response::json($result);
    }
    
    public function getBySlug($slug) {
        $post = $this->blogModel->findBySlugWithAuthor($slug);
        
        if (!$post) {
            Response::error('Post not found', 404);
        }
        
        if (!$post['is_published']) {
            $this->auth->requireAdmin();
        }
        
        // Increment views
        $this->blogModel->incrementViews($post['id']);
        
        // Get related posts
        $relatedPosts = $this->blogModel->getRelatedPosts($post['id'], 3);

        // Calculate read time if not set
        if (!$post['read_time']) {
            $readTime = $this->calculateReadTime($post['content']);
            $this->blogModel->updatePostStats($post['id'], $readTime);
            $post['read_time'] = $readTime;
        }

        Response::json([
            'post' => $post,
            'related' => $relatedPosts,
            'category' => [
                'posts_count' => $this->blogModel->countByCategory($post['category']),
                'latest_posts' => $this->blogModel->findAllWithAuthor([
                    'limit' => 3,
                    'where' => 'category = ? AND id != ? AND is_published = 1',
                    'params' => [$post['category'], $post['id']]
                ])
            ]
        ]);

    }

    // Helper: Calculate read time in minutes (average 200 words/minute)
    private function calculateReadTime($content) {
        $wordCount = str_word_count(strip_tags($content));
        return max(1, ceil($wordCount / 200));
    }
    
    public function create() {
        $user = $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['title']) || !isset($data['content'])) {
            Response::error('Title and content are required');
        }
        
        // Generate slug if not provided
        if (!isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        // Set author
        $data['author_id'] = $user['id'];
        
        // Set published_at if post is published
        if (isset($data['is_published']) && $data['is_published']) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $post = $this->blogModel->create($data);
        Response::json($post);
    }
    
    public function update($id) {
        $this->auth->requireAdmin();
        
        $post = $this->blogModel->findById($id);
        
        if (!$post) {
            Response::error('Post not found', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Update slug if title changed
        if (isset($data['title']) && $data['title'] !== $post['title']) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        // Set published_at if post is being published
        if (isset($data['is_published']) && $data['is_published'] && !$post['is_published']) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }
        
        $post = $this->blogModel->update($id, $data);
        Response::json($post);
    }
    
    public function delete($id) {
        $this->auth->requireAdmin();
        
        $post = $this->blogModel->findById($id);
        
        if (!$post) {
            Response::error('Post not found', 404);
        }
        
        $this->blogModel->delete($id);
        Response::json(['message' => 'Post deleted successfully']);
    }
    
    public function getCategories() {
        $categories = $this->categoryModel->getAllWithPostCount();
        Response::json($categories);
    }
    
    public function createCategory() {
        $this->auth->requireAdmin();
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'])) {
            Response::error('Category name is required');
        }
        
        if (!isset($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        $category = $this->categoryModel->create($data);
        Response::json($category);
    }
    
    public function updateCategory($id) {
        $this->auth->requireAdmin();
        
        $category = $this->categoryModel->findById($id);
        
        if (!$category) {
            Response::error('Category not found', 404);
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (isset($data['name']) && $data['name'] !== $category['name']) {
            $data['slug'] = $this->generateSlug($data['name']);
        }
        
        $category = $this->categoryModel->update($id, $data);
        Response::json($category);
    }
    
    public function deleteCategory($id) {
        $this->auth->requireAdmin();
        
        $category = $this->categoryModel->findById($id);
        
        if (!$category) {
            Response::error('Category not found', 404);
        }
        
        // Check if category has posts
        $postsCount = $this->blogModel->countByCategory($category['slug']);
        
        if ($postsCount > 0) {
            Response::error('Cannot delete category with existing posts');
        }
        
        $this->categoryModel->delete($id);
        Response::json(['message' => 'Category deleted successfully']);
    }
    
    public function getComments($postId) {
        $comments = $this->commentModel->findByPost($postId);
        Response::json($comments);
    }
    
    public function createComment($postId) {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['content'])) {
            Response::error('Comment content is required');
        }
        
        $post = $this->blogModel->findById($postId);
        
        if (!$post || !$post['is_published']) {
            Response::error('Post not found', 404);
        }
        
        try {
            $user = $this->auth->requireAuth();
            $data['user_id'] = $user['id'];
            $data['is_approved'] = true; // Auto-approve authenticated users
        } catch (Exception $e) {
            // Guest comment
            if (!isset($data['author_name']) || !isset($data['author_email'])) {
                Response::error('Author name and email are required for guest comments');
            }
        }
        
        $data['post_id'] = $postId;
        $comment = $this->commentModel->create($data);
        Response::json($comment);
    }
    
    public function approveComment($id) {
        $this->auth->requireAdmin();
        
        $comment = $this->commentModel->findById($id);
        
        if (!$comment) {
            Response::error('Comment not found', 404);
        }
        
        $comment = $this->commentModel->update($id, ['is_approved' => true]);
        Response::json($comment);
    }
    
    public function deleteComment($id) {
        $this->auth->requireAdmin();
        
        $comment = $this->commentModel->findById($id);
        
        if (!$comment) {
            Response::error('Comment not found', 404);
        }
        
        $this->commentModel->delete($id);
        Response::json(['message' => 'Comment deleted successfully']);
    }
    
    private function generateSlug($title) {
        // Convert title to lowercase and replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
        $slug = trim($slug, '-');
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->blogModel->findBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}

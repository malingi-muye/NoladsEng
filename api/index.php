<?php
require_once __DIR__ . '/lib/Response.php';
require_once __DIR__ . '/lib/Auth.php';

// Enable CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Parse request
$request = parse_url($_SERVER['REQUEST_URI']);
$path = $request['path'];
$path = str_replace('/api', '', $path);
$method = $_SERVER['REQUEST_METHOD'];

// Routes
$routes = [
    // Auth routes
    'POST:/auth/login' => ['AuthController', 'login'],
    'POST:/auth/logout' => ['AuthController', 'logout'],
    'GET:/auth/verify' => ['AuthController', 'verify'],
    
    // Services routes
    'GET:/services' => ['ServicesController', 'list'],
    'GET:/services/featured' => ['ServicesController', 'featured'],
    'GET:/services/:id' => ['ServicesController', 'get'],
    'POST:/services' => ['ServicesController', 'create'],
    'PUT:/services/:id' => ['ServicesController', 'update'],
    'DELETE:/services/:id' => ['ServicesController', 'delete'],
    
    // Products routes
    'GET:/products' => ['ProductsController', 'list'],
    'GET:/products/featured' => ['ProductsController', 'featured'],
    'GET:/products/categories' => ['ProductsController', 'getCategories'],
    'GET:/products/:id' => ['ProductsController', 'get'],
    'GET:/products/:id/related' => ['ProductsController', 'related'],
    'POST:/products' => ['ProductsController', 'create'],
    'PUT:/products/:id' => ['ProductsController', 'update'],
    'PUT:/products/:id/stock' => ['ProductsController', 'updateStock'],
    'POST:/products/:id/stock-alert' => ['ProductsController', 'subscribeToStock'],
    'DELETE:/products/:id' => ['ProductsController', 'delete'],
    
    // Blog routes
    'GET:/blog' => ['BlogController', 'list'],
    'GET:/blog/all' => ['BlogController', 'listAll'],
    'GET:/blog/post/:slug' => ['BlogController', 'getBySlug'],
    'POST:/blog/post' => ['BlogController', 'create'],
    'PUT:/blog/post/:id' => ['BlogController', 'update'],
    'DELETE:/blog/post/:id' => ['BlogController', 'delete'],
    
    // Blog categories
    'GET:/blog/categories' => ['BlogController', 'getCategories'],
    'POST:/blog/categories' => ['BlogController', 'createCategory'],
    'PUT:/blog/categories/:id' => ['BlogController', 'updateCategory'],
    'DELETE:/blog/categories/:id' => ['BlogController', 'deleteCategory'],
    
    // Blog comments
    'GET:/blog/post/:postId/comments' => ['BlogController', 'getComments'],
    'POST:/blog/post/:postId/comments' => ['BlogController', 'createComment'],
    'PUT:/blog/comments/:id/approve' => ['BlogController', 'approveComment'],
    'DELETE:/blog/comments/:id' => ['BlogController', 'deleteComment'],
    
    // Company routes
    'GET:/company/info' => ['CompanyController', 'info'],
    'GET:/company/contact' => ['CompanyController', 'contact'],
    'GET:/company/stats' => ['CompanyController', 'stats'],
    'GET:/company/registration' => ['CompanyController', 'registration'],
    'PUT:/company/info' => ['CompanyController', 'updateInfo'],
    'PUT:/company/contact' => ['CompanyController', 'updateContact'],
    'PUT:/company/registration' => ['CompanyController', 'updateRegistration'],

    // Contact routes
    'GET:/contact' => ['ContactController', 'list'],
    'GET:/contact/statistics' => ['ContactController', 'statistics'],
    'GET:/contact/:id' => ['ContactController', 'get'],
    'POST:/contact' => ['ContactController', 'create'],
    'PUT:/contact/:id' => ['ContactController', 'update'],
    'PUT:/contact/:id/read' => ['ContactController', 'markAsRead'],
    'PUT:/contact/:id/replied' => ['ContactController', 'markAsReplied'],
    'DELETE:/contact/:id' => ['ContactController', 'delete'],

    // Quotes routes
    'GET:/quotes' => ['QuotesController', 'list'],
    'GET:/quotes/statistics' => ['QuotesController', 'statistics'],
    'GET:/quotes/:id' => ['QuotesController', 'get'],
    'POST:/quotes' => ['QuotesController', 'create'],
    'PUT:/quotes/:id' => ['QuotesController', 'update'],
    'DELETE:/quotes/:id' => ['QuotesController', 'delete'],

    // Testimonials routes
    'GET:/testimonials' => ['TestimonialsController', 'list'],
    'GET:/testimonials/featured' => ['TestimonialsController', 'featured'],
    'GET:/testimonials/statistics' => ['TestimonialsController', 'statistics'],
    'GET:/testimonials/:id' => ['TestimonialsController', 'get'],
    'POST:/testimonials' => ['TestimonialsController', 'create'],
    'PUT:/testimonials/:id' => ['TestimonialsController', 'update'],
    'PUT:/testimonials/:id/featured' => ['TestimonialsController', 'setFeatured'],
    'DELETE:/testimonials/:id' => ['TestimonialsController', 'delete'],

    // Images routes
    'GET:/images' => ['ImagesController', 'list'],
    'GET:/images/stats' => ['ImagesController', 'stats'],
    'GET:/images/:id' => ['ImagesController', 'get'],
    'GET:/images/entity/:entityType/:entityId' => ['ImagesController', 'getByEntity'],
    'POST:/images/upload' => ['ImagesController', 'upload'],
    'POST:/images/upload-multiple' => ['ImagesController', 'uploadMultiple'],
    'PUT:/images/:id' => ['ImagesController', 'update'],
    'DELETE:/images/:id' => ['ImagesController', 'delete'],
    'POST:/images/cleanup' => ['ImagesController', 'cleanup'],
];

// Route matching
function matchRoute($method, $path, $routes) {
    foreach ($routes as $route => $handler) {
        [$routeMethod, $routePath] = explode(':', $route);
        
        if ($routeMethod !== $method) {
            continue;
        }
        
        $pattern = preg_replace('/:[\w]+/', '([^/]+)', $routePath);
        $pattern = '@^' . $pattern . '$@';
        
        if (preg_match($pattern, $path, $matches)) {
            array_shift($matches);
            return [$handler, $matches];
        }
    }
    return null;
}

// Handle request
$match = matchRoute($method, $path, $routes);

if (!$match) {
    Response::error('Not Found', 404);
}

[$handler, $params] = $match;
[$controllerName, $action] = $handler;

$controllerFile = __DIR__ . "/controllers/{$controllerName}.php";
if (!file_exists($controllerFile)) {
    Response::error('Controller not found', 500);
}

require_once $controllerFile;
$controller = new $controllerName();
call_user_func_array([$controller, $action], $params);

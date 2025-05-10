<?php
/**
 * Simple Router for handling URL routing
 */
class Router {
    private static $instance = null;
    private $routes = [];
    private $notFoundCallback;

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize routes array
        $this->routes = [
            'GET' => [],
            'POST' => []
        ];
    }

    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Add a GET route
     */
    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
        return $this;
    }

    /**
     * Add a POST route
     */
    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
        return $this;
    }

    /**
     * Set 404 Not Found handler
     */
    public function notFound($callback) {
        $this->notFoundCallback = $callback;
        return $this;
    }

    /**
     * Resolve the current route
     */
    public function resolve() {
        // Get current request method and URI
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Debug information
        error_log("Original URI: " . $uri);

        // Extract the path from the URI (remove query string)
        $path = parse_url($uri, PHP_URL_PATH);
        error_log("Path: " . $path);

        // Remove the script name and directory from the path
        $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
        error_log("Script Dir: " . $scriptDir);

        if ($scriptDir !== '/' && strpos($path, $scriptDir) === 0) {
            $path = substr($path, strlen($scriptDir));
        }

        // Remove any leading/trailing slashes
        $path = trim($path, '/');
        error_log("Cleaned Path: " . $path);

        // Default to home page if path is empty
        if ($path === '') {
            $path = '/';
        } else {
            $path = '/' . $path;
        }

        error_log("Final Path for Routing: " . $path);

        // Set the URI to the cleaned path
        $uri = $path;

        // Check if route exists
        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];

            // Execute callback
            if (is_callable($callback)) {
                return call_user_func($callback);
            } elseif (is_array($callback) && count($callback) === 2) {
                // Controller method
                list($controller, $action) = $callback;

                if (class_exists($controller)) {
                    $controllerInstance = new $controller();

                    if (method_exists($controllerInstance, $action)) {
                        return call_user_func([$controllerInstance, $action]);
                    }
                }
            }
        } else {
            // Check for routes with parameters
            foreach ($this->routes[$method] as $route => $callback) {
                // Convert route to regex pattern
                $pattern = preg_replace('/\/:([^\/]+)/', '/(?P<$1>[^/]+)', $route);
                $pattern = "@^" . $pattern . "$@D";

                // Check if the URI matches the pattern
                if (preg_match($pattern, $uri, $matches)) {
                    // Remove the full match
                    array_shift($matches);

                    // Execute callback with parameters
                    return call_user_func_array($callback, $matches);
                }
            }
        }

        // Route not found, execute 404 handler
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }

        // Default 404 response
        header("HTTP/1.0 404 Not Found");
        echo "404 - Page Not Found";
    }
}

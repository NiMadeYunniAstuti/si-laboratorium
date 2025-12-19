<?php

/**
 * Simple Router Class
 */
class Router
{
    private $routes = [];
    private $currentRoute = '';

    /**
     * Add a route
     */
    public function addRoute($method, $path, $controller, $action, $middleware = [])
    {
        $this->routes[] = [
            'method' => strtoupper($method),
            'path' => $path,
            'controller' => $controller,
            'action' => $action,
            'middleware' => $middleware
        ];
    }

    /**
     * Add GET route
     */
    public function get($path, $controller, $action, $middleware = [])
    {
        $this->addRoute('GET', $path, $controller, $action, $middleware);
    }

    /**
     * Add POST route
     */
    public function post($path, $controller, $action, $middleware = [])
    {
        $this->addRoute('POST', $path, $controller, $action, $middleware);
    }

    /**
     * Add PUT route
     */
    public function put($path, $controller, $action, $middleware = [])
    {
        $this->addRoute('PUT', $path, $controller, $action, $middleware);
    }

    /**
     * Add DELETE route
     */
    public function delete($path, $controller, $action, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $controller, $action, $middleware);
    }

    /**
     * Dispatch the request
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove query string from request URI
        $requestUri = strtok($requestUri, '?');

        // Find matching route
        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                $this->currentRoute = $route['path'];

                // Execute middleware
                foreach ($route['middleware'] as $middleware) {
                    if (class_exists($middleware)) {
                        $middlewareInstance = new $middleware();
                        if (!$middlewareInstance->handle()) {
                            return;
                        }
                    }
                }

                // Load and execute controller
                $controllerFile = __DIR__ . '/../Controllers/' . $route['controller'] . '.php';
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;

                    $controllerClass = $route['controller'];
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();

                        // Extract parameters from URL
                        $params = $this->extractParams($route['path'], $requestUri);

                        if (method_exists($controller, $route['action'])) {
                            $controller->{$route['action']}(...$params);
                            return;
                        }
                    }
                }

                $this->handle404("Controller or action not found");
                return;
            }
        }

        $this->handle404("No route found");
    }

    /**
     * Match URL path with route pattern
     */
    private function matchPath($routePath, $requestPath)
    {
        // Convert route path to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $requestPath);
    }

    /**
     * Extract parameters from URL path
     */
    private function extractParams($routePath, $requestPath)
    {
        // Convert route path to regex to capture parameters
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            // Remove the full match and return captured groups
            array_shift($matches);
            return $matches;
        }

        return [];
    }

    /**
     * Handle 404 errors
     */
    private function handle404($message = 'Page not found')
    {
        http_response_code(404);

        if (Config::APP_DEBUG) {
            echo "<h1>404 - Not Found</h1>";
            echo "<p>" . htmlspecialchars($message) . "</p>";
        } else {
            $this->renderView('errors/404', [
                'message' => $message
            ]);
        }
        exit;
    }

    /**
     * Render a view
     */
    public function renderView($view, $data = [])
    {
        extract($data);

        $viewPath = __DIR__ . '/../Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            echo "View not found: " . $view;
        }
    }

    /**
     * Get current route
     */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    /**
     * Redirect to a URL
     */
    public function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /**
     * Get URL parameters from route
     */
    public function getParams()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUri = strtok($requestUri, '?');

        foreach ($this->routes as $route) {
            if ($this->matchPath($route['path'], $requestUri)) {
                $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['path']);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches); // Remove full match

                    // Get parameter names from route
                    preg_match_all('/\{([^}]+)\}/', $route['path'], $paramNames);

                    return array_combine($paramNames[1], $matches);
                }
            }
        }

        return [];
    }
}
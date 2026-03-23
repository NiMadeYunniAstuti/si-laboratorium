<?php

/**
 * Kelas Router sederhana
 * Mencocokkan URL request dengan route yang sudah didaftarkan,
 * lalu menjalankan controller dan method yang sesuai
 */
class Router
{
    private $routes = [];
    private $currentRoute = '';

    /** Daftarkan route baru */
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

    /** Daftarkan route GET */
    public function get($path, $controller, $action = null, $middleware = [])
    {
        if ($controller instanceof Closure) {
            $this->addRoute('GET', $path, null, $controller, $middleware);
        } else {
            $this->addRoute('GET', $path, $controller, $action, $middleware);
        }
    }

    /** Daftarkan route POST */
    public function post($path, $controller, $action = null, $middleware = [])
    {
        if ($controller instanceof Closure) {
            $this->addRoute('POST', $path, null, $controller, $middleware);
        } else {
            $this->addRoute('POST', $path, $controller, $action, $middleware);
        }
    }

    /** Daftarkan route PUT */
    public function put($path, $controller, $action, $middleware = [])
    {
        $this->addRoute('PUT', $path, $controller, $action, $middleware);
    }

    /** Daftarkan route DELETE */
    public function delete($path, $controller, $action, $middleware = [])
    {
        $this->addRoute('DELETE', $path, $controller, $action, $middleware);
    }

    /**
     * Proses request yang masuk
     * Cocokkan URL dengan route, jalankan middleware, lalu panggil controller
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $requestUri = strtok($requestUri, '?');

        foreach ($this->routes as $route) {
            if ($route['method'] === $requestMethod && $this->matchPath($route['path'], $requestUri)) {
                $this->currentRoute = $route['path'];

                // Jalankan middleware (misal: cek login)
                foreach ($route['middleware'] as $middleware) {
                    if ($middleware === 'auth') {
                        if (session_status() === PHP_SESSION_NONE) {
                            session_start();
                        }

                        // Kalau belum login, redirect ke logout
                        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
                            header('Location: /logout');
                            exit;
                        }

                        // Cek apakah akun masih aktif
                        if (!empty($_SESSION['user_id'])) {
                            $userModel = new UserModel();
                            $statusRow = $userModel->getStatusById($_SESSION['user_id']);
                            $status = strtoupper($statusRow['status'] ?? 'INACTIVE');

                            if ($status !== 'ACTIVE') {
                                session_unset();
                                session_destroy();
                                session_start();
                                $_SESSION['error'] = $status === 'BLACKLIST'
                                    ? 'Akun Anda di-blacklist. Silakan hubungi administrator.'
                                    : 'Akun Anda tidak aktif. Silakan hubungi administrator.';
                                header('Location: /login');
                                exit;
                            }
                        }
                    }
                }

                // Kalau action-nya berupa fungsi langsung (Closure)
                if ($route['action'] instanceof Closure) {
                    $params = $this->extractParams($route['path'], $requestUri);
                    call_user_func_array($route['action'], $params);
                    return;
                }

                // Kalau action-nya berupa controller + method
                if (!empty($route['controller'])) {
                    $controllerFile = __DIR__ . '/../Controllers/' . $route['controller'] . '.php';
                    if (file_exists($controllerFile)) {
                        require_once $controllerFile;

                        $controllerClass = $route['controller'];
                        if (class_exists($controllerClass)) {
                            $controller = new $controllerClass();

                            $params = $this->extractParams($route['path'], $requestUri);

                            if (method_exists($controller, $route['action'])) {
                                $controller->{$route['action']}(...$params);
                                return;
                            }
                        }
                    }
                }

                $this->handle404("Controller or action not found");
                return;
            }
        }

        $this->handle404("No route found");
    }

    /** Cocokkan pola URL route dengan URL yang diminta */
    private function matchPath($routePath, $requestPath)
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        return preg_match($pattern, $requestPath);
    }

    /** Ambil parameter dari URL (misal: /users/{id} → id = 5) */
    private function extractParams($routePath, $requestPath)
    {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (preg_match($pattern, $requestPath, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return [];
    }

    /** Tampilkan halaman 404 (tidak ditemukan) */
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

    /** Render file view PHP dan kirim datanya */
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

    /** Ambil route yang sedang aktif */
    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    /** Redirect ke URL tertentu */
    public function redirect($url, $statusCode = 302)
    {
        header('Location: ' . $url, true, $statusCode);
        exit;
    }

    /** Ambil parameter dari URL berdasarkan nama */
    public function getParams()
    {
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $requestUri = strtok($requestUri, '?');

        foreach ($this->routes as $route) {
            if ($this->matchPath($route['path'], $requestUri)) {
                $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route['path']);
                $pattern = '#^' . $pattern . '$#';

                if (preg_match($pattern, $requestUri, $matches)) {
                    array_shift($matches);

                    preg_match_all('/\{([^}]+)\}/', $route['path'], $paramNames);

                    return array_combine($paramNames[1], $matches);
                }
            }
        }

        return [];
    }
}

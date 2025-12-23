<?php

/**
 * Base Controller Class
 */
class BaseController
{
    protected $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    /**
     * Render a view with data
     */
    protected function view($view, $data = [])
    {
        $data['app_name'] = Config::APP_NAME;
        $data['app_version'] = Config::APP_VERSION;
        $data['current_route'] = $this->router->getCurrentRoute();

        $this->router->renderView($view, $data);
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /**
     * Redirect to another URL
     */
    protected function redirect($url, $statusCode = 302)
    {
        $this->router->redirect($url, $statusCode);
    }

    /**
     * Get POST data
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /**
     * Get request input (POST or GET)
     */
    protected function input($key = null, $default = null)
    {
        $post = $this->post();
        $get = $this->get();

        if ($key === null) {
            return array_merge($get, $post);
        }

        return $post[$key] ?? $get[$key] ?? $default;
    }

    /**
     * Validate required fields
     */
    protected function validate($fields, $data = null)
    {
        $data = $data ?? $this->input();
        $errors = [];

        foreach ($fields as $field => $rules) {
            $value = $data[$field] ?? null;
            $fieldName = is_array($rules) ? ($rules['name'] ?? $field) : $field;
            $fieldRules = is_array($rules) ? $rules : ['required' => true];

            if (isset($fieldRules['required']) && $fieldRules['required'] && empty($value)) {
                $errors[$field] = "$fieldName is required";
            }

            if (!empty($value)) {
                if (isset($fieldRules['min']) && strlen($value) < $fieldRules['min']) {
                    $errors[$field] = "$fieldName must be at least {$fieldRules['min']} characters";
                }

                if (isset($fieldRules['max']) && strlen($value) > $fieldRules['max']) {
                    $errors[$field] = "$fieldName must not exceed {$fieldRules['max']} characters";
                }

                if (isset($fieldRules['email']) && $fieldRules['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "$fieldName must be a valid email address";
                }

                if (isset($fieldRules['pattern']) && !preg_match($fieldRules['pattern'], $value)) {
                    $errors[$field] = "$fieldName format is invalid";
                }
            }
        }

        return $errors;
    }

    /**
     * Check if request is AJAX
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Check if request is POST
     */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Check if request is GET
     */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Set flash message
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash messages
     */
    protected function getFlash()
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Get route parameters
     */
    protected function getParams()
    {
        return $this->router->getParams();
    }

    /**
     * Get current user (implement based on your auth system)
     */
    protected function getCurrentUser()
    {
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            return [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? null,
                'email' => $_SESSION['user_email'] ?? null,
                'role' => $_SESSION['user_role'] ?? null
            ];
        }
        return null;
    }

    /**
     * Check if user is logged in
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true &&
               !empty($_SESSION['user_id']) && !empty($_SESSION['user_email']);
    }

    /**
     * Require authentication
     */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/logout');
        }
    }

    /**
     * Get current user data for views
     */
    protected function getUser()
    {
        if ($this->isLoggedIn()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role']
            ];
        }
        return null;
    }
}
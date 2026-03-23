<?php

/**
 * Kelas dasar untuk semua Controller
 * Menyediakan fungsi umum seperti render view, redirect, validasi, dll
 */
class BaseController
{
    protected $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    /** Tampilkan halaman view beserta datanya */
    protected function view($view, $data = [])
    {
        $data['app_name'] = Config::APP_NAME;
        $data['app_version'] = Config::APP_VERSION;
        $data['current_route'] = $this->router->getCurrentRoute();

        $this->router->renderView($view, $data);
    }

    /** Kirim response dalam format JSON */
    protected function json($data, $statusCode = 200)
    {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    /** Arahkan pengguna ke URL lain */
    protected function redirect($url, $statusCode = 302)
    {
        $this->router->redirect($url, $statusCode);
    }

    /** Ambil data dari form POST */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? $default;
    }

    /** Ambil data dari parameter URL (GET) */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? $default;
    }

    /** Ambil data dari POST atau GET */
    protected function input($key = null, $default = null)
    {
        $post = $this->post();
        $get = $this->get();

        if ($key === null) {
            return array_merge($get, $post);
        }

        return $post[$key] ?? $get[$key] ?? $default;
    }

    /** Validasi field yang wajib diisi */
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

    /** Cek apakah request ini AJAX */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /** Cek apakah request ini POST */
    protected function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /** Cek apakah request ini GET */
    protected function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /** Simpan pesan flash ke session (muncul sekali lalu hilang) */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'][$type] = $message;
    }

    /** Ambil pesan flash dari session */
    protected function getFlash()
    {
        $flash = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flash;
    }

    /** Ambil parameter dari route URL */
    protected function getParams()
    {
        return $this->router->getParams();
    }

    /** Ambil data user yang sedang login */
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

    /** Cek apakah user sudah login */
    protected function isLoggedIn()
    {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true &&
               !empty($_SESSION['user_id']) && !empty($_SESSION['user_email']);
    }

    /** Paksa user harus login, kalau belum langsung redirect */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/logout');
        }
    }

    /** Paksa hanya admin yang boleh akses */
    protected function requireAdmin()
    {
        if (!$this->isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'ADMIN') {
            $_SESSION['error'] = 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.';
            $this->redirect('/dashboard');
            exit;
        }
    }

    /** Ambil data user untuk ditampilkan di view */
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

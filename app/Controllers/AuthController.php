<?php

/**
 * Auth Controller
 */
class AuthController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    /**
     * Show login page
     */
    public function login()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Login - LBMS',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('auth/login', $data);
    }

    /**
     * Handle login request
     */
    public function doLogin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = 'Email and password are required';
            $this->redirect('/login');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Invalid email format';
            $this->redirect('/login');
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user['password_hash'])) {
                $status = strtoupper($user['status'] ?? 'INACTIVE');

                if ($status === 'BLACKLIST') {
                    $_SESSION['error'] = 'Akun Anda di-blacklist. Silakan hubungi administrator.';
                    $this->redirect('/login');
                    return;
                }

                if ($status !== 'ACTIVE') {
                    $_SESSION['error'] = 'Akun Anda tidak aktif. Silakan hubungi administrator.';
                    $this->redirect('/login');
                    return;
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();

                $this->userModel->updateLastLogin($user['id']);

                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/'); 
                }

                $this->redirect('/dashboard');
                return;
            } else {
                $_SESSION['error'] = 'Invalid email or password';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred during login. Please try again.';
        }

        $this->redirect('/login');
    }

    /**
     * Show register page
     */
    public function register()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Register - LBMS',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('auth/register', $data);
    }

    /**
     * Handle register request
     */
    public function doRegister()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = $_POST['role'] ?? 'USER';

        $errors = [];

        if (empty($name)) {
            $errors[] = 'Full name is required';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Full name must be at least 3 characters long';
        }

        if (empty($email)) {
            $errors[] = 'Email address is required';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors[] = 'Password is required';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (!in_array($role, ['USER', 'ADMIN'])) {
            $errors[] = 'Invalid account type selected';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/register');
            return;
        }

        try {
            if ($this->userModel->emailExists($email)) {
                $_SESSION['error'] = 'Email address is already registered';
                $this->redirect('/register');
                return;
            }

            $userData = [
                'name' => htmlspecialchars($name),
                'email' => strtolower($email),
                'password' => $password,
                'role' => $role,
                'status' => 'ACTIVE'
            ];

            $userId = $this->userModel->createUser($userData);

            if ($userId) {
                $_SESSION['success'] = 'Registration successful! You can now login.';
                $this->redirect('/login');
            } else {
                $_SESSION['error'] = 'Registration failed. Please try again.';
                $this->redirect('/register');
            }

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred during registration. Please try again.';
            $this->redirect('/register');
        }
    }

    /**
     * Handle logout request
     */
    public function logout()
    {
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            unset($_COOKIE['remember_token']);
        }

        session_unset();
        session_destroy();

        session_start();
        $_SESSION['success'] = 'You have been logged out successfully';

        $this->redirect('/login');
    }

    /**
     * Show forgot password page
     */
    public function forgotPassword()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Forgot Password - LBMS',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('auth/forgot-password', $data);
    }

    /**
     * Handle forgot password request
     */
    public function doForgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            $this->redirect('/forgot-password');
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));


                $_SESSION['success'] = 'Password reset instructions have been sent to your email address';
            } else {
                $_SESSION['success'] = 'If an account with that email exists, password reset instructions have been sent';
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $_SESSION['error'] = 'An error occurred. Please try again.';
        }

        $this->redirect('/forgot-password');
    }

    /**
     * Check if user is logged in (for AJAX requests)
     */
    public function checkAuth()
    {
        header('Content-Type: application/json');

        if ($this->isLoggedIn()) {
            echo json_encode([
                'authenticated' => true,
                'user' => [
                    'id' => $_SESSION['user_id'],
                    'name' => $_SESSION['user_name'],
                    'email' => $_SESSION['user_email'],
                    'role' => $_SESSION['user_role']
                ]
            ]);
        } else {
            echo json_encode(['authenticated' => false]);
        }
        exit;
    }
}
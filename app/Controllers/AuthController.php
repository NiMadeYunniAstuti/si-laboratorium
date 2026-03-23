<?php

/**
 * Controller untuk autentikasi (login, register, logout)
 */
class AuthController extends BaseController
{
    private $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    /** Tampilkan halaman login */
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

    /** Proses login user */
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
            $_SESSION['error'] = 'Email dan password harus diisi';
            $this->redirect('/login');
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Format email tidak valid';
            $this->redirect('/login');
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if ($user && $this->userModel->verifyPassword($password, $user['password_hash'])) {
                $status = strtoupper($user['status'] ?? 'INACTIVE');

                // Cek apakah akun di-blacklist
                if ($status === 'BLACKLIST') {
                    $_SESSION['error'] = 'Akun Anda di-blacklist. Silakan hubungi administrator.';
                    $this->redirect('/login');
                    return;
                }

                // Cek apakah akun aktif
                if ($status !== 'ACTIVE') {
                    $_SESSION['error'] = 'Akun Anda tidak aktif. Silakan hubungi administrator.';
                    $this->redirect('/login');
                    return;
                }

                // Simpan data user ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['logged_in'] = true;
                $_SESSION['login_time'] = time();

                $this->userModel->updateLastLogin($user['id']);

                // Simpan token "ingat saya" kalau dicentang
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/');
                }

                $this->redirect('/dashboard');
                return;
            } else {
                $_SESSION['error'] = 'Email atau password salah';
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat login. Silakan coba lagi.';
        }

        $this->redirect('/login');
    }

    /** Tampilkan halaman registrasi */
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

    /** Proses registrasi user baru */
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

        // Validasi input
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Nama lengkap wajib diisi';
        } elseif (strlen($name) < 3) {
            $errors[] = 'Nama lengkap minimal 3 karakter';
        }

        if (empty($email)) {
            $errors[] = 'Alamat email wajib diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        if (empty($password)) {
            $errors[] = 'Password wajib diisi';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Password dan konfirmasi password tidak cocok';
        }

        if (!in_array($role, ['USER', 'ADMIN'])) {
            $errors[] = 'Tipe akun tidak valid';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $this->redirect('/register');
            return;
        }

        try {
            // Cek apakah email sudah terdaftar
            if ($this->userModel->emailExists($email)) {
                $_SESSION['error'] = 'Email sudah terdaftar. Gunakan email lain.';
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
                $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
                $this->redirect('/login');
            } else {
                $_SESSION['error'] = 'Registrasi gagal. Silakan coba lagi.';
                $this->redirect('/register');
            }

        } catch (Exception $e) {
            error_log("Registration error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat registrasi. Silakan coba lagi.';
            $this->redirect('/register');
        }
    }

    /** Proses logout user */
    public function logout()
    {
        // Hapus cookie "ingat saya" kalau ada
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
            unset($_COOKIE['remember_token']);
        }

        session_unset();
        session_destroy();

        session_start();
        $_SESSION['success'] = 'Anda telah berhasil keluar';

        $this->redirect('/login');
    }

    /** Tampilkan halaman lupa password */
    public function forgotPassword()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/dashboard');
            return;
        }

        $data = [
            'title' => 'Lupa Password - LBMS',
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('auth/forgot-password', $data);
    }

    /** Proses permintaan reset password */
    public function doForgotPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/forgot-password');
            return;
        }

        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Masukkan alamat email yang valid';
            $this->redirect('/forgot-password');
            return;
        }

        try {
            $user = $this->userModel->findByEmail($email);

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $_SESSION['success'] = 'Instruksi reset password telah dikirim ke email Anda';
            } else {
                // Tetap tampilkan pesan sukses supaya tidak bocor info akun
                $_SESSION['success'] = 'Jika akun dengan email tersebut ada, instruksi reset password telah dikirim';
            }
        } catch (Exception $e) {
            error_log("Forgot password error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan. Silakan coba lagi.';
        }

        $this->redirect('/forgot-password');
    }

    /** Cek apakah user sudah login (untuk request AJAX) */
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

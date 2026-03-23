<?php

/**
 * Admin Controller
 */
class AdminController extends BaseController
{
    private $alatModel;
    private $ruanganModel;
    private $peminjamanModel;
    private $userModel;
    private $kategoriModel;
    private $notifikasiModel;


    public function __construct()
    {
        parent::__construct();
        $this->alatModel = new AlatModel();
        $this->ruanganModel = new RuanganModel();
        $this->peminjamanModel = new PeminjamanModel();
        $this->userModel = new UserModel();
        $this->kategoriModel = new KategoriModel();
        $this->notifikasiModel = new NotifikasiModel();

    }

    /**
     * Override view method to add notification count
     */
    protected function view($view, $data = [])
    {
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            try {
                $unreadCount = $this->notifikasiModel->getUnreadCount($userId);
                $data['unreadNotificationCount'] = $unreadCount;
            } catch (Exception $e) {
                $data['unreadNotificationCount'] = 0;
            }
        } else {
            $data['unreadNotificationCount'] = 0;
        }

        parent::view($view, $data);
    }

    /**
     * Show dashboard page
     */
    public function dashboard()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses dashboard';
            $this->redirect('/login');
            return;
        }

        $user = $this->getUser();
        $data = [
            'title' => 'Dashboard - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        try {
            $stats = $this->getDashboardStats();
            $data = array_merge($data, $stats);
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            $data['stats'] = $this->getDefaultStats();
        }

        try {
            $recentPeminjaman = $this->peminjamanModel->getRecentPeminjaman(10);
            $data['recentPeminjaman'] = $recentPeminjaman;

            error_log("Recent peminjaman count: " . count($recentPeminjaman));
        } catch (Exception $e) {
            error_log("Recent peminjaman error: " . $e->getMessage());
            $data['recentPeminjaman'] = [];
        }

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('dashboard/index', $data);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        try {
            $alatStats = $this->alatModel->getAlatStatistics();

            $userStats = $this->userModel->getUserStatistics();

            $peminjamanStats = $this->peminjamanModel->getPeminjamanStatistics();

            return [
                'totalAlat' => $alatStats['total'] ?? 0,
                'alatTersedia' => $alatStats['by_status']['tersedia'] ?? 0,
                'alatDipinjam' => $alatStats['by_status']['dipinjam'] ?? 0,
                'totalPeminjaman' => $peminjamanStats['total'] ?? 0,
                'totalUsers' => $userStats['total'] ?? 0,
                'activeUsers' => $userStats['active_last_30_days'] ?? 0,
                'peminjamanPending' => $peminjamanStats['by_status']['pending'] ?? 0,
                'peminjamanSelesai' => $peminjamanStats['by_status']['selesai'] ?? 0
            ];
        } catch (Exception $e) {
            return $this->getDefaultStats();
        }
    }

    /**
     * Get default statistics if data loading fails
     */
    private function getDefaultStats()
    {
        return [
            'totalAlat' => 0,
            'alatTersedia' => 0,
            'alatDipinjam' => 0,
            'totalPeminjaman' => 0,
            'totalUsers' => 0,
            'activeUsers' => 0
        ];
    }

    /**
     * Get recent activities for the dashboard
     */
    public function getRecentActivities()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $recentPeminjaman = $this->peminjamanModel->getPeminjamanByUser($userId, 'DIPINJAM', 5);

            echo json_encode([
                'success' => true,
                'activities' => $recentPeminjaman
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load activities'
            ]);
        }
        exit;
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function getStats()
    {
        header('Content-Type: application/json');


        try {
            $stats = $this->getDashboardStats();
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to load statistics'
            ]);
        }
        exit;
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            $userDetails = $this->userModel->getUserDetails($userId);

            $data = [
                'title' => 'Profil Saya - LBMS',
                'user' => $userDetails,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('dashboard/profile', $data);
        } catch (Exception $e) {
            error_log("Profile error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat profil';
            $this->redirect('/dashboard');
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile()
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
            return;
        }

        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        try {
            if (empty($name)) {
                $_SESSION['error'] = 'Nama tidak boleh kosong';
                $this->redirect('/settings/profile');
                return;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email tidak valid';
                $this->redirect('/settings/profile');
                return;
            }

            if ($this->userModel->emailExists($email, $userId)) {
                $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                $this->redirect('/settings/profile');
                return;
            }

            $userData = [
                'name' => htmlspecialchars($name),
                'email' => strtolower($email)
            ];

            if ($this->userModel->update($userId, $userData)) {
                $_SESSION['user_name'] = $userData['name'];
                $_SESSION['user_email'] = $userData['email'];

                $_SESSION['success'] = 'Profil berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui profil';
            }

        } catch (Exception $e) {
            error_log("Profile update error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat memperbarui profil';
        }

        $this->redirect('/settings/profile');
    }

    /**
     * Change password
     */
    public function changePassword()
    {
        if (!$this->isLoggedIn() || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
            return;
        }

        $userId = $_SESSION['user_id'];
        $email = trim($_POST['email'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        try {
            // Handle email update if provided
            $emailUpdated = false;
            if (!empty($email)) {
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['error'] = 'Email tidak valid';
                    $this->redirect('/settings/privacy-security');
                    return;
                }

                if ($this->userModel->emailExists($email, $userId)) {
                    $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                    $this->redirect('/settings/privacy-security');
                    return;
                }

                if ($this->userModel->update($userId, ['email' => strtolower($email)])) {
                    $_SESSION['user_email'] = strtolower($email);
                    $emailUpdated = true;
                }
            }

            // If no password fields filled, just return after email update
            if (empty($currentPassword) && empty($newPassword) && empty($confirmPassword)) {
                if ($emailUpdated) {
                    $_SESSION['success'] = 'Email berhasil diperbarui';
                }
                $this->redirect('/settings/privacy-security');
                return;
            }

            // Validate password fields
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = 'Semua field password harus diisi';
                $this->redirect('/settings/privacy-security');
                return;
            }

            $user = $this->userModel->find($userId);
            if (!$user) {
                $_SESSION['error'] = 'Pengguna tidak ditemukan';
                $this->redirect('/settings/privacy-security');
                return;
            }

            if (!password_verify($currentPassword, $user['password_hash'])) {
                $_SESSION['error'] = 'Password saat ini tidak benar';
                $this->redirect('/settings/privacy-security');
                return;
            }

            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = 'Password baru minimal 8 karakter';
                $this->redirect('/settings/privacy-security');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Password baru dan konfirmasi password tidak cocok';
                $this->redirect('/settings/privacy-security');
                return;
            }

            if ($this->userModel->updatePassword($userId, $newPassword)) {
                $_SESSION['success'] = 'Password berhasil diubah';
            } else {
                $_SESSION['error'] = 'Gagal mengubah password';
            }

        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat mengubah password';
        }

        $this->redirect('/settings/privacy-security');
    }

    /**
     * Show admin users management
     */
    public function users()
    {
        $this->requireAdmin();

        try {
            $page = $_GET['page'] ?? 1;
            $search = $_GET['search'] ?? '';
            $role = $_GET['role'] ?? '';
            $status = $_GET['status'] ?? '';

            $users = $this->userModel->getUsersPaginated($page, 10, $search, $role, $status);

            $data = [
                'title' => 'Kelola Pengguna - LBMS',
                'users' => $users,
                'search' => $search,
                'role' => $role,
                'status' => $status,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('admin/users', $data);
        } catch (Exception $e) {
            error_log("Users management error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat data pengguna';
            $this->redirect('/dashboard');
        }
    }

    /**
     * Create new user
     */
    public function createUser()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
            return;
        }

        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            $role = $_POST['role'] ?? 'USER';

            if (empty($name) || empty($email) || empty($password)) {
                $_SESSION['error'] = 'Semua field wajib diisi';
                $this->redirect('/users/new');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email tidak valid';
                $this->redirect('/users/new');
                return;
            }

            if ($password !== $confirmPassword) {
                $_SESSION['error'] = 'Password dan konfirmasi password tidak cocok';
                $this->redirect('/users/new');
                return;
            }

            if (empty($password)) {
                $_SESSION['error'] = 'Password tidak boleh kosong';
                $this->redirect('/users/new');
                return;
            }

            $userData = [
                'name' => htmlspecialchars($name),
                'email' => strtolower($email),
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role
            ];

            if ($this->userModel->create($userData)) {
                $_SESSION['success'] = 'User berhasil ditambahkan';
            } else {
                $_SESSION['error'] = 'Gagal menambahkan user';
            }

        } catch (Exception $e) {
            error_log("Create user error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat menambahkan user';
        }

        $this->redirect('/users');
    }

    /**
     * Update user
     */
    public function updateUser($userId)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
            return;
        }

        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $status = strtoupper($_POST['status'] ?? 'ACTIVE');

            if (empty($name) || empty($email)) {
                $_SESSION['error'] = 'Nama dan email wajib diisi';
                $this->redirect('/users/' . $userId);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email tidak valid';
                $this->redirect('/users/' . $userId);
                return;
            }

            $validStatuses = ['ACTIVE', 'INACTIVE', 'BLACKLIST'];
            if (!in_array($status, $validStatuses)) {
                $_SESSION['error'] = 'Status tidak valid';
                $this->redirect('/users/' . $userId);
                return;
            }

            if ($this->userModel->emailExists($email, $userId)) {
                $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                $this->redirect('/users/' . $userId);
                return;
            }

            $userData = [
                'name' => htmlspecialchars($name),
                'email' => strtolower($email),
                'status' => $status
            ];

            if ($this->userModel->update($userId, $userData)) {
                $_SESSION['success'] = 'Data user berhasil diperbarui';
            } else {
                $_SESSION['error'] = 'Gagal memperbarui data user';
            }

        } catch (Exception $e) {
            error_log("Update user error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat memperbarui data user';
        }

        $this->redirect('/users/' . $userId);
    }

    /**
     * Toggle user status
     */
    public function toggleUserStatus($userId)
    {
        $this->requireAdmin();

        if ($userId == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Tidak dapat mengubah status sendiri';
            $this->redirect('/users');
            return;
        }

        try {
            if ($this->userModel->toggleStatus($userId)) {
                $_SESSION['success'] = 'Status pengguna berhasil diubah';
            } else {
                $_SESSION['error'] = 'Gagal mengubah status pengguna';
            }
        } catch (Exception $e) {
            error_log("Toggle user status error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat mengubah status pengguna';
        }

        $this->redirect('/users');
    }

    /**
     * Update user status
     */
    public function updateUserStatus($userId)
    {
        header('Content-Type: application/json');

        $this->requireAdmin();

        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Tidak dapat mengubah status sendiri']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $status = strtoupper($input['status'] ?? '');

            $validStatuses = ['ACTIVE', 'INACTIVE', 'BLACKLIST'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                exit;
            }

            if ($this->userModel->updateStatus($userId, $status)) {
                $statusMessages = [
                    'ACTIVE' => 'User berhasil diaktifkan',
                    'INACTIVE' => 'User berhasil dinonaktifkan',
                    'BLACKLIST' => 'User berhasil di-blacklist'
                ];
                echo json_encode([
                    'success' => true,
                    'message' => $statusMessages[$status] ?? 'Status berhasil diperbarui'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status user']);
            }
        } catch (Exception $e) {
            error_log("Update user status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memperbarui status']);
        }
        exit;
    }

    /**
     * Show Data User page
     */
    public function dataUsers()
    {
        $this->requireAdmin();

        try {
            $result = $this->userModel->getUsersPaginated(1, 1000); 
            $usersList = $result['data'] ?? [];
        } catch (Exception $e) {
            error_log("Error fetching users data: " . $e->getMessage());
            $usersList = [];
        }

        $data = [
            'title' => 'Data User - LBMS',
            'user' => [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'users' => $usersList,
            'usersList' => $usersList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('users/index', $data);
    }

    /**
     * Show Manajemen Alat page
     */
    public function manajemenAlat()
    {
        $this->requireAdmin();
        try {
            $alatModel = new AlatModel();
            $result = $alatModel->getAlatPaginated(1, 1000); 
            $alatList = $result['data'] ?? [];
        } catch (Exception $e) {
            error_log("Error fetching alat data: " . $e->getMessage());
            $alatList = [];
        }

        $data = [
            'title' => 'Manajemen Alat - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'alat' => $alatList,
            'alatList' => $alatList,
            'ruanganList' => $ruanganList ?? [],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('alat/index', $data);
    }

    /**
     * Show new alat page
     */
    public function newAlat()
    {
        $this->requireAdmin();

        try {
            $kategoriList = $this->alatModel->getAllKategori();
        } catch (Exception $e) {
            error_log("Error fetching kategori data: " . $e->getMessage());
            $kategoriList = [];
        }

        $oldInput = $_SESSION['old_input'] ?? [];

        $data = [
            'title' => 'Tambah Alat Baru - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'kategoriList' => $kategoriList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'oldInput' => $oldInput  
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);
        unset($_SESSION['old_input']);

        $this->view('alat/new', $data);
    }

    /**
     * Show alat detail page
     */
    public function detailAlat($id)
    {
        $this->requireAdmin();

        $alatData = $this->alatModel->getAlatDetails($id);

        if (!$alatData) {
            $_SESSION['error'] = 'Alat tidak ditemukan';
            $this->redirect('/alat');
            return;
        }

        $data = [
            'title' => 'Detail Alat - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'alatDetail' => $alatData,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('alat/detail', $data);
    }

    /**
     * Show edit alat page
     */
    public function editAlat($id)
    {
        $this->requireAdmin();

        $alatData = $this->alatModel->getAlatDetails($id);

        if (!$alatData) {
            $_SESSION['error'] = 'Alat tidak ditemukan';
            $this->redirect('/alat');
            return;
        }

        try {
            $kategoriList = $this->alatModel->getAllKategori();
        } catch (Exception $e) {
            error_log("Error fetching kategori data: " . $e->getMessage());
            $kategoriList = [];
        }

        $data = [
            'title' => 'Edit Alat - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'alatDetail' => $alatData,
            'kategoriList' => $kategoriList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('alat/edit', $data);
    }

    /**
     * Show new user page
     */
    public function newUser()
    {
        $this->requireAdmin();

        $data = [
            'title' => 'Tambah User Baru - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('users/new', $data);
    }

    /**
     * Show edit user page
     */
    public function editUser($id)
    {
        $this->requireAdmin();

        $userData = $this->userModel->getUserDetails($id);

        if (!$userData) {
            $_SESSION['error'] = 'User tidak ditemukan';
            $this->redirect('/users');
            return;
        }

        $data = [
            'title' => 'Edit User - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'userDetail' => $userData,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('users/edit', $data);
    }

    /**
     * Show user detail page
     */
    public function detailUser($id)
    {
        $this->requireAdmin();

        $userData = $this->userModel->getUserDetails($id);

        if (!$userData) {
            $_SESSION['error'] = 'User tidak ditemukan';
            $this->redirect('/users');
            return;
        }

        $data = [
            'title' => 'Detail User - LBMS',
            'user' => [
                'id' => $_SESSION['user_id'] ?? null,
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'userDetail' => $userData,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('users/detail', $data);
    }

    /**
     * Create new alat
     */
    public function createAlat()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kode_alat = $_POST['kode_alat'] ?? '';

            try {
                $exists = $this->alatModel->kodeAlatExists($kode_alat);
                if ($exists) {
                    $_SESSION['error'] = "Kode alat '{$kode_alat}' sudah terdaftar. Silakan gunakan kode lain.";
                    $_SESSION['old_input'] = [
                        'kode_alat' => $_POST['kode_alat'] ?? '',
                        'nama_alat' => $_POST['nama_alat'] ?? '',
                        'kategori_id' => $_POST['kategori_id'] ?? null,
                        'tipe_id' => $_POST['tipe_id'] ?? null,
                        'tahun_pembelian' => $_POST['tahun_pembelian'] ?? null,
                        'status' => $_POST['status'] ?? 'TERSEDIA',
                        'deskripsi' => $_POST['deskripsi'] ?? '',
                'jumlah' => $_POST['stok'] ?? $_POST['jumlah'] ?? 1
                    ];
                    $this->redirect('/alat/new');
                    return;
                }
            } catch (Exception $e) {
                error_log("Error checking kode_alat: " . $e->getMessage());
            }

            $data = [
                'kode_alat' => $kode_alat,
                'nama_alat' => $_POST['nama_alat'] ?? '',
                'kategori_id' => $_POST['kategori_id'] ?? null,
                'tipe_id' => $_POST['tipe_id'] ?? null,
                'tahun_pembelian' => $_POST['tahun_pembelian'] ?? null,
                'status' => $_POST['status'] ?? 'TERSEDIA',
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'jumlah' => $_POST['stok'] ?? $_POST['jumlah'] ?? 1
            ];

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/upload/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                $fileName = 'alat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    $data['gambar'] = 'upload/images/' . $fileName;
                }
            }

            try {
                $this->alatModel->create($data);
                $_SESSION['success'] = 'Alat berhasil ditambahkan';
                $this->redirect('/alat');
            } catch (Exception $e) {
                error_log("Create alat error: " . $e->getMessage());
                $_SESSION['error'] = 'Gagal menambahkan alat: ' . $e->getMessage();
                $_SESSION['old_input'] = $data;
                $this->redirect('/alat/new');
            }
        }
    }

    /**
     * Update alat
     */
    public function updateAlat($id)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_alat' => $_POST['nama_alat'] ?? '',
                'kategori_id' => $_POST['kategori_id'] ?? '',
                'tipe_id' => $_POST['tipe_id'] ?? '',
                'tahun_pembelian' => $_POST['tahun_pembelian'] ?? '',
                'status' => $_POST['status'] ?? 'TERSEDIA',
                'deskripsi' => $_POST['deskripsi'] ?? '',
                'jumlah' => $_POST['stok'] ?? $_POST['jumlah'] ?? 1
            ];

            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/upload/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                $fileName = 'alat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    $data['gambar'] = 'upload/images/' . $fileName;
                }
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            try {
                $this->alatModel->update($id, $data);

                $_SESSION['success'] = 'Alat berhasil diperbarui';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Gagal memperbarui alat: ' . $e->getMessage();
            }

            $this->redirect("/alat/{$id}/detail");
        }
    }

    /**
     * Delete alat
     */
    public function deleteAlat($id)
    {
        $this->requireAdmin();

        // Check for active loans before deleting
        $activeLoans = $this->peminjamanModel->getPeminjamanByItem($id, 'alat', 'DIPINJAM');
        $pendingLoans = $this->peminjamanModel->getPeminjamanByItem($id, 'alat', 'PENDING');
        if (!empty($activeLoans) || !empty($pendingLoans)) {
            $_SESSION['error'] = 'Alat tidak dapat dihapus karena sedang dipinjam';
            $this->redirect('/alat');
            return;
        }

        try {
            $alat = $this->alatModel->getAlatDetails($id);

            $this->alatModel->softDelete($id);

            if ($alat) {
                $this->notifikasiModel->createNotification(
                    'Alat Dihapus',
                    "Alat '{$alat['nama_alat']}' telah dihapus",
                    [$_SESSION['user_id']]
                );
            }

            $_SESSION['success'] = 'Alat berhasil dihapus';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Gagal menghapus alat: ' . $e->getMessage();
        }

        $this->redirect('/alat');
    }

    /**
     * Change alat status
     */
    public function changeAlatStatus($id, $status)
    {
        $this->requireAdmin();

        try {
            $this->alatModel->updateStatus($id, $status);
            $_SESSION['success'] = 'Status alat berhasil diubah';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Gagal mengubah status alat: ' . $e->getMessage();
        }

        $this->redirect("/alat/{$id}/detail");
    }

    /**
     * Update alat status via AJAX
     */
    public function updateAlatStatus($alatId)
    {
        $this->requireAdmin();

        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $status = strtolower($input['status'] ?? '');

            $validStatuses = ['tersedia', 'dipinjam', 'maintenance', 'rusak'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                exit;
            }

            if ($this->alatModel->updateStatus($alatId, $status)) {
                $statusMessages = [
                    'tersedia' => 'Status alat berhasil diubah menjadi Tersedia',
                    'dipinjam' => 'Status alat berhasil diubah menjadi Dipinjam',
                    'maintenance' => 'Status alat berhasil diubah menjadi Maintenance',
                    'rusak' => 'Status alat berhasil diubah menjadi Rusak'
                ];
                echo json_encode(['success' => true, 'message' => $statusMessages[$status]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status alat']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
        exit;
    }
    /**
     * Show Manajemen Ruangan page
     */
    public function manajemenRuangan()
    {
        $this->requireAdmin();

        try {
            $page = $_GET['page'] ?? 1;
            $search = $_GET['search'] ?? '';
            $status = $_GET['status'] ?? '';

            $result = $this->ruanganModel->getRuanganPaginated($page, 10, $search, $status);
            $ruanganList = $result['data'] ?? [];

            $data = [
                'title' => 'Manajemen Ruangan - LBMS',
                'user' => [
                    'name' => $_SESSION['user_name'] ?? 'Admin User',
                    'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                    'role' => $_SESSION['user_role'] ?? 'ADMIN'
                ],
                'alat' => $ruanganList,
                'alatList' => $ruanganList,
                'search' => $search,
                'status' => $status,
                'total' => $result['total'] ?? 0,
                'page' => $result['page'] ?? 1,
                'total_pages' => $result['total_pages'] ?? 1,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('ruangan/index', $data);
        } catch (Exception $e) {
            error_log("Manajemen Ruangan error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat data ruangan';
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show new ruangan page
     */
    public function newRuangan()
    {
        $this->requireAdmin();

        try {
            $kategoriList = $this->ruanganModel->getAllKategori();
            $tipeList = [];
        } catch (Exception $e) {
            error_log("Error fetching kategori data: " . $e->getMessage());
            $kategoriList = [];
            $tipeList = [];
        }

        $oldInput = $_SESSION['old_input'] ?? [];

        $data = [
            'title' => 'Tambah Ruangan Baru - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'kategoriList' => $kategoriList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'oldInput' => $oldInput
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);
        unset($_SESSION['old_input']);

        $this->view('ruangan/new', $data);
    }

    /**
     * Show ruangan detail page
     */
    public function detailRuangan($id)
    {
        $this->requireAdmin();

        $ruanganData = $this->ruanganModel->getRuanganById($id);

        if (!$ruanganData) {
            $_SESSION['error'] = 'Ruangan tidak ditemukan';
            $this->redirect('/ruangan');
            return;
        }

        $data = [
            'title' => 'Detail Ruangan - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'ruanganDetail' => $ruanganData,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('ruangan/detail', $data);
    }

    /**
     * Show edit ruangan page
     */
    public function editRuangan($id)
    {
        $this->requireAdmin();

        $ruanganData = $this->ruanganModel->getRuanganById($id);

        if (!$ruanganData) {
            $_SESSION['error'] = 'Ruangan tidak ditemukan';
            $this->redirect('/ruangan');
            return;
        }

        try {
            $kategoriList = $this->ruanganModel->getAllKategori();
        } catch (Exception $e) {
            error_log("Error fetching kategori data: " . $e->getMessage());
            $kategoriList = [];
        }

        $data = [
            'title' => 'Edit Ruangan - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'ruanganDetail' => $ruanganData,
            'kategoriList' => $kategoriList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('ruangan/edit', $data);
    }

    /**
     * Create new ruangan
     */
    public function createRuangan()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/ruangan/new');
            return;
        }

        $kode_ruangan = $_POST['kode_ruangan'] ?? $_POST['kode_alat'] ?? '';

        try {
            $exists = $this->ruanganModel->kodeRuanganExists($kode_ruangan);
            if ($exists) {
                $_SESSION['error'] = "Kode ruangan '$kode_ruangan' sudah terdaftar. Silakan gunakan kode lain.";
                $_SESSION['old_input'] = [
                    'kode_ruangan' => $kode_ruangan,
                    'nama_ruangan' => $_POST['nama_ruangan'] ?? $_POST['nama_alat'] ?? '',
                    'kategori_id' => $_POST['kategori_id'] ?? null,
                    'status' => $_POST['status'] ?? 'TERSEDIA',
                    'deskripsi' => $_POST['deskripsi'] ?? '',
                    'kapasitas' => $_POST['kapasitas'] ?? $_POST['jumlah'] ?? 1,
                    'lantai' => $_POST['lantai'] ?? 1,
                    'gedung' => $_POST['gedung'] ?? ''
                ];
                $this->redirect('/ruangan/new');
                return;
            }
        } catch (Exception $e) {
            error_log("Error checking kode_ruangan: " . $e->getMessage());
        }

        $data = [
            'kode_ruangan' => $kode_ruangan,
            'nama_ruangan' => $_POST['nama_ruangan'] ?? $_POST['nama_alat'] ?? '',
            'kategori_id' => $_POST['kategori_id'] ?? null,
            'status' => $_POST['status'] ?? 'TERSEDIA',
            'deskripsi' => $_POST['deskripsi'] ?? '',
            'kapasitas' => $_POST['kapasitas'] ?? $_POST['jumlah'] ?? 1,
            'lantai' => $_POST['lantai'] ?? 1,
            'gedung' => $_POST['gedung'] ?? ''
        ];

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/upload/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $fileName = 'ruangan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                $data['gambar'] = 'upload/images/' . $fileName;
            }
        }

        try {
            $this->ruanganModel->create($data);
            $_SESSION['success'] = 'Ruangan berhasil ditambahkan';
            $this->redirect('/ruangan');
        } catch (Exception $e) {
            error_log("Create ruangan error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal menambahkan ruangan: ' . $e->getMessage();
            $_SESSION['old_input'] = $data;
            $this->redirect('/ruangan/new');
        }
    }

    /**
     * Update ruangan
     */
    public function updateRuangan($id)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/ruangan/' . $id . '/edit');
            return;
        }

        $ruangan = $this->ruanganModel->getRuanganById($id);
        if (!$ruangan) {
            $_SESSION['error'] = 'Ruangan tidak ditemukan';
            $this->redirect('/ruangan');
            return;
        }

        $data = [
            'nama_ruangan' => $_POST['nama_ruangan'] ?? $_POST['nama_alat'] ?? '',
            'kategori_id' => $_POST['kategori_id'] ?? '',
            'status' => $_POST['status'] ?? 'TERSEDIA',
            'deskripsi' => $_POST['deskripsi'] ?? '',
            'kapasitas' => $_POST['kapasitas'] ?? $_POST['jumlah'] ?? 1,
            'lantai' => $_POST['lantai'] ?? 1,
            'gedung' => $_POST['gedung'] ?? ''
        ];

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/upload/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
            $fileName = 'ruangan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                $data['gambar'] = 'upload/images/' . $fileName;
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        try {
            $this->ruanganModel->update($id, $data);
            $_SESSION['success'] = 'Ruangan berhasil diperbarui';
        } catch (Exception $e) {
            error_log("Update ruangan error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memperbarui ruangan: ' . $e->getMessage();
        }

        $this->redirect("/ruangan/$id/detail");
    }

    /**
     * Delete ruangan
     */
    public function deleteRuangan($id)
    {
        $this->requireAdmin();

        try {
            $ruangan = $this->ruanganModel->getRuanganById($id);

            $this->ruanganModel->softDelete($id);

            if ($ruangan) {
                $this->notifikasiModel->createNotification(
                    'Ruangan Dihapus',
                    "Ruangan '{$ruangan['nama_ruangan']}' telah dihapus",
                    [$_SESSION['user_id']]
                );
            }

            $_SESSION['success'] = 'Ruangan berhasil dihapus';
        } catch (Exception $e) {
            error_log("Delete ruangan error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal menghapus ruangan: ' . $e->getMessage();
        }

        $this->redirect('/ruangan');
    }

    /**
     * Change ruangan status
     */
    public function changeRuanganStatus($id, $status)
    {
        $this->requireAdmin();

        try {
            $this->ruanganModel->updateStatus($id, $status);
            $_SESSION['success'] = 'Status ruangan berhasil diubah';
        } catch (Exception $e) {
            error_log("Change ruangan status error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal mengubah status ruangan: ' . $e->getMessage();
        }

        $this->redirect("/ruangan/$id/detail");
    }

    /**
     * Update ruangan status via AJAX
     */
    public function updateRuanganStatus($ruanganId)
    {
        header('Content-Type: application/json');

        $this->requireAdmin();

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $status = strtolower($input['status'] ?? '');

            $validStatuses = ['tersedia', 'dipinjam', 'maintenance', 'rusak'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                exit;
            }

            if ($this->ruanganModel->updateStatus($ruanganId, $status)) {
                $statusMessages = [
                    'tersedia' => 'Status ruangan berhasil diubah menjadi Tersedia',
                    'dipinjam' => 'Status ruangan berhasil diubah menjadi Dipinjam',
                    'maintenance' => 'Status ruangan berhasil diubah menjadi Maintenance',
                    'rusak' => 'Status ruangan berhasil diubah menjadi Rusak'
                ];
                echo json_encode(['success' => true, 'message' => $statusMessages[$status]]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal memperbarui status ruangan']);
            }
        } catch (Exception $e) {
            error_log("Update ruangan status error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Show new peminjaman page
     */
    public function newPeminjaman()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        try {
            $alatList = $this->alatModel->getAvailableAlat();
            $ruanganList = $this->ruanganModel->getRuanganForBooking();
        } catch (Exception $e) {
            error_log("Error fetching available alat: " . $e->getMessage());
            $alatList = [];
            $ruanganList = [];
        }

        $data = [
            'title' => 'Ajukan Peminjaman Baru - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'alatList' => $alatList,
            'ruanganList' => $ruanganList ?? [],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('peminjaman/new', $data);
    }

    /**
     * Show peminjaman detail page
     */
    public function detailPeminjaman($id)
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $peminjamanDetail = $this->peminjamanModel->getPeminjamanDetails($id);

        if (!$peminjamanDetail) {
            $_SESSION['error'] = 'Data peminjaman tidak ditemukan';
            $this->redirect('/peminjaman');
            return;
        }

        $userRole = $_SESSION['user_role'] ?? 'USER';
        $userId = $_SESSION['user_id'] ?? null;

        // Regular users can only view their own peminjaman
        if ($userRole !== 'ADMIN' && $peminjamanDetail['user_id'] != $userId) {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke peminjaman ini';
            $this->redirect('/peminjaman');
            return;
        }

        $data = [
            'title' => 'Detail Peminjaman - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $userRole
            ],
            'peminjamanDetail' => $peminjamanDetail,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('peminjaman/detail', $data);
    }

    /**
     * Create new peminjaman
     */
    public function createPeminjaman()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $userId = $_SESSION['user_id'] ?? null;

                if (!$userId) {
                    $this->redirect('/logout');
                    return;
                }

                // Determine item type and set appropriate ID
                $jenisItem = $_POST['jenis_item'] ?? '';
                $alatId = $_POST['alat_id'] ?? null;
                $ruanganId = $_POST['ruangan_id'] ?? null;

                $namaPeminjam = $_POST['nama_peminjam'] ?? $_SESSION['user_name'] ?? 'User';

                // Determine item type and ID
                $itemType = '';
                $itemId = null;

                if ($jenisItem === 'alat') {
                    if (empty($alatId) || empty($_POST['tanggal_pinjam']) || empty($_POST['tanggal_kembali'])) {
                        $_SESSION['error'] = 'Semua field wajib diisi';
                        $this->redirect('/peminjaman/new');
                        return;
                    }
                    $itemType = 'alat';
                    $itemId = $alatId;
                } elseif ($jenisItem === 'ruangan') {
                    if (empty($ruanganId) || empty($_POST['tanggal_pinjam']) || empty($_POST['tanggal_kembali'])) {
                        $_SESSION['error'] = 'Semua field wajib diisi';
                        $this->redirect('/peminjaman/new');
                        return;
                    }
                    $itemType = 'ruangan';
                    $itemId = $ruanganId;
                } else {
                    $_SESSION['error'] = 'Jenis item tidak valid';
                    $this->redirect('/peminjaman/new');
                    return;
                }

                $data = [
                    'user_id' => $userId,
                    'nama_peminjam' => $namaPeminjam,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'tanggal_pinjam' => $_POST['tanggal_pinjam'] ?? '',
                    'tanggal_kembali' => $_POST['tanggal_kembali'] ?? '',
                    'keterangan' => $_POST['catatan'] ?? '',
                    'status' => 'PENDING'
                ];

                $peminjamanId = $this->peminjamanModel->createPeminjaman($data);
                if ($peminjamanId) {
                    $peminjaman = $this->peminjamanModel->getPeminjamanDetails($peminjamanId);
                    if ($peminjaman) {
                        $itemName = $peminjaman['nama_alat'] ?? $peminjaman['nama_ruangan'] ?? $itemType;
                        $this->notifikasiModel->createNotification(
                            'Pengajuan Peminjaman Dikirim',
                            "Pengajuan peminjaman {$itemName} berhasil dikirim dan menunggu persetujuan",
                            [$userId],
                            $peminjamanId
                        );

                        $adminUsers = $this->userModel->getUsersByRole('ADMIN');
                        if ($adminUsers && !empty($adminUsers)) {
                            $adminIds = array_column($adminUsers, 'id');
                            $this->notifikasiModel->createNotification(
                                'Pengajuan Peminjaman Baru',
                                "Peminjaman {$itemName} oleh {$namaPeminjam} menunggu persetujuan",
                                $adminIds,
                                $peminjamanId
                            );
                        }
                    }
                    $_SESSION['success'] = "Pengajuan peminjaman {$itemType} berhasil dikirim";
                } else {
                    $_SESSION['error'] = 'Gagal mengajukan peminjaman';
                }

            } catch (Exception $e) {
                $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            }

            $this->redirect('/peminjaman');
        }
    }

    /**
     * Update peminjaman status
     */
    public function updatePeminjamanStatus($id)
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $status = $_POST['status'] ?? '';
                $catatan = $_POST['catatan'] ?? '';

                if (empty($status)) {
                    $_SESSION['error'] = 'Status harus diisi';
                    $this->redirect("/peminjaman/{$id}/detail");
                    return;
                }

                if ($this->peminjamanModel->update($id, ['status' => $status, 'catatan' => $catatan])) {
                    $_SESSION['success'] = 'Status peminjaman berhasil diperbarui';
                } else {
                    $_SESSION['error'] = 'Gagal memperbarui status peminjaman';
                }

            } catch (Exception $e) {
                $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
            }

            $this->redirect("/peminjaman/{$id}/detail");
        }
    }

    /**
     * Kembalikan peminjaman
     */
    public function kembalikanPeminjaman($id)
    {
        $this->requireAdmin();

        try {
            if ($this->peminjamanModel->kembalikan($id)) {
                $_SESSION['success'] = 'Alat berhasil dikembalikan';
            } else {
                $_SESSION['error'] = 'Gagal mengembalikan alat';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        $this->redirect("/peminjaman/{$id}/detail");
    }

    /**
     * Batalkan peminjaman
     */
    public function batalkanPeminjaman($id)
    {
        $this->requireAdmin();

        try {
            if ($this->peminjamanModel->batalkan($id)) {
                $_SESSION['success'] = 'Peminjaman berhasil dibatalkan';
            } else {
                $_SESSION['error'] = 'Gagal membatalkan peminjaman';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        $this->redirect('/peminjaman');
    }

    /**
     * Show Peminjaman page
     */
    public function peminjaman()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? 'USER';

        if ($userRole === 'ADMIN') {
            $peminjamanList = $this->peminjamanModel->getRecentPeminjaman(100);
        } else {
            $peminjamanList = $this->peminjamanModel->getPeminjamanByUser($userId, null, 100);
        }

        $data = [
            'title' => 'Peminjaman - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $userRole
            ],
            'peminjaman' => $peminjamanList ?? [],
            'peminjamanList' => $peminjamanList ?? [],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('peminjaman/index', $data);
    }

    /**
     * Show Settings page
     */
    public function settings()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/logout');
            return;
        }

        $data = [
            'title' => 'Pengaturan - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('settings/index', $data);
    }

    /**
     * Show Settings Profile page
     */
    public function settingsProfile()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/logout');
            return;
        }

        $data = [
            'title' => 'Profil Pengaturan - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('settings/profile/index', $data);
    }

    /**
     * Show Privacy & Security page
     */
    public function settingsPrivacySecurity()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/logout');
            return;
        }

        $data = [
            'title' => 'Privasi & Keamanan - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('settings/privacy-security/index', $data);
    }

    /**
     * Show Notifications page
     */
    public function notifications()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/logout');
        }

        $data = [
            'title' => 'Notifikasi - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        try {
            $userId = $_SESSION['user_id'] ?? 1; 
            $notifications = $this->notifikasiModel->getNotificationsForUser($userId, 50);
            $unreadCount = $this->notifikasiModel->getUnreadCount($userId);

            $data['notifications'] = $notifications;
            $data['unreadCount'] = $unreadCount;
        } catch (Exception $e) {
            error_log("Notifications error: " . $e->getMessage());
            $data['notifications'] = [];
            $data['unreadCount'] = 0;
        }

        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('notifications/index', $data);
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function markNotificationRead($id)
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $result = $this->notifikasiModel->markAsRead($id, $userId);
            echo json_encode(['success' => $result ? true : false]);
        } catch (Exception $e) {
            error_log("Mark notification read error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
        }
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllNotificationsRead()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        try {
            $userId = $_SESSION['user_id'];
            $result = $this->notifikasiModel->markAllAsRead($userId);
            echo json_encode(['success' => $result ? true : false]);
        } catch (Exception $e) {
            error_log("Mark all notifications read error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to mark all as read']);
        }
    }

    /**
     * Delete notification (AJAX)
     */
    public function deleteNotification($id)
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        try {
            $result = $this->notifikasiModel->deleteNotificationForUser($id, $_SESSION['user_id']);
            echo json_encode(['success' => $result ? true : false]);
        } catch (Exception $e) {
            error_log("Delete notification error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to delete notification']);
        }
    }

    /**
     * Proses peminjaman (approve)
     */
    public function prosesPeminjaman($id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            if ($this->peminjamanModel->updateStatus($id, 'DIPINJAM')) {
                $peminjaman = $this->peminjamanModel->getPeminjamanDetails($id);
                if ($peminjaman) {
                    $this->notifikasiModel->createNotification(
                        'Peminjaman Disetujui',
                        "Peminjaman {$peminjaman['nama_alat']} telah disetujui",
                        [$peminjaman['user_id']]
                    );
                }

                echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil disetujui']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyetujui peminjaman']);
            }
        } catch (Exception $e) {
            error_log("Proses peminjaman error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat memproses peminjaman']);
        }
        exit;
    }

    /**
     * Tolak peminjaman
     */
    public function tolakPeminjaman($id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $alasan = $input['alasan'] ?? 'Peminjaman ditolak';

            if ($this->peminjamanModel->updateStatus($id, 'DITOLAK')) {
                $peminjaman = $this->peminjamanModel->getPeminjamanDetails($id);
                if ($peminjaman) {
                    $this->notifikasiModel->createNotification(
                        'Peminjaman Ditolak',
                        "Peminjaman {$peminjaman['nama_alat']} ditolak: " . htmlspecialchars($alasan),
                        [$peminjaman['user_id']]
                    );
                }

                echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil ditolak']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menolak peminjaman']);
            }
        } catch (Exception $e) {
            error_log("Tolak peminjaman error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menolak peminjaman']);
        }
        exit;
    }

    /**
     * Selesaikan peminjaman
     */
    public function selesaikanPeminjaman($id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            if ($this->peminjamanModel->returnAsset($id, 'BAIK')) {
                $peminjaman = $this->peminjamanModel->getPeminjamanDetails($id);
                if ($peminjaman) {
                    $this->notifikasiModel->createNotification(
                        'Peminjaman Selesai',
                        "Peminjaman {$peminjaman['nama_alat']} telah selesai",
                        [$peminjaman['user_id']]
                    );
                }

                echo json_encode(['success' => true, 'message' => 'Peminjaman berhasil diselesaikan']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Gagal menyelesaikan peminjaman']);
            }
        } catch (Exception $e) {
            error_log("Selesaikan peminjaman error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat menyelesaikan peminjaman']);
        }
        exit;
    }
}

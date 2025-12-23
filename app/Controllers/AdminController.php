<?php

/**
 * Admin Controller
 */
class AdminController extends BaseController
{
    private $alatModel;
    private $peminjamanModel;
    private $userModel;
    private $kategoriAlatModel;
    private $tipeAlatModel;
    private $notifikasiModel;

    public function __construct()
    {
        parent::__construct();
        $this->alatModel = new AlatModel();
        $this->peminjamanModel = new PeminjamanModel();
        $this->userModel = new UserModel();
        $this->kategoriAlatModel = new KategoriAlatModel();
        $this->tipeAlatModel = new TipeAlatModel();
        $this->notifikasiModel = new NotifikasiModel();
    }

    /**
     * Override view method to add notification count
     */
    protected function view($view, $data = [])
    {
        // Add unread notification count if user is logged in
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

        // Call parent view method
        parent::view($view, $data);
    }

    /**
     * Show dashboard page
     */
    public function dashboard()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses dashboard';
            $this->redirect('/login');
            return;
        }

        // Get current user data
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

        // Get dashboard statistics
        try {
            $stats = $this->getDashboardStats();
            $data = array_merge($data, $stats);
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            $data['stats'] = $this->getDefaultStats();
        }

        // Get recent peminjaman data
        try {
            $recentPeminjaman = $this->peminjamanModel->getRecentPeminjaman(10);
            $data['recentPeminjaman'] = $recentPeminjaman;

            // Debug logging
            error_log("Recent peminjaman count: " . count($recentPeminjaman));
            if (!empty($recentPeminjaman)) {
                error_log("First peminjaman record: " . print_r($recentPeminjaman[0], true));
            }
        } catch (Exception $e) {
            error_log("Recent peminjaman error: " . $e->getMessage());
            $data['recentPeminjaman'] = [];
        }

        // Clear session messages
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
            // Get alat statistics
            $alatStats = $this->alatModel->getAlatStatistics();

            // Get user statistics
            $userStats = $this->userModel->getUserStatistics();

            // Get peminjaman statistics
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

        // Check if user is logged in
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

        // Authentication disabled for now
        // if (!$this->isLoggedIn()) {
        //     echo json_encode(['error' => 'Unauthorized']);
        //     return;
        // }

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
            // Validate input
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

            // Check if email exists for other user
            if ($this->userModel->emailExists($email, $userId)) {
                $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                $this->redirect('/settings/profile');
                return;
            }

            // Update user
            $userData = [
                'name' => htmlspecialchars($name),
                'email' => strtolower($email)
            ];

            if ($this->userModel->update($userId, $userData)) {
                // Update session
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
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        try {
            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = 'Semua field harus diisi';
                $this->redirect('/dashboard/profile');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok';
                $this->redirect('/dashboard/profile');
                return;
            }

            if (empty($newPassword)) {
                $_SESSION['error'] = 'Password baru tidak boleh kosong';
                $this->redirect('/dashboard/profile');
                return;
            }

            // Get user to verify current password
            $user = $this->userModel->find($userId);
            if (!$user) {
                $_SESSION['error'] = 'Pengguna tidak ditemukan';
                $this->redirect('/dashboard/profile');
                return;
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password_hash'])) {
                $_SESSION['error'] = 'Password saat ini tidak benar';
                $this->redirect('/dashboard/profile');
                return;
            }

            // Update password
            if ($this->userModel->updatePassword($userId, $newPassword)) {
                $_SESSION['success'] = 'Password berhasil diubah';
            } else {
                $_SESSION['error'] = 'Gagal mengubah password';
            }

        } catch (Exception $e) {
            error_log("Password change error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat mengubah password';
        }

        $this->redirect('/dashboard/profile');
    }

    /**
     * Show admin users management
     */
    public function users()
    {
        // Check if user is admin
        if (!$this->isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
            $_SESSION['error'] = 'Akses ditolak. Hanya admin yang dapat mengakses halaman ini.';
            $this->redirect('/dashboard');
            return;
        }

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

            // Validation
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

            // Create user
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
        if (!$this->isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
            $_SESSION['error'] = 'Unauthorized access';
            $this->redirect('/dashboard');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/users');
            return;
        }

        try {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $status = strtoupper($_POST['status'] ?? 'ACTIVE');

            // Validation
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

            // Validate status
            $validStatuses = ['ACTIVE', 'INACTIVE', 'BLACKLIST'];
            if (!in_array($status, $validStatuses)) {
                $_SESSION['error'] = 'Status tidak valid';
                $this->redirect('/users/' . $userId);
                return;
            }

            // Check if email exists for other user
            if ($this->userModel->emailExists($email, $userId)) {
                $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                $this->redirect('/users/' . $userId);
                return;
            }

            // Update user
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
        // Check if user is admin
        if (!$this->isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
            $this->redirect('/dashboard');
            return;
        }

        // Prevent admin from deactivating themselves
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

        // Check if user is logged in and is admin
        if (!$this->isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        // Prevent admin from changing their own status
        if ($userId == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'Tidak dapat mengubah status sendiri']);
            exit;
        }

        try {
            // Get status from POST body
            $input = json_decode(file_get_contents('php://input'), true);
            $status = strtoupper($input['status'] ?? '');

            // Validate status
            $validStatuses = ['ACTIVE', 'INACTIVE', 'BLACKLIST'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                exit;
            }

            // Update user status
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
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        try {
            // Get all users data using pagination with large limit
            $result = $this->userModel->getUsersPaginated(1, 1000); // Get first 1000 records
            $usersList = $result['data'] ?? [];
        } catch (Exception $e) {
            error_log("Error fetching users data: " . $e->getMessage());
            $usersList = [];
        }

        // Set user data
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

        // Clear session messages
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('users/index', $data);
    }

    /**
     * Show Manajemen Alat page
     */
    public function manajemenAlat()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        try {
            // Get all alat data using pagination with large limit
            $alatModel = new AlatModel();
            $result = $alatModel->getAlatPaginated(1, 1000); // Get first 1000 records
            $alatList = $result['data'] ?? [];
        } catch (Exception $e) {
            error_log("Error fetching alat data: " . $e->getMessage());
            $alatList = [];
        }

        // Set default user data when auth is disabled
        $data = [
            'title' => 'Manajemen Alat - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'alat' => $alatList,
            'alatList' => $alatList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null
        ];

        // Clear session messages
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('alat/index', $data);
    }

    /**
     * Show new alat page
     */
    public function newAlat()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        try {
            // Get all categories and types
            $kategoriList = $this->alatModel->getAllKategori();
            $tipeList = $this->alatModel->getAllTipe();
        } catch (Exception $e) {
            error_log("Error fetching kategori/tipe data: " . $e->getMessage());
            $kategoriList = [];
            $tipeList = [];
        }

        // Get old input from session if exists (for form repopulation)
        $oldInput = $_SESSION['old_input'] ?? [];

        $data = [
            'title' => 'Tambah Alat Baru - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'kategoriList' => $kategoriList,
            'tipeList' => $tipeList,
            'error' => $_SESSION['error'] ?? null,
            'success' => $_SESSION['success'] ?? null,
            'oldInput' => $oldInput  // Pass old input to view
        ];

        // Clear session messages and old input
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
        // Get alat data
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
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        // Get alat data
        $alatData = $this->alatModel->getAlatDetails($id);

        if (!$alatData) {
            $_SESSION['error'] = 'Alat tidak ditemukan';
            $this->redirect('/alat');
            return;
        }

        try {
            // Get all categories and types
            $kategoriList = $this->alatModel->getAllKategori();
            $tipeList = $this->alatModel->getAllTipe();
        } catch (Exception $e) {
            error_log("Error fetching kategori/tipe data: " . $e->getMessage());
            $kategoriList = [];
            $tipeList = [];
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
            'tipeList' => $tipeList,
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
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

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
        // Get user data
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
        // Get user data
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $kode_alat = $_POST['kode_alat'] ?? '';

            // Validasi: Cek apakah kode_alat sudah ada
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
                        'deskripsi' => $_POST['deskripsi'] ?? ''
                    ];
                    $this->redirect('/alat/new');
                    return;
                }
            } catch (Exception $e) {
                error_log("Error checking kode_alat: " . $e->getMessage());
                // Continue with insert attempt
            }

            $data = [
                'kode_alat' => $kode_alat,
                'nama_alat' => $_POST['nama_alat'] ?? '',
                'kategori_id' => $_POST['kategori_id'] ?? null,
                'tipe_id' => $_POST['tipe_id'] ?? null,
                'tahun_pembelian' => $_POST['tahun_pembelian'] ?? null,
                'status' => $_POST['status'] ?? 'TERSEDIA',
                'deskripsi' => $_POST['deskripsi'] ?? ''
            ];

            // Handle file upload
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/upload/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                $fileName = 'alat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    // Store the path in database (without 'public/' prefix for web access)
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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nama_alat' => $_POST['nama_alat'] ?? '',
                // kode_alat di-exclude dari update untuk mencegah perubahan
                'kategori_id' => $_POST['kategori_id'] ?? '',
                'tipe_id' => $_POST['tipe_id'] ?? '',
                'tahun_pembelian' => $_POST['tahun_pembelian'] ?? '',
                'status' => $_POST['status'] ?? 'TERSEDIA',
                'deskripsi' => $_POST['deskripsi'] ?? ''
            ];

            // Handle file upload if new image provided
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/upload/images/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $extension = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
                $fileName = 'alat_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . ($extension ? '.' . $extension : '');
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    // Store the path in database (without 'public/' prefix for web access)
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
        try {
            // Get alat details before deletion for notification
            $alat = $this->alatModel->getAlatDetails($id);

            // Use soft delete instead of permanent deletion
            $this->alatModel->softDelete($id);

            // Create notification for deleted alat
            if ($alat) {
                $this->notifikasiModel->createNotification(
                    'Alat Dihapus',
                    "Alat '{$alat['nama_alat']}' telah dihapus",
                    'admin',
                    [$this->currentUser['id']]
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
        header('Content-Type: application/json');

        // Check if user is logged in and is admin
        if (!$this->isLoggedIn() || $_SESSION['user_role'] !== 'ADMIN') {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $status = strtolower($input['status'] ?? '');

            // Validate status
            $validStatuses = ['tersedia', 'dipinjam', 'maintenance', 'rusak'];
            if (!in_array($status, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Status tidak valid']);
                exit;
            }

            // Update alat status
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
     * Show new peminjaman page
     */
    public function newPeminjaman()
    {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        // Get available alat (status = TERSEDIA)
        try {
            $alatList = $this->alatModel->getAvailableAlat();
        } catch (Exception $e) {
            error_log("Error fetching available alat: " . $e->getMessage());
            $alatList = [];
        }

        $data = [
            'title' => 'Ajukan Peminjaman Baru - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'alatList' => $alatList,
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
        // Check if user is ADMIN - USER role cannot access detail page
        $userRole = $_SESSION['user_role'] ?? 'USER';
        if ($userRole !== 'ADMIN') {
            $_SESSION['error'] = 'Anda tidak memiliki akses ke halaman detail peminjaman';
            $this->redirect('/peminjaman');
            return;
        }

        // Get peminjaman data from database
        $peminjamanDetail = $this->peminjamanModel->getPeminjamanDetails($id);

        if (!$peminjamanDetail) {
            $_SESSION['error'] = 'Data peminjaman tidak ditemukan';
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
                // Get user_id from session (session structure is $_SESSION['user_id'], not $_SESSION['user']['id'])
                $userId = $_SESSION['user_id'] ?? null;

                if (!$userId) {
                    // User is not authenticated, redirect to logout
                    $this->redirect('/logout');
                    return;
                }

                $data = [
                    'user_id' => $userId,
                    'nama_peminjam' => $_POST['nama_peminjam'] ?? $_SESSION['user_name'] ?? 'User',
                    'alat_id' => $_POST['alat_id'] ?? '',
                    'tanggal_pinjam' => $_POST['tanggal_pinjam'] ?? '',
                    'tanggal_kembali' => $_POST['tanggal_kembali'] ?? '',
                    'keterangan' => $_POST['catatan'] ?? '', // Map 'catatan' from form to 'keterangan' in DB
                    'status' => 'PENDING'
                ];

                // Get nama_peminjam from data for notification
                $namaPeminjam = $data['nama_peminjam'];

                // Validation
                if (empty($data['alat_id']) || empty($data['tanggal_pinjam']) || empty($data['tanggal_kembali'])) {
                    $_SESSION['error'] = 'Semua field wajib diisi';
                    $this->redirect('/peminjaman/new');
                    return;
                }

                // Create peminjaman record
                $peminjamanId = $this->peminjamanModel->create($data);
                if ($peminjamanId) {
                    // Get peminjaman details for notification
                    $peminjaman = $this->peminjamanModel->getPeminjamanDetails($peminjamanId);
                    if ($peminjaman) {
                        // Notify requesting user
                        $this->notifikasiModel->createNotification(
                            'Pengajuan Peminjaman Dikirim',
                            "Pengajuan peminjaman {$peminjaman['nama_alat']} berhasil dikirim dan menunggu persetujuan",
                            [$userId],
                            $peminjamanId
                        );

                        // Get all ADMIN users
                        $adminUsers = $this->userModel->getUsersByRole('ADMIN');
                        if ($adminUsers && !empty($adminUsers)) {
                            $adminIds = array_column($adminUsers, 'id');
                            // Create notification to all admins
                            $this->notifikasiModel->createNotification(
                                'Pengajuan Peminjaman Baru',
                                "Peminjaman {$peminjaman['nama_alat']} oleh {$namaPeminjam} menunggu persetujuan",
                                $adminIds,
                                $peminjamanId  // Attach peminjaman_id to notification
                            );
                        }
                    }
                    $_SESSION['success'] = 'Pengajuan peminjaman berhasil dikirim';
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
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses halaman ini';
            $this->redirect('/login');
            return;
        }

        // Get current user
        $userId = $_SESSION['user_id'] ?? null;
        $userRole = $_SESSION['user_role'] ?? 'USER';

        // Fetch peminjaman data based on role
        if ($userRole === 'ADMIN') {
            // Admin sees all peminjaman
            $peminjamanList = $this->peminjamanModel->getRecentPeminjaman(100);
        } else {
            // Regular user sees only their own peminjaman
            $peminjamanList = $this->peminjamanModel->getPeminjamanByUser($userId, null, 100);
        }

        // Set default user data when auth is disabled
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

        // Clear session messages
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('peminjaman/index', $data);
    }

    /**
     * Show Settings page
     */
    public function settings()
    {
        // Set default user data when auth is disabled
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

        // Clear session messages
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('settings/index', $data);
    }

    /**
     * Show Settings Profile page
     */
    public function settingsProfile()
    {
        // Set default user data when auth is disabled
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

        // Clear session messages
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('settings/profile/index', $data);
    }

    /**
     * Show Privacy & Security page
     */
    public function settingsPrivacySecurity()
    {
        // Set default user data when auth is disabled
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

        // Clear session messages
        unset($_SESSION['error']);
        unset($_SESSION['success']);

        $this->view('settings/privacy-security/index', $data);
    }

    /**
     * Show Notifications page
     */
    public function notifications()
    {
        // Set default user data when auth is disabled
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

        // Get notifications data
        try {
            $userId = $_SESSION['user_id'] ?? 1; // Default to user ID 1 for demo
            $notifications = $this->notifikasiModel->getNotificationsForUser($userId, 50);
            $unreadCount = $this->notifikasiModel->getUnreadCount($userId);

            $data['notifications'] = $notifications;
            $data['unreadCount'] = $unreadCount;
        } catch (Exception $e) {
            error_log("Notifications error: " . $e->getMessage());
            $data['notifications'] = [];
            $data['unreadCount'] = 0;
        }

        // Clear session messages
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
        header('Content-Type: application/json');

        try {
            if ($this->peminjamanModel->updateStatus($id, 'DIPINJAM')) {
                // Create notification
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
        header('Content-Type: application/json');

        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $alasan = $input['alasan'] ?? 'Peminjaman ditolak';

            if ($this->peminjamanModel->updateStatus($id, 'DITOLAK')) {
                // Create notification
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
        header('Content-Type: application/json');

        try {
            if ($this->peminjamanModel->returnAsset($id, 'BAIK')) {
                // Create notification
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

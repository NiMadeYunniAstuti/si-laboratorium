<?php

/**
 * Users Controller
 */
class UsersController extends BaseController
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
     * Show dashboard page
     */
    public function dashboard()
    {
        if (!$this->isLoggedIn()) {
            $_SESSION['error'] = 'Anda harus login untuk mengakses dashboard';
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            $userDetails = $this->userModel->getUserDetails($userId);

            $data = [
                'title' => 'Dashboard - LBMS',
                'user' => $userDetails,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('dashboard/index', $data);
        } catch (Exception $e) {
            error_log("Dashboard error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat dashboard';
            $this->redirect('/login');
        }
    }

    /**
     * Get dashboard statistics (AJAX endpoint)
     */
    public function getDashboardStats()
    {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }

        try {
            $stats = [];

            $alatStats = $this->alatModel->getAlatStatistics();
            $stats['totalAlat'] = $alatStats['total'] ?? 0;
            $stats['alatTersedia'] = $alatStats['by_status']['tersedia'] ?? 0;

            $peminjamanStats = $this->peminjamanModel->getPeminjamanStatistics();
            $stats['totalPeminjaman'] = $peminjamanStats['total'] ?? 0;
            $stats['peminjamanAktif'] = $peminjamanStats['by_status']['dipinjam'] ?? 0;

            $userStats = $this->userModel->getUserStatistics();
            $stats['totalUsers'] = $userStats['total'] ?? 0;
            $stats['usersAktif'] = $userStats['by_status']['active'] ?? 0;

            $stats['recentPeminjaman'] = $this->peminjamanModel->getRecentPeminjaman(5);
            $stats['recentUsers'] = $this->userModel->getRecentUsers(5);

            echo json_encode($stats);
        } catch (Exception $e) {
            error_log("Dashboard stats error: " . $e->getMessage());
            echo json_encode(['error' => 'Failed to fetch statistics']);
        }
    }

    /**
     * Show profile page
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
                $this->redirect('/dashboard/profile');
                return;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email tidak valid';
                $this->redirect('/dashboard/profile');
                return;
            }

            if ($this->userModel->emailExists($email, $userId)) {
                $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                $this->redirect('/dashboard/profile');
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

        $this->redirect('/dashboard/profile');
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
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = 'Semua field password harus diisi';
                $this->redirect('/dashboard/profile');
                return;
            }

            if (strlen($newPassword) < 6) {
                $_SESSION['error'] = 'Password baru minimal 6 karakter';
                $this->redirect('/dashboard/profile');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Password baru dan konfirmasi tidak cocok';
                $this->redirect('/dashboard/profile');
                return;
            }

            $currentUser = $this->userModel->find($userId);
            if (!$currentUser) {
                $_SESSION['error'] = 'User tidak ditemukan';
                $this->redirect('/dashboard/profile');
                return;
            }

            if (!password_verify($currentPassword, $currentUser['password_hash'])) {
                $_SESSION['error'] = 'Password saat ini salah';
                $this->redirect('/dashboard/profile');
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

        $this->redirect('/dashboard/profile');
    }

    /**
     * Show list of alat
     */
    public function alat()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        try {
            $result = $this->alatModel->getAlatPaginated($page, 10, $search, $kategori, $status);
            $kategoriList = $this->kategoriAlatModel->getAllKategori();

            $data = [
                'title' => 'Daftar Alat - LBMS',
                'alat' => $result['data'],
                'pagination' => [
                    'current_page' => $result['page'],
                    'total_pages' => $result['total_pages'],
                    'total_items' => $result['total']
                ],
                'kategori' => $kategoriList,
                'filters' => [
                    'search' => $search,
                    'kategori' => $kategori,
                    'status' => $status
                ],
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('alat/index', $data);
        } catch (Exception $e) {
            error_log("Alat list error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat daftar alat';
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show alat detail page
     */
    public function detailAlat($id)
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        try {
            $alat = $this->alatModel->getAlatDetails($id);
            if (!$alat) {
                $_SESSION['error'] = 'Alat tidak ditemukan';
                $this->redirect('/alat');
                return;
            }

            $peminjamanHistory = $this->peminjamanModel->getPeminjamanByAlat($id);

            $data = [
                'title' => 'Detail Alat - LBMS',
                'alat' => $alat,
                'peminjaman_history' => $peminjamanHistory,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('alat/detail', $data);
        } catch (Exception $e) {
            error_log("Alat detail error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat detail alat';
            $this->redirect('/alat');
        }
    }

    /**
     * Show peminjaman page
     */
    public function peminjaman()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $status = isset($_GET['status']) ? $_GET['status'] : '';

        try {
            $result = $this->peminjamanModel->getPeminjamanPaginated($page, 10, $search, $status);

            $data = [
                'title' => 'Data Peminjaman - LBMS',
                'peminjaman' => $result['data'],
                'pagination' => [
                    'current_page' => $result['page'],
                    'total_pages' => $result['total_pages'],
                    'total_items' => $result['total']
                ],
                'filters' => [
                    'search' => $search,
                    'status' => $status
                ],
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('peminjaman/index', $data);
        } catch (Exception $e) {
            error_log("Peminjaman list error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat data peminjaman';
            $this->redirect('/dashboard');
        }
    }

    /**
     * Show form to create peminjaman
     */
    public function createPeminjaman()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        try {
            $alatList = $this->alatModel->getAvailableAlat();
            $usersList = $this->userModel->getUsersByRole('USER');

            $data = [
                'title' => 'Pinjam Alat - LBMS',
                'alat_list' => $alatList,
                'users_list' => $usersList,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('peminjaman/create', $data);
        } catch (Exception $e) {
            error_log("Create peminjaman form error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat form peminjaman';
            $this->redirect('/peminjaman');
        }
    }

    /**
     * Return asset
     */
    public function returnAsset($id)
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        try {
            if ($this->peminjamanModel->returnAsset($id)) {
                $_SESSION['success'] = 'Alat berhasil dikembalikan';
            } else {
                $_SESSION['error'] = 'Gagal mengembalikan alat';
            }
        } catch (Exception $e) {
            error_log("Return asset error: " . $e->getMessage());
            $_SESSION['error'] = 'Terjadi kesalahan saat mengembalikan alat';
        }

        $this->redirect('/peminjaman');
    }

    /**
     * Show notifications
     */
    public function notifications()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'];

        try {
            $notifications = $this->notifikasiModel->getNotificationsForUser($userId);

            $data = [
                'title' => 'Notifikasi - LBMS',
                'notifications' => $notifications,
                'error' => $_SESSION['error'] ?? null,
                'success' => $_SESSION['success'] ?? null
            ];

            unset($_SESSION['error']);
            unset($_SESSION['success']);

            $this->view('dashboard/notifications', $data);
        } catch (Exception $e) {
            error_log("Notifications error: " . $e->getMessage());
            $_SESSION['error'] = 'Gagal memuat notifikasi';
            $this->redirect('/dashboard');
        }
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead($id)
    {
        if (!$this->isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            return;
        }

        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'];
            $result = $this->notifikasiModel->markAsRead($id, $userId);

            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            error_log("Mark notification read error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to mark notification as read']);
        }
    }

    /**
     * Get unread notification count
     */
    public function getNotificationCount()
    {
        if (!$this->isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['count' => 0]);
            return;
        }

        header('Content-Type: application/json');

        try {
            $userId = $_SESSION['user_id'];
            $count = $this->notifikasiModel->getUnreadCount($userId);

            echo json_encode(['count' => $count]);
        } catch (Exception $e) {
            error_log("Get notification count error: " . $e->getMessage());
            echo json_encode(['count' => 0]);
        }
    }
}
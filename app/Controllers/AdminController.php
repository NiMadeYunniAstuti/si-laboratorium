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
     * Show dashboard page
     */
    public function dashboard()
    {
        // Authentication disabled for now
        // Check if user is logged in
        // if (!$this->isLoggedIn()) {
        //     $_SESSION['error'] = 'Anda harus login untuk mengakses dashboard';
        //     $this->redirect('/login');
        //     return;
        // }

        // Set default user data when auth is disabled
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

        // Fallback: Add sample data if no data found (for testing)
        if (empty($data['recentPeminjaman'])) {
            error_log("No peminjaman data found, using fallback sample data");
            $data['recentPeminjaman'] = [
                [
                    'id' => 1,
                    'user_id' => 2,
                    'alat_id' => 1,
                    'nama_alat' => 'Oscilloscope Tektronix TBS1102C',
                    'kode_alat' => 'ELE-001',
                    'user_name' => 'John Doe',
                    'user_email' => 'john.doe@lbms.com',
                    'tanggal_pinjam' => '2024-01-15',
                    'tanggal_kembali' => '2024-01-20',
                    'status' => 'SELESAI',
                    'keterangan' => 'Peminjaman untuk praktikum elektronika dasar',
                    'surat' => 'uploads/dokumen/surat-peminjaman-001.pdf',
                    'created_at' => '2024-01-15 09:00:00'
                ],
                [
                    'id' => 2,
                    'user_id' => 3,
                    'alat_id' => 2,
                    'nama_alat' => 'Multimeter Digital Fluke 87V',
                    'kode_alat' => 'ELE-002',
                    'user_name' => 'Jane Smith',
                    'user_email' => 'jane.smith@lbms.com',
                    'tanggal_pinjam' => '2024-01-16',
                    'tanggal_kembali' => '2024-01-23',
                    'status' => 'DIPINJAM',
                    'keterangan' => 'Pengukuran resistansi komponen elektronik',
                    'surat' => 'uploads/dokumen/surat-peminjaman-002.pdf',
                    'created_at' => '2024-01-16 10:00:00'
                ],
                [
                    'id' => 3,
                    'user_id' => 2,
                    'alat_id' => 3,
                    'nama_alat' => 'Power Supply Rigol DP832',
                    'kode_alat' => 'ELE-003',
                    'user_name' => 'John Doe',
                    'user_email' => 'john.doe@lbms.com',
                    'tanggal_pinjam' => '2024-01-19',
                    'tanggal_kembali' => '2024-01-26',
                    'status' => 'PENDING',
                    'keterangan' => 'Pengukuran pH air sungai untuk penelitian lingkungan',
                    'surat' => 'uploads/dokumen/surat-peminjaman-005.pdf',
                    'created_at' => '2024-01-19 11:00:00'
                ]
            ];
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

        // Authentication disabled for now
        // if (!$this->isLoggedIn()) {
        //     echo json_encode(['error' => 'Unauthorized']);
        //     return;
        // }

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
                $this->redirect('/dashboard/profile');
                return;
            }

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['error'] = 'Email tidak valid';
                $this->redirect('/dashboard/profile');
                return;
            }

            // Check if email exists for other user
            if ($this->userModel->emailExists($email, $userId)) {
                $_SESSION['error'] = 'Email sudah digunakan oleh pengguna lain';
                $this->redirect('/dashboard/profile');
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

            if (strlen($newPassword) < 8) {
                $_SESSION['error'] = 'Password baru minimal 8 karakter';
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
            $nik = trim($_POST['nik'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            $role = $_POST['role'] ?? 'USER';

            // Validation
            if (empty($nik) || empty($name) || empty($email) || empty($phone) || empty($password)) {
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

            if (strlen($password) < 8) {
                $_SESSION['error'] = 'Password minimal 8 karakter';
                $this->redirect('/users/new');
                return;
            }

            // Create user
            $userData = [
                'nik' => htmlspecialchars($nik),
                'name' => htmlspecialchars($name),
                'email' => strtolower($email),
                'phone' => htmlspecialchars($phone),
                'password' => password_hash($password, PASSWORD_DEFAULT),
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
     * Show Data User page
     */
    public function dataUsers()
    {
        // Set default user data when auth is disabled
        $data = [
            'title' => 'Data User - LBMS',
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

        $this->view('users/index', $data);
    }

    /**
     * Show Manajemen Alat page
     */
    public function manajemenAlat()
    {
        // Set default user data when auth is disabled
        $data = [
            'title' => 'Manajemen Alat - LBMS',
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

        $this->view('alat/index', $data);
    }

    /**
     * Show new alat page
     */
    public function newAlat()
    {
        $data = [
            'title' => 'Tambah Alat Baru - LBMS',
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
     * Show new user page
     */
    public function newUser()
    {
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
            $data = [
                'kode_alat' => $_POST['kode_alat'] ?? '',
                'nama_alat' => $_POST['nama_alat'] ?? '',
                'kategori_id' => $_POST['kategori_id'] ?? null,
                'tipe_id' => $_POST['tipe_id'] ?? null,
                'tahun_pembelian' => $_POST['tahun_pembelian'] ?? null,
                'jumlah' => $_POST['jumlah'] ?? 1,
                'kondisi' => $_POST['kondisi'] ?? 'BAIK',
                'status' => $_POST['status'] ?? 'TERSEDIA',
                'deskripsi' => $_POST['deskripsi'] ?? ''
            ];

            // Handle file upload
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'public/images/alat/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = time() . '_' . basename($_FILES['gambar']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    $data['gambar'] = $fileName;
                }
            }

            try {
                $this->alatModel->create($data);
                $_SESSION['success'] = 'Alat berhasil ditambahkan';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Gagal menambahkan alat: ' . $e->getMessage();
            }

            $this->redirect('/alat');
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
                'kode_alat' => $_POST['kode_alat'] ?? '',
                'merek' => $_POST['merek'] ?? '',
                'kategori_id' => $_POST['kategori_id'] ?? '',
                'tipe_id' => $_POST['tipe_id'] ?? '',
                'jumlah' => $_POST['jumlah'] ?? 1,
                'kondisi' => $_POST['kondisi'] ?? 'BAIK',
                'status' => $_POST['status'] ?? 'TERSEDIA',
                'keterangan' => $_POST['keterangan'] ?? ''
            ];

            // Handle file upload if new image provided
            if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = 'public/images/alat/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $fileName = time() . '_' . basename($_FILES['gambar']['name']);
                $targetPath = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetPath)) {
                    $data['gambar'] = $fileName;
                }
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            try {
                $this->alatModel->update($id, $data);

                // Create notification for updated alat
                $this->notifikasiModel->createNotification(
                    'Alat Diperbarui',
                    "Alat '{$data['nama_alat']}' telah diperbarui",
                    'admin',
                    [$this->currentUser['id']]
                );

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
     * Show new peminjaman page
     */
    public function newPeminjaman()
    {
        $data = [
            'title' => 'Ajukan Peminjaman Baru - LBMS',
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

        $this->view('peminjaman/new', $data);
    }

    /**
     * Show peminjaman detail page
     */
    public function detailPeminjaman($id)
    {
        // Get peminjaman data - would normally fetch from database
        $peminjamanData = [
            'id' => $id,
            'nama_peminjam' => 'John Doe',
            'nim_nip' => '123456789012',
            'keperluan' => 'Praktikum Mikrobiologi untuk penelitian kultur bakteri',
            'alat_id' => '1',
            'jumlah' => '1',
            'tanggal_pinjam' => '2024-12-15',
            'tanggal_kembali' => '2024-12-22',
            'tanggal_selesai' => null,
            'status' => 'DIPINJAM',
            'surat' => 'SP/001/LAB/2024',
            'created_at' => '2024-12-15 09:00:00'
        ];

        $data = [
            'title' => 'Detail Peminjaman - LBMS',
            'user' => [
                'name' => $_SESSION['user_name'] ?? 'Admin User',
                'email' => $_SESSION['user_email'] ?? 'admin@lbms.com',
                'role' => $_SESSION['user_role'] ?? 'ADMIN'
            ],
            'peminjamanDetail' => $peminjamanData,
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
                $data = [
                    'nama_peminjam' => $_POST['nama_peminjam'] ?? '',
                    'nim_nip' => $_POST['nim_nip'] ?? '',
                    'alat_id' => $_POST['alat_id'] ?? '',
                    'jumlah' => $_POST['jumlah'] ?? 1,
                    'tanggal_pinjam' => $_POST['tanggal_pinjam'] ?? '',
                    'tanggal_kembali' => $_POST['tanggal_kembali'] ?? '',
                    'keperluan' => $_POST['keperluan'] ?? '',
                    'surat' => $_POST['surat'] ?? '',
                    'status' => 'PENDING'
                ];

                // Validation
                if (empty($data['nama_peminjam']) || empty($data['nim_nip']) || empty($data['alat_id'])) {
                    $_SESSION['error'] = 'Semua field wajib diisi';
                    $this->redirect('/peminjaman/new');
                    return;
                }

                // Create peminjaman record
                if ($this->peminjamanModel->create($data)) {
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
        // Set default user data when auth is disabled
        $data = [
            'title' => 'Peminjaman - LBMS',
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
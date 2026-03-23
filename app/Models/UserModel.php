<?php

/**
 * Model untuk mengelola data pengguna (user)
 */
class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'status',
        'foto',
        'last_login'
    ];
    protected $hidden = ['password_hash'];

    /** Cari user berdasarkan email */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL LIMIT 1";
        return $this->db->fetch($sql, ['email' => $email]);
    }

    /** Ambil status user berdasarkan ID */
    public function getStatusById($id)
    {
        $sql = "SELECT status FROM {$this->table} WHERE id = :id AND deleted_at IS NULL LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Ambil daftar user dengan pagination dan filter pencarian */
    public function getUsersPaginated($page = 1, $limit = 10, $search = '', $status = '', $role = '')
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE deleted_at IS NULL';
        $params = [];

        if (!empty($search)) {
            $whereClause .= ' AND (name LIKE :search OR email LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereClause .= ' AND status = :status';
            $params['status'] = $status;
        }

        if (!empty($role)) {
            $whereClause .= ' AND role = :role';
            $params['role'] = $role;
        }

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $whereClause";
        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        $sql = "SELECT * FROM {$this->table}
                $whereClause
                ORDER BY created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->query($sql, array_merge($params, [
            'limit' => $limit,
            'offset' => $offset
        ]));

        $records = $stmt ? $stmt->fetchAll() : [];

        return [
            'data' => $records,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }

    /** Ambil detail user beserta jumlah peminjamannya */
    public function getUserDetails($id)
    {
        $sql = "SELECT u.*, COUNT(p.id) as total_peminjaman,
                       COUNT(CASE WHEN p.status = 'DIPINJAM' THEN 1 END) as active_peminjaman
                FROM {$this->table} u
                LEFT JOIN peminjaman p ON u.id = p.user_id AND p.deleted_at IS NULL
                WHERE u.id = :id AND u.deleted_at IS NULL
                GROUP BY u.id";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Buat user baru (password otomatis di-hash) */
    public function createUser($data)
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        if (!isset($data['status'])) {
            $data['status'] = 'ACTIVE';
        }

        return $this->create($data);
    }

    /** Update data user (password otomatis di-hash kalau ada) */
    public function updateUser($id, $data)
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /** Ganti password user */
    public function updatePassword($userId, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password_hash = :password_hash, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->query($sql, ['password_hash' => $passwordHash, 'id' => $userId]);

        if ($stmt === false) {
            error_log("Gagal update password untuk user {$userId}");
            return false;
        }

        return $stmt->rowCount() > 0;
    }

    /** Catat waktu login terakhir */
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id AND deleted_at IS NULL";
        return $this->db->query($sql, ['id' => $userId]);
    }

    /** Toggle status user (ACTIVE jadi INACTIVE, atau sebaliknya) */
    public function toggleStatus($id)
    {
        $sql = "SELECT status FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['id' => $id]);

        if (!$result) {
            return false;
        }

        $newStatus = ($result['status'] === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        return $this->updateStatus($id, $newStatus);
    }

    /** Update status user */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";
        return $this->db->query($sql, ['status' => $status, 'id' => $id]);
    }

    /** Ambil daftar user berdasarkan role */
    public function getUsersByRole($role)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE role = :role AND deleted_at IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql, ['role' => $role]);
    }

    /** Ambil daftar user berdasarkan status */
    public function getUsersByStatus($status)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = :status AND deleted_at IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    /** Cari user berdasarkan nama atau email */
    public function searchUsers($query, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE (name LIKE :query OR email LIKE :query)
                  AND deleted_at IS NULL
                ORDER BY name
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ]);
    }

    /** Verifikasi password user */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /** Cek apakah email sudah terdaftar */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /** Ambil statistik user (total, per role, per status, dll) */
    public function getUserStatistics()
    {
        $stats = [];

        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['total'] = $result['total'] ?? 0;

        $sql = "SELECT role, COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY role";
        $roleResults = $this->db->fetchAll($sql);
        foreach ($roleResults as $row) {
            $stats['by_role'][strtolower($row['role'])] = $row['total'];
        }

        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY status";
        $statusResults = $this->db->fetchAll($sql);
        foreach ($statusResults as $row) {
            $stats['by_status'][strtolower($row['status'])] = $row['total'];
        }

        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['active_last_30_days'] = $result['total'] ?? 0;

        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE MONTH(created_at) = MONTH(CURDATE())
                  AND YEAR(created_at) = YEAR(CURDATE())
                  AND deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['new_this_month'] = $result['total'] ?? 0;

        return $stats;
    }

    /** Update foto profil user */
    public function updatePhoto($id, $foto)
    {
        $sql = "UPDATE {$this->table} SET foto = :foto, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";
        return $this->db->query($sql, ['foto' => $foto, 'id' => $id]);
    }

    /** Hapus user (soft delete, hanya tandai deleted_at) */
    public function softDelete($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /** Ambil user terbaru */
    public function getRecentUsers($limit = 5)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE deleted_at IS NULL
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }
}

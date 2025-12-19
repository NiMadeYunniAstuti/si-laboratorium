<?php

/**
 * User Model
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

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND deletedAt IS NULL LIMIT 1";
        return $this->db->fetch($sql, ['email' => $email]);
    }

    /**
     * Get users with pagination and filters
     */
    public function getUsersPaginated($page = 1, $limit = 10, $search = '', $status = '', $role = '')
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE deletedAt IS NULL';
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

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} $whereClause";
        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        // Get records
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

    /**
     * Get user by ID with details
     */
    public function getUserDetails($id)
    {
        $sql = "SELECT u.*, COUNT(p.id) as total_peminjaman,
                       COUNT(CASE WHEN p.status = 'DIPINJAM' THEN 1 END) as active_peminjaman
                FROM {$this->table} u
                LEFT JOIN peminjaman p ON u.id = p.user_id AND p.deletedAt IS NULL
                WHERE u.id = :id AND u.deletedAt IS NULL
                GROUP BY u.id";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Create new user with password hashing
     */
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

    /**
     * Update user
     */
    public function updateUser($id, $data)
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Update user password
     */
    public function updatePassword($userId, $password)
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE {$this->table} SET password_hash = :password_hash, updated_at = NOW()
                WHERE id = :id AND deletedAt IS NULL";
        return $this->db->query($sql, ['password_hash' => $passwordHash, 'id' => $userId]);
    }

    /**
     * Update user last login
     */
    public function updateLastLogin($userId)
    {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = :id AND deletedAt IS NULL";
        return $this->db->query($sql, ['id' => $userId]);
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id)
    {
        $sql = "SELECT status FROM {$this->table} WHERE id = :id AND deletedAt IS NULL";
        $result = $this->db->fetch($sql, ['id' => $id]);

        if (!$result) {
            return false;
        }

        $newStatus = ($result['status'] === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';
        return $this->updateStatus($id, $newStatus);
    }

    /**
     * Update user status
     */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW()
                WHERE id = :id AND deletedAt IS NULL";
        return $this->db->query($sql, ['status' => $status, 'id' => $id]);
    }

    /**
     * Get users by role
     */
    public function getUsersByRole($role)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE role = :role AND deletedAt IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql, ['role' => $role]);
    }

    /**
     * Get users by status
     */
    public function getUsersByStatus($status)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE status = :status AND deletedAt IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    /**
     * Search users
     */
    public function searchUsers($query, $limit = 20)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE (name LIKE :query OR email LIKE :query)
                  AND deletedAt IS NULL
                ORDER BY name
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ]);
    }

    /**
     * Verify user password
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email AND deletedAt IS NULL";
        $params = ['email' => $email];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /**
     * Get user statistics
     */
    public function getUserStatistics()
    {
        $stats = [];

        // Total users
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deletedAt IS NULL";
        $result = $this->db->fetch($sql);
        $stats['total'] = $result['total'] ?? 0;

        // By role
        $sql = "SELECT role, COUNT(*) as total FROM {$this->table} WHERE deletedAt IS NULL GROUP BY role";
        $roleResults = $this->db->fetchAll($sql);
        foreach ($roleResults as $row) {
            $stats['by_role'][strtolower($row['role'])] = $row['total'];
        }

        // By status
        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} WHERE deletedAt IS NULL GROUP BY status";
        $statusResults = $this->db->fetchAll($sql);
        foreach ($statusResults as $row) {
            $stats['by_status'][strtolower($row['status'])] = $row['total'];
        }

        // Active users (logged in within last 30 days)
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE last_login >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND deletedAt IS NULL";
        $result = $this->db->fetch($sql);
        $stats['active_last_30_days'] = $result['total'] ?? 0;

        // New users this month
        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE MONTH(created_at) = MONTH(CURDATE())
                  AND YEAR(created_at) = YEAR(CURDATE())
                  AND deletedAt IS NULL";
        $result = $this->db->fetch($sql);
        $stats['new_this_month'] = $result['total'] ?? 0;

        return $stats;
    }

    /**
     * Update user photo
     */
    public function updatePhoto($id, $foto)
    {
        $sql = "UPDATE {$this->table} SET foto = :foto, updated_at = NOW()
                WHERE id = :id AND deletedAt IS NULL";
        return $this->db->query($sql, ['foto' => $foto, 'id' => $id]);
    }

    /**
     * Soft delete user
     */
    public function softDelete($id)
    {
        $sql = "UPDATE {$this->table} SET deletedAt = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Get recent users
     */
    public function getRecentUsers($limit = 5)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE deletedAt IS NULL
                ORDER BY created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }
}
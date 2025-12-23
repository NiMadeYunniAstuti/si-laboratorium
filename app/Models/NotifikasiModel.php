<?php

/**
 * Notifikasi Model
 */
class NotifikasiModel extends BaseModel
{
    protected $table = 'notifikasi';
    protected $fillable = [
        'title',
        'description',
        'peminjaman_id',
        'is_read'
    ];

    /**
     * Create new notification
     */
    public function createNotification($title, $description = null, $userIds = [], $peminjamanId = null)
    {
        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO {$this->table} (title, description, peminjaman_id, is_read, created_at)
                    VALUES (:title, :description, :peminjaman_id, FALSE, NOW())";
            $this->db->query($sql, [
                'title' => $title,
                'description' => $description,
                'peminjaman_id' => $peminjamanId
            ]);

            $notificationId = $this->db->lastInsertId();

            if (!empty($userIds)) {
                foreach ($userIds as $userId) {
                    $sql = "INSERT INTO notifikasi_users (notifikasi_id, user_id, created_at)
                            VALUES (:notif_id, :user_id, NOW())";
                    $this->db->query($sql, [
                        'notif_id' => $notificationId,
                        'user_id' => $userId
                    ]);
                }
            }

            $this->db->commit();
            return $notificationId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get notifications for user
     */
    public function getNotificationsForUser($userId, $limit = 10, $unreadOnly = false)
    {
        $whereClause = "WHERE nu.user_id = :user_id AND n.deleted_at IS NULL";
        $params = ['user_id' => $userId];

        if ($unreadOnly) {
            $whereClause .= " AND nu.is_read = FALSE AND n.is_read = FALSE";
        }

        $sql = "SELECT n.*, nu.is_read as user_read, nu.created_at as assigned_at
                FROM {$this->table} n
                INNER JOIN notifikasi_users nu ON n.id = nu.notifikasi_id
                {$whereClause}
                ORDER BY n.created_at DESC
                LIMIT :limit";

        return $this->db->fetchAll($sql, array_merge($params, ['limit' => $limit]));
    }

    /**
     * Alias for getNotificationsForUser for consistency with other models
     */
    public function getNotificationsByUser($userId, $limit = 10, $unreadOnly = false)
    {
        return $this->getNotificationsForUser($userId, $limit, $unreadOnly);
    }

    /**
     * Mark notification as read for user
     */
    public function markAsRead($notificationId, $userId)
    {
        $sql = "UPDATE notifikasi_users
                SET is_read = TRUE
                WHERE notifikasi_id = :notif_id AND user_id = :user_id";

        return $this->db->query($sql, [
            'notif_id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    /**
     * Mark all notifications as read for user
     */
    public function markAllAsRead($userId)
    {
        $sql = "UPDATE notifikasi_users
                SET is_read = TRUE
                WHERE user_id = :user_id";

        return $this->db->query($sql, ['user_id' => $userId]);
    }

    /**
     * Get unread count for user
     */
    public function getUnreadCount($userId)
    {
        $sql = "SELECT COUNT(*) as count
                FROM {$this->table} n
                INNER JOIN notifikasi_users nu ON n.id = nu.notifikasi_id
                WHERE nu.user_id = :user_id
                  AND nu.is_read = FALSE
                  AND n.is_read = FALSE
                  AND n.deleted_at IS NULL";

        $result = $this->db->fetch($sql, ['user_id' => $userId]);
        return $result['count'] ?? 0;
    }

    /**
     * Delete notification
     */
    public function deleteNotification($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Get notification by ID with details
     */
    public function getNotificationDetails($id)
    {
        $sql = "SELECT n.*,
                       p.id as peminjaman_id, p.tanggal_pinjam, p.status as peminjaman_status,
                       a.nama_alat, a.kode_alat,
                       u.name as user_name, u.email as user_email
                FROM {$this->table} n
                LEFT JOIN peminjaman p ON n.peminjaman_id = p.id AND p.deleted_at IS NULL
                LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                WHERE n.id = :id AND n.deleted_at IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Get notifications by peminjaman
     */
    public function getNotificationsByPeminjaman($peminjamanId)
    {
        $sql = "SELECT n.* FROM {$this->table} n
                WHERE n.peminjaman_id = :peminjaman_id
                  AND n.deleted_at IS NULL
                ORDER BY n.created_at DESC";

        return $this->db->fetchAll($sql, ['peminjaman_id' => $peminjamanId]);
    }

    /**
     * Create system-wide notification (for all users)
     */
    public function createSystemNotification($title, $description = null, $role = null)
    {
        $this->db->beginTransaction();

        try {
            $sql = "INSERT INTO {$this->table} (title, description, peminjaman_id, is_read, created_at)
                    VALUES (:title, :description, NULL, FALSE, NOW())";
            $this->db->query($sql, [
                'title' => $title,
                'description' => $description
            ]);

            $notificationId = $this->db->lastInsertId();

            $userWhere = $role ? "WHERE role = :role AND deleted_at IS NULL" : "WHERE deleted_at IS NULL";
            $userParams = $role ? ['role' => $role] : [];

            $usersSql = "SELECT id FROM users {$userWhere}";
            $users = $this->db->fetchAll($usersSql, $userParams);

            foreach ($users as $user) {
                $sql = "INSERT INTO notifikasi_users (notifikasi_id, user_id, created_at)
                        VALUES (:notif_id, :user_id, NOW())";
                $this->db->query($sql, [
                    'notif_id' => $notificationId,
                    'user_id' => $user['id']
                ]);
            }

            $this->db->commit();
            return $notificationId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get paginated notifications for user
     */
    public function getNotificationsPaginated($userId, $page = 1, $limit = 10)
    {
        $offset = ($page - 1) * $limit;

        $countSql = "SELECT COUNT(*) as total
                     FROM {$this->table} n
                     INNER JOIN notifikasi_users nu ON n.id = nu.notifikasi_id
                     WHERE nu.user_id = :user_id AND n.deleted_at IS NULL";
        $countResult = $this->db->fetch($countSql, ['user_id' => $userId]);
        $total = $countResult['total'] ?? 0;

        $sql = "SELECT n.*, nu.is_read as user_read, nu.created_at as assigned_at
                FROM {$this->table} n
                INNER JOIN notifikasi_users nu ON n.id = nu.notifikasi_id
                WHERE nu.user_id = :user_id AND n.deleted_at IS NULL
                ORDER BY n.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->query($sql, [
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);

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
     * Delete notification for specific user
     */
    public function deleteNotificationForUser($notificationId, $userId)
    {
        $sql = "DELETE FROM notifikasi_users
                WHERE notifikasi_id = :notif_id AND user_id = :user_id";

        return $this->db->query($sql, [
            'notif_id' => $notificationId,
            'user_id' => $userId
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStatistics()
    {
        $stats = [];

        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['total'] = $result['total'] ?? 0;

        $sql = "SELECT is_read, COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY is_read";
        $statusResults = $this->db->fetchAll($sql);
        foreach ($statusResults as $row) {
            $stats['by_read_status'][$row['is_read'] ? 'read' : 'unread'] = $row['total'];
        }

        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE peminjaman_id IS NOT NULL AND deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['linked_to_peminjaman'] = $result['total'] ?? 0;

        $sql = "SELECT COUNT(*) as total FROM {$this->table}
                WHERE DATE(created_at) = CURDATE() AND deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['created_today'] = $result['total'] ?? 0;

        return $stats;
    }

    /**
     * Mark notification as read globally (for all users)
     */
    public function markAsReadGlobally($notificationId)
    {
        $sql = "UPDATE {$this->table} SET is_read = TRUE, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";

        return $this->db->query($sql, ['id' => $notificationId]);
    }

    /**
     * Get recent notifications for all users (admin view)
     */
    public function getRecentNotifications($limit = 20)
    {
        $sql = "SELECT n.*,
                       COUNT(nu.user_id) as recipient_count,
                       COUNT(CASE WHEN nu.is_read = FALSE THEN 1 END) as unread_count
                FROM {$this->table} n
                LEFT JOIN notifikasi_users nu ON n.id = nu.notifikasi_id
                WHERE n.deleted_at IS NULL
                GROUP BY n.id
                ORDER BY n.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }
}
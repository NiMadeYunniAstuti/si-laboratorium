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
        'is_read'
    ];

    /**
     * Create new notification
     */
    public function createNotification($title, $description = null, $userIds = [])
    {
        $this->db->beginTransaction();

        try {
            // Create notification
            $sql = "INSERT INTO {$this->table} (title, description, is_read, created_at)
                    VALUES (:title, :description, FALSE, NOW())";
            $this->db->query($sql, [
                'title' => $title,
                'description' => $description
            ]);

            $notificationId = $this->db->lastInsertId();

            // Assign to users
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
        $whereClause = "WHERE nu.user_id = :user_id AND n.deletedAt IS NULL";
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
                  AND n.deletedAt IS NULL";

        $result = $this->db->fetch($sql, ['user_id' => $userId]);
        return $result['count'] ?? 0;
    }

    /**
     * Delete notification
     */
    public function deleteNotification($id)
    {
        $sql = "UPDATE {$this->table} SET deletedAt = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}
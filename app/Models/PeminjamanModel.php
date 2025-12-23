<?php

/**
 * Peminjaman Model
 */
class PeminjamanModel extends BaseModel
{
    protected $table = 'peminjaman';
    protected $fillable = [
        'user_id',
        'nama_peminjam',
        'alat_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_pengembalian',
        'status',
        'keterangan'
    ];

    /**
     * Get peminjaman with pagination and filters
     */
    public function getPeminjamanPaginated($page = 1, $limit = 10, $search = '', $status = '')
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE p.deleted_at IS NULL';
        $params = [];

        if (!empty($search)) {
            $whereClause .= ' AND (u.name LIKE :search OR a.nama_alat LIKE :search OR a.kode_alat LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereClause .= ' AND p.status = :status';
            $params['status'] = $status;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM peminjaman p
                     LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                     LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                     $whereClause";

        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        // Get records
        $sql = "SELECT p.*, u.name as user_name, u.email as user_email, u.foto as user_foto,
                       a.nama_alat, a.kode_alat, k.name as kategori_name, t.name as tipe_name
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN kategori_alat k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                LEFT JOIN tipe_alat t ON a.tipe_id = t.id AND t.deleted_at IS NULL
                $whereClause
                ORDER BY p.created_at DESC
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
     * Get peminjaman with related details
     */
    public function getPeminjamanDetails($id)
    {
        $sql = "SELECT p.*, u.name as user_name, u.email as user_email, u.foto as user_foto,
                       a.nama_alat, a.kode_alat, a.jumlah, a.kondisi, a.gambar,
                       k.name as kategori_name, t.name as tipe_name
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN kategori_alat k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                LEFT JOIN tipe_alat t ON a.tipe_id = t.id AND t.deleted_at IS NULL
                WHERE p.id = :id AND p.deleted_at IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Create new peminjaman
     */
    public function createPeminjaman($data)
    {
        // Start transaction
        $this->db->beginTransaction();

        try {
            // Set created_at if not provided
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            // Create peminjaman record
            $sql = "INSERT INTO {$this->table} (user_id, nama_peminjam, alat_id, tanggal_pinjam, tanggal_kembali, status, keterangan, created_at)
                    VALUES (:user_id, :nama_peminjam, :alat_id, :tanggal_pinjam, :tanggal_kembali, :status, :keterangan, :created_at)";

            $this->db->query($sql, $data);
            $peminjamanId = $this->db->lastInsertId();

            // Update alat status if not pending
            if ($data['status'] === 'DIPINJAM') {
                $this->db->query("UPDATE alat SET status = 'DIPINJAM', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL", [
                    'id' => $data['alat_id']
                ]);
            }

            $this->db->commit();
            return $peminjamanId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Update peminjaman status
     */
    public function updateStatus($id, $status, $tanggalPengembalian = null)
    {
        $this->db->beginTransaction();

        try {
            $peminjaman = $this->getPeminjamanDetails($id);
            if (!$peminjaman) {
                throw new Exception("Peminjaman not found");
            }

            // Update peminjaman
            $updateData = ['status' => $status, 'updated_at' => date('Y-m-d H:i:s')];
            if ($tanggalPengembalian) {
                $updateData['tanggal_pengembalian'] = $tanggalPengembalian;
            }

            $setClause = [];
            $params = ['id' => $id];
            foreach ($updateData as $key => $value) {
                $setClause[] = "$key = :$key";
                $params[$key] = $value;
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id AND deleted_at IS NULL";
            $this->db->query($sql, $params);

            // Update alat status
            if ($status === 'SELESAI') {
                $this->db->query("UPDATE alat SET status = 'TERSEDIA', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL", [
                    'id' => $peminjaman['alat_id']
                ]);
            } elseif (in_array($status, ['DIPINJAM', 'DISETUJUI'], true) && $peminjaman['status'] === 'PENDING') {
                $this->db->query("UPDATE alat SET status = 'DIPINJAM', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL", [
                    'id' => $peminjaman['alat_id']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Return asset
     */
    public function returnAsset($id, $kondisi = null)
    {
        $this->db->beginTransaction();

        try {
            $peminjaman = $this->getPeminjamanDetails($id);
            if (!$peminjaman) {
                throw new Exception("Peminjaman not found");
            }

            // Update peminjaman
            $updateData = [
                'status' => 'SELESAI',
                'tanggal_pengembalian' => date('Y-m-d'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $setClause = [];
            $params = ['id' => $id];
            foreach ($updateData as $key => $value) {
                $setClause[] = "$key = :$key";
                $params[$key] = $value;
            }

            $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . " WHERE id = :id AND deleted_at IS NULL";
            $this->db->query($sql, $params);

            // Update alat status and condition
            $assetUpdate = ['status' => 'TERSEDIA', 'updated_at' => date('Y-m-d H:i:s')];
            if ($kondisi) {
                $assetUpdate['kondisi'] = $kondisi;
            }

            $setClause = [];
            $params = ['id' => $peminjaman['alat_id']];
            foreach ($assetUpdate as $key => $value) {
                $setClause[] = "$key = :$key";
                $params[$key] = $value;
            }

            $this->db->query("UPDATE alat SET " . implode(', ', $setClause) . " WHERE id = :id AND deleted_at IS NULL", $params);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Get peminjaman by status
     */
    public function getPeminjamanByStatus($status)
    {
        $sql = "SELECT p.*, u.name as user_name, u.email as user_email,
                       a.nama_alat, a.kode_alat, k.name as kategori_name
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN kategori_alat k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE p.status = :status AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    /**
     * Get peminjaman by user
     */
    public function getPeminjamanByUser($userId, $status = null, $limit = null)
    {
        $sql = "SELECT p.*, a.nama_alat, a.kode_alat, k.name as kategori_name, t.name as tipe_name
                FROM peminjaman p
                LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN kategori_alat k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                LEFT JOIN tipe_alat t ON a.tipe_id = t.id AND t.deleted_at IS NULL
                WHERE p.user_id = :user_id AND p.deleted_at IS NULL";

        $params = ['user_id' => $userId];

        if ($status) {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC";

        if ($limit) {
            $sql .= " LIMIT :limit";
            $params['limit'] = $limit;
        }

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get peminjaman by asset
     */
    public function getPeminjamanByAlat($alatId, $status = null)
    {
        $sql = "SELECT p.*, u.name as user_name, u.email, u.foto
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                WHERE p.alat_id = :alat_id AND p.deleted_at IS NULL";

        $params = ['alat_id' => $alatId];

        if ($status) {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get overdue peminjaman
     */
    public function getOverduePeminjaman()
    {
        $sql = "SELECT p.*, u.name as user_name, u.email, u.foto,
                       a.nama_alat, a.kode_alat, k.name as kategori_name
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN kategori_alat k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE p.status = 'DIPINJAM'
                  AND p.tanggal_kembali < CURDATE()
                  AND p.deleted_at IS NULL
                ORDER BY p.tanggal_kembali ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get peminjaman statistics
     */
    public function getPeminjamanStatistics()
    {
        $stats = [];

        // Total peminjaman
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['total'] = $result['total'] ?? 0;

        // By status
        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY status";
        $statusResults = $this->db->fetchAll($sql);
        foreach ($statusResults as $row) {
            $stats['by_status'][strtolower($row['status'])] = $row['total'];
        }

        // Overdue count
        $overdueSql = "SELECT COUNT(*) as count FROM peminjaman
                      WHERE status = 'DIPINJAM'
                        AND tanggal_kembali < CURDATE()
                        AND deleted_at IS NULL";
        $overdueResult = $this->db->fetch($overdueSql);
        $stats['overdue'] = $overdueResult['count'] ?? 0;

        // Today's peminjaman
        $todaySql = "SELECT COUNT(*) as count FROM peminjaman
                    WHERE DATE(tanggal_pinjam) = CURDATE() AND deleted_at IS NULL";
        $todayResult = $this->db->fetch($todaySql);
        $stats['today'] = $todayResult['count'] ?? 0;

        // This month's peminjaman
        $monthSql = "SELECT COUNT(*) as count FROM peminjaman
                    WHERE MONTH(tanggal_pinjam) = MONTH(CURDATE())
                      AND YEAR(tanggal_pinjam) = YEAR(CURDATE())
                      AND deleted_at IS NULL";
        $monthResult = $this->db->fetch($monthSql);
        $stats['this_month'] = $monthResult['count'] ?? 0;

        return $stats;
    }

    /**
     * Check if alat is available for borrowing
     */
    public function isAlatAvailable($alatId)
    {
        $sql = "SELECT status FROM alat WHERE id = :id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['id' => $alatId]);

        if (!$result) {
            return false;
        }

        return $result['status'] === 'TERSEDIA';
    }

    /**
     * Get active peminjaman for alat
     */
    public function getActivePeminjamanByAlat($alatId)
    {
        $sql = "SELECT p.*, u.name as user_name, u.foto
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                WHERE p.alat_id = :alat_id
                  AND p.status IN ('PENDING', 'DIPINJAM')
                  AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
                LIMIT 1";

        return $this->db->fetch($sql, ['alat_id' => $alatId]);
    }

    /**
     * Soft delete peminjaman
     */
    public function softDelete($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Get recent peminjaman for dashboard (simple version)
     */
    public function getRecentPeminjaman($limit = 10)
    {
        try {
            $sql = "SELECT p.*, u.name as user_name, u.email as user_email,
                           a.nama_alat, a.kode_alat
                    FROM peminjaman p
                    LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                    LEFT JOIN alat a ON p.alat_id = a.id AND a.deleted_at IS NULL
                    WHERE p.deleted_at IS NULL
                    ORDER BY p.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->query($sql, ['limit' => $limit]);
            $records = $stmt ? $stmt->fetchAll() : [];

            error_log("Simple getRecentPeminjaman result count: " . count($records));
            return $records;
        } catch (Exception $e) {
            error_log("Error in getRecentPeminjaman: " . $e->getMessage());
            return [];
        }
    }
}

<?php

/**
 * Model untuk mengelola data peminjaman alat dan ruangan
 */
class PeminjamanModel extends BaseModel
{
    protected $table = 'peminjaman';
    protected $fillable = [
        'user_id',
        'nama_peminjam',
        'item_type',
        'item_id',
        'tanggal_pinjam',
        'tanggal_kembali',
        'tanggal_pengembalian',
        'status',
        'keterangan'
    ];

    /** Ambil daftar peminjaman dengan pagination dan filter */
    public function getPeminjamanPaginated($page = 1, $limit = 10, $search = '', $status = '')
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE p.deleted_at IS NULL';
        $params = [];

        if (!empty($search)) {
            $whereClause .= ' AND (u.name LIKE :search OR a.nama_alat LIKE :search OR a.kode_alat LIKE :search OR r.nama_ruangan LIKE :search OR r.kode_ruangan LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereClause .= ' AND p.status = :status';
            $params['status'] = $status;
        }

        $countSql = "SELECT COUNT(*) as total FROM peminjaman p
                     LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                     LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                     LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                     $whereClause";

        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        $sql = "SELECT p.*, u.name as user_name, u.email as user_email, u.foto as user_foto,
                       COALESCE(a.nama_alat, r.nama_ruangan) as item_name,
                       COALESCE(a.kode_alat, r.kode_ruangan) as item_code,
                       k.name as kategori_name, p.item_type
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                LEFT JOIN kategori k ON COALESCE(a.kategori_id, r.kategori_id) = k.id AND k.deleted_at IS NULL
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

    /** Ambil detail peminjaman beserta info alat/ruangan dan user */
    public function getPeminjamanDetails($id)
    {
        $sql = "SELECT p.*, u.name as user_name, u.email as user_email, u.foto as user_foto,
                       a.nama_alat, a.kode_alat, a.jumlah, a.kondisi, a.gambar as alat_gambar,
                       r.nama_ruangan, r.kode_ruangan, r.gambar as ruangan_gambar,
                       k.name as kategori_name
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                LEFT JOIN kategori k ON COALESCE(a.kategori_id, r.kategori_id) = k.id AND k.deleted_at IS NULL
                WHERE p.id = :id AND p.deleted_at IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Buat peminjaman baru, otomatis update status alat/ruangan kalau langsung dipinjam */
    public function createPeminjaman($data)
    {
        $this->db->beginTransaction();
        try {
            if (!isset($data['created_at'])) {
                $data['created_at'] = date('Y-m-d H:i:s');
            }

            $sql = "INSERT INTO {$this->table} (user_id, nama_peminjam, item_type, item_id, tanggal_pinjam, tanggal_kembali, status, keterangan, created_at)
                    VALUES (:user_id, :nama_peminjam, :item_type, :item_id, :tanggal_pinjam, :tanggal_kembali, :status, :keterangan, :created_at)";

            $this->db->query($sql, $data);
            $peminjamanId = $this->db->lastInsertId();

            // Kalau langsung DIPINJAM, update status alat/ruangan jadi tidak tersedia
            if ($data['status'] === 'DIPINJAM') {
                $tableToUpdate = $data['item_type'] === 'ruangan' ? 'ruangan' : 'alat';
                $this->db->query("UPDATE {$tableToUpdate} SET status = 'DIPINJAM', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL", [
                    'id' => $data['item_id']
                ]);
            }

            $this->db->commit();
            return $peminjamanId;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /** Update status peminjaman dan sekaligus update status alat/ruangan */
    public function updateStatus($id, $status, $tanggalPengembalian = null)
    {
        $this->db->beginTransaction();

        try {
            $peminjaman = $this->getPeminjamanDetails($id);
            if (!$peminjaman) {
                throw new Exception("Peminjaman not found");
            }

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

            // Kalau selesai, kembalikan status alat/ruangan jadi TERSEDIA
            if ($status === 'SELESAI') {
                $tableToUpdate = $peminjaman['item_type'] === 'ruangan' ? 'ruangan' : 'alat';
                $this->db->query("UPDATE {$tableToUpdate} SET status = 'TERSEDIA', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL", [
                    'id' => $peminjaman['item_id']
                ]);
            // Kalau disetujui dari PENDING, update jadi DIPINJAM
            } elseif (in_array($status, ['DIPINJAM', 'DISETUJUI'], true) && $peminjaman['status'] === 'PENDING') {
                $tableToUpdate = $peminjaman['item_type'] === 'ruangan' ? 'ruangan' : 'alat';
                $this->db->query("UPDATE {$tableToUpdate} SET status = 'DIPINJAM', updated_at = NOW() WHERE id = :id AND deleted_at IS NULL", [
                    'id' => $peminjaman['item_id']
                ]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /** Proses pengembalian barang/ruangan */
    public function returnAsset($id, $kondisi = null)
    {
        $this->db->beginTransaction();

        try {
            $peminjaman = $this->getPeminjamanDetails($id);
            if (!$peminjaman) {
                throw new Exception("Peminjaman not found");
            }

            // Update peminjaman jadi SELESAI
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

            // Kembalikan status alat/ruangan jadi TERSEDIA
            $assetUpdate = ['status' => 'TERSEDIA', 'updated_at' => date('Y-m-d H:i:s')];
            if ($kondisi && $peminjaman['item_type'] === 'alat') {
                $assetUpdate['kondisi'] = $kondisi;
            }
            $setClause = [];
            $params = ['id' => $peminjaman['item_id']];
            foreach ($assetUpdate as $key => $value) {
                $setClause[] = "$key = :$key";
                $params[$key] = $value;
            }

            $tableToUpdate = $peminjaman['item_type'] === 'ruangan' ? 'ruangan' : 'alat';
            $this->db->query("UPDATE {$tableToUpdate} SET " . implode(', ', $setClause) . " WHERE id = :id AND deleted_at IS NULL", $params);
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /** Ambil daftar peminjaman berdasarkan status */
    public function getPeminjamanByStatus($status)
    {
        $sql = "SELECT p.*, u.name as user_name, u.email as user_email,
                       COALESCE(a.nama_alat, r.nama_ruangan) as item_name,
                       COALESCE(a.kode_alat, r.kode_ruangan) as item_code,
                       k.name as kategori_name, p.item_type
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                LEFT JOIN kategori k ON COALESCE(a.kategori_id, r.kategori_id) = k.id AND k.deleted_at IS NULL
                WHERE p.status = :status AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    /** Ambil daftar peminjaman milik user tertentu */
    public function getPeminjamanByUser($userId, $status = null, $limit = null)
    {
        $sql = "SELECT p.*,
                       COALESCE(a.nama_alat, r.nama_ruangan) as item_name,
                       COALESCE(a.kode_alat, r.kode_ruangan) as item_code,
                       k.name as kategori_name, p.item_type
                FROM peminjaman p
                LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                LEFT JOIN kategori k ON COALESCE(a.kategori_id, r.kategori_id) = k.id AND k.deleted_at IS NULL
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

    /** Ambil daftar peminjaman untuk item tertentu (alat atau ruangan) */
    public function getPeminjamanByItem($itemId, $itemType, $status = null)
    {
        $sql = "SELECT p.*, u.name as user_name, u.email, u.foto
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                WHERE p.item_id = :item_id AND p.item_type = :item_type AND p.deleted_at IS NULL";

        $params = ['item_id' => $itemId, 'item_type' => $itemType];

        if ($status) {
            $sql .= " AND p.status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY p.created_at DESC";

        return $this->db->fetchAll($sql, $params);
    }

    /** Ambil daftar peminjaman yang sudah melewati batas waktu pengembalian */
    public function getOverduePeminjaman()
    {
        $sql = "SELECT p.*, u.name as user_name, u.email, u.foto,
                       COALESCE(a.nama_alat, r.nama_ruangan) as item_name,
                       COALESCE(a.kode_alat, r.kode_ruangan) as item_code,
                       k.name as kategori_name, p.item_type
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                LEFT JOIN kategori k ON COALESCE(a.kategori_id, r.kategori_id) = k.id AND k.deleted_at IS NULL
                WHERE p.status = 'DIPINJAM'
                  AND p.tanggal_kembali < CURDATE()
                  AND p.deleted_at IS NULL
                ORDER BY p.tanggal_kembali ASC";

        return $this->db->fetchAll($sql);
    }

    /** Ambil statistik peminjaman (total, per status, terlambat, hari ini, bulan ini) */
    public function getPeminjamanStatistics()
    {
        $stats = [];

        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['total'] = $result['total'] ?? 0;

        $sql = "SELECT status, COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY status";
        $statusResults = $this->db->fetchAll($sql);
        foreach ($statusResults as $row) {
            $stats['by_status'][strtolower($row['status'])] = $row['total'];
        }

        $overdueSql = "SELECT COUNT(*) as count FROM peminjaman
                      WHERE status = 'DIPINJAM'
                        AND tanggal_kembali < CURDATE()
                        AND deleted_at IS NULL";
        $overdueResult = $this->db->fetch($overdueSql);
        $stats['overdue'] = $overdueResult['count'] ?? 0;

        $todaySql = "SELECT COUNT(*) as count FROM peminjaman
                    WHERE DATE(tanggal_pinjam) = CURDATE() AND deleted_at IS NULL";
        $todayResult = $this->db->fetch($todaySql);
        $stats['today'] = $todayResult['count'] ?? 0;

        $monthSql = "SELECT COUNT(*) as count FROM peminjaman
                    WHERE MONTH(tanggal_pinjam) = MONTH(CURDATE())
                      AND YEAR(tanggal_pinjam) = YEAR(CURDATE())
                      AND deleted_at IS NULL";
        $monthResult = $this->db->fetch($monthSql);
        $stats['this_month'] = $monthResult['count'] ?? 0;

        return $stats;
    }

    /** Cek apakah alat tersedia untuk dipinjam */
    public function isAlatAvailable($alatId)
    {
        $sql = "SELECT status FROM alat WHERE id = :id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['id' => $alatId]);

        if (!$result) {
            return false;
        }

        return $result['status'] === 'TERSEDIA';
    }

    /** Ambil peminjaman aktif (PENDING/DIPINJAM) untuk item tertentu */
    public function getActivePeminjamanByItem($itemId, $itemType)
    {
        $sql = "SELECT p.*, u.name as user_name, u.foto
                FROM peminjaman p
                LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                WHERE p.item_id = :item_id
                  AND p.item_type = :item_type
                  AND p.status IN ('PENDING', 'DIPINJAM')
                  AND p.deleted_at IS NULL
                ORDER BY p.created_at DESC
                LIMIT 1";

        return $this->db->fetch($sql, ['item_id' => $itemId, 'item_type' => $itemType]);
    }

    /** Hapus peminjaman (soft delete) */
    public function softDelete($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /** Ambil peminjaman terbaru untuk dashboard */
    public function getRecentPeminjaman($limit = 10)
    {
        try {
            $sql = "SELECT p.*, u.name as user_name, u.email as user_email,
                           COALESCE(a.nama_alat, r.nama_ruangan) as item_name,
                           COALESCE(a.kode_alat, r.kode_ruangan) as item_code,
                           p.item_type
                    FROM peminjaman p
                    LEFT JOIN users u ON p.user_id = u.id AND u.deleted_at IS NULL
                    LEFT JOIN alat a ON p.item_type = 'alat' AND p.item_id = a.id AND a.deleted_at IS NULL
                    LEFT JOIN ruangan r ON p.item_type = 'ruangan' AND p.item_id = r.id AND r.deleted_at IS NULL
                    WHERE p.deleted_at IS NULL
                    ORDER BY p.created_at DESC
                    LIMIT :limit";

            $stmt = $this->db->query($sql, ['limit' => $limit]);
            return $stmt ? $stmt->fetchAll() : [];
        } catch (Exception $e) {
            error_log("Error ambil peminjaman terbaru: " . $e->getMessage());
            return [];
        }
    }
}

<?php

/**
 * Model untuk mengelola data alat laboratorium
 */
class AlatModel extends BaseModel
{
    protected $table = 'alat';
    protected $fillable = [
        'kode_alat',
        'nama_alat',
        'kategori_id',
        'tahun_pembelian',
        'jumlah',
        'kondisi',
        'gambar',
        'deskripsi',
        'status'
    ];

    /** Ambil daftar alat dengan pagination dan filter */
    public function getAlatPaginated($page = 1, $limit = 10, $search = '', $kategoriId = '')
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE a.deleted_at IS NULL';
        $params = [];

        if (!empty($search)) {
            $whereClause .= ' AND (a.nama_alat LIKE :search OR a.kode_alat LIKE :search OR a.deskripsi LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($kategoriId)) {
            $whereClause .= ' AND a.kategori_id = :kategori_id';
            $params['kategori_id'] = $kategoriId;
        }

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} a $whereClause";
        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                $whereClause
                ORDER BY a.created_at DESC
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

    /** Ambil detail alat beserta jumlah peminjaman */
    public function getAlatDetails($id)
    {
        $sql = "SELECT a.*,
                       k.name as kategori_name,
                       COUNT(p.id) as total_peminjaman,
                       COUNT(CASE WHEN p.status = 'DIPINJAM' THEN 1 END) as active_peminjaman
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                LEFT JOIN peminjaman p ON p.item_type = 'alat' AND p.item_id = a.id AND p.deleted_at IS NULL
                WHERE a.id = :id AND a.deleted_at IS NULL
                GROUP BY a.id";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Ambil semua kategori yang tersedia */
    public function getAllKategori()
    {
        $sql = "SELECT id, name FROM kategori WHERE deleted_at IS NULL ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    /** Ambil daftar alat berdasarkan kategori */
    public function getAlatByKategori($kategoriId)
    {
        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE a.kategori_id = :kategori_id AND a.deleted_at IS NULL
                ORDER BY a.nama_alat";

        return $this->db->fetchAll($sql, ['kategori_id' => $kategoriId]);
    }

    /** Ambil alat yang tersedia untuk dipinjam (status TERSEDIA & kondisi BAIK) */
    public function getAvailableAlat()
    {
        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE a.status = 'TERSEDIA'
                  AND a.kondisi = 'BAIK'
                  AND a.deleted_at IS NULL
                ORDER BY a.nama_alat";

        return $this->db->fetchAll($sql);
    }

    /** Update status alat */
    public function updateStatus($alatId, $status)
    {
        $sql = "UPDATE {$this->table}
                SET status = :status, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";
        return $this->db->query($sql, ['status' => $status, 'id' => $alatId]);
    }

    /** Update kondisi alat */
    public function updateCondition($alatId, $kondisi)
    {
        $sql = "UPDATE {$this->table}
                SET kondisi = :kondisi, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";
        return $this->db->query($sql, ['kondisi' => $kondisi, 'id' => $alatId]);
    }

    /** Cari alat berdasarkan nama, kode, atau deskripsi */
    public function searchAlat($query, $limit = 20)
    {
        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE (a.nama_alat LIKE :query OR a.kode_alat LIKE :query OR a.deskripsi LIKE :query)
                  AND a.deleted_at IS NULL
                ORDER BY a.nama_alat
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ]);
    }

    /** Ambil daftar alat berdasarkan kondisi */
    public function getAlatByCondition($kondisi)
    {
        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE a.kondisi = :kondisi AND a.deleted_at IS NULL
                ORDER BY a.nama_alat";

        return $this->db->fetchAll($sql, ['kondisi' => $kondisi]);
    }

    /** Ambil daftar alat berdasarkan status */
    public function getAlatByStatus($status)
    {
        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE a.status = :status AND a.deleted_at IS NULL
                ORDER BY a.nama_alat";

        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    /** Hitung jumlah alat per kategori */
    public function getAlatCountByKategori()
    {
        $sql = "SELECT k.name, COUNT(a.id) as total
                FROM kategori k
                LEFT JOIN alat a ON k.id = a.kategori_id AND a.deleted_at IS NULL
                WHERE k.deleted_at IS NULL
                GROUP BY k.id, k.name
                ORDER BY total DESC";

        return $this->db->fetchAll($sql);
    }

    /** Ambil statistik alat (total, per status, per kondisi, dll) */
    public function getAlatStatistics()
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

        $sql = "SELECT kondisi, COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL GROUP BY kondisi";
        $conditionResults = $this->db->fetchAll($sql);
        foreach ($conditionResults as $row) {
            $stats['by_condition'][strtolower($row['kondisi'])] = $row['total'];
        }

        $stats['by_kategori'] = $this->getAlatCountByKategori();

        $sql = "SELECT SUM(jumlah) as total_quantity FROM {$this->table} WHERE deleted_at IS NULL";
        $result = $this->db->fetch($sql);
        $stats['total_quantity'] = $result['total_quantity'] ?? 0;

        return $stats;
    }

    /** Cek apakah kode alat sudah ada di database */
    public function kodeAlatExists($kodeAlat, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE kode_alat = :kode_alat AND deleted_at IS NULL";
        $params = ['kode_alat' => $kodeAlat];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /** Ambil alat yang baru ditambahkan */
    public function getRecentAlat($limit = 5)
    {
        $sql = "SELECT a.*, k.name as kategori_name
                FROM {$this->table} a
                LEFT JOIN kategori k ON a.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE a.deleted_at IS NULL
                ORDER BY a.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Hapus alat (soft delete, hanya tandai deleted_at) */
    public function softDelete($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}

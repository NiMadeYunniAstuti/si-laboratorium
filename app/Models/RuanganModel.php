<?php

/**
 * Model untuk mengelola data ruangan
 */
class RuanganModel extends BaseModel
{
    protected $table = 'ruangan';
    protected $fillable = [
        'kode_ruangan',
        'nama_ruangan',
        'kategori_id',
        'kapasitas',
        'lantai',
        'gedung',
        'gambar',
        'deskripsi',
        'status'
    ];

    /** Ambil daftar ruangan dengan pagination dan filter */
    public function getRuanganPaginated($page = 1, $limit = 10, $search = '', $status = '')
    {
        $page = ($page < 1) ? 1 : (int) $page;
        $limit = ($limit < 1) ? Config::ITEMS_PER_PAGE : (int) $limit;
        $offset = ($page - 1) * $limit;

        $whereClause = 'WHERE r.deleted_at IS NULL';
        $params = [];

        if (!empty($search)) {
            $whereClause .= ' AND (r.nama_ruangan LIKE :search OR r.kode_ruangan LIKE :search OR r.deskripsi LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }

        if (!empty($status)) {
            $whereClause .= ' AND LOWER(r.status) = :status';
            $params['status'] = strtolower($status);
        }

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} r $whereClause";
        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        $sql = "SELECT r.*, k.name as kategori_name
                FROM {$this->table} r
                LEFT JOIN kategori k ON r.kategori_id = k.id AND k.deleted_at IS NULL
                $whereClause
                ORDER BY r.created_at DESC
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

    /** Ambil detail ruangan berdasarkan ID */
    public function getRuanganById($id)
    {
        $sql = "SELECT r.*,
                       k.name as kategori_name
                FROM {$this->table} r
                LEFT JOIN kategori k ON r.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE r.id = :id AND r.deleted_at IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Ambil ruangan yang tersedia untuk dipinjam */
    public function getRuanganForBooking()
    {
        $sql = "SELECT r.*, k.name as kategori_name
                FROM {$this->table} r
                LEFT JOIN kategori k ON r.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE r.status = 'TERSEDIA'
                  AND r.deleted_at IS NULL
                ORDER BY r.nama_ruangan";

        return $this->db->fetchAll($sql);
    }

    /** Update status ruangan */
    public function updateStatus($id, $status)
    {
        $sql = "UPDATE {$this->table}
                SET status = :status, updated_at = NOW()
                WHERE id = :id AND deleted_at IS NULL";
        return $this->db->query($sql, ['status' => $status, 'id' => $id]);
    }

    /** Cari ruangan berdasarkan nama, kode, atau deskripsi */
    public function searchRuangan($query, $limit = 20)
    {
        $sql = "SELECT r.*, k.name as kategori_name
                FROM {$this->table} r
                LEFT JOIN kategori k ON r.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE (r.nama_ruangan LIKE :query OR r.kode_ruangan LIKE :query OR r.deskripsi LIKE :query)
                  AND r.deleted_at IS NULL
                ORDER BY r.nama_ruangan
                LIMIT :limit";

        return $this->db->fetchAll($sql, [
            'query' => '%' . $query . '%',
            'limit' => $limit
        ]);
    }

    /** Cek apakah kode ruangan sudah ada di database */
    public function kodeRuanganExists($kodeRuangan, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE kode_ruangan = :kode_ruangan AND deleted_at IS NULL";
        $params = ['kode_ruangan' => $kodeRuangan];

        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }

        $result = $this->db->fetch($sql, $params);
        return ($result['count'] ?? 0) > 0;
    }

    /** Ambil semua kategori yang tersedia */
    public function getAllKategori()
    {
        $sql = "SELECT id, name FROM kategori WHERE deleted_at IS NULL ORDER BY name ASC";
        return $this->db->fetchAll($sql);
    }

    /** Ambil daftar ruangan berdasarkan status */
    public function getRuanganByStatus($status)
    {
        $sql = "SELECT r.*, k.name as kategori_name
                FROM {$this->table} r
                LEFT JOIN kategori k ON r.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE r.status = :status AND r.deleted_at IS NULL
                ORDER BY r.nama_ruangan";

        return $this->db->fetchAll($sql, ['status' => $status]);
    }

    /** Ambil ruangan yang baru ditambahkan */
    public function getRecentRuangan($limit = 5)
    {
        $sql = "SELECT r.*, k.name as kategori_name
                FROM {$this->table} r
                LEFT JOIN kategori k ON r.kategori_id = k.id AND k.deleted_at IS NULL
                WHERE r.deleted_at IS NULL
                ORDER BY r.created_at DESC
                LIMIT :limit";

        $stmt = $this->db->query($sql, ['limit' => $limit]);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Hapus ruangan (soft delete, hanya tandai deleted_at) */
    public function softDelete($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /** Cek apakah ruangan tersedia untuk dipinjam */
    public function isRuanganAvailable($id)
    {
        $sql = "SELECT status FROM {$this->table} WHERE id = :id AND deleted_at IS NULL";
        $result = $this->db->fetch($sql, ['id' => $id]);

        if (!$result) {
            return false;
        }

        return $result['status'] === 'TERSEDIA';
    }
}

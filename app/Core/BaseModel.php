<?php

/**
 * Kelas dasar untuk semua Model
 * Menyediakan fungsi CRUD standar yang bisa dipakai di model turunannya
 */
class BaseModel
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    protected $hidden = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** Cari data berdasarkan ID */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Cari data berdasarkan kolom tertentu */
    public function findBy($column, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return $this->db->fetch($sql, ['value' => $value]);
    }

    /** Ambil semua data dari tabel */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    /** Ambil data dengan kondisi tertentu */
    public function where($column, $operator = '=', $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        return $this->db->fetchAll($sql, ['value' => $value]);
    }

    /** Tambah data baru ke tabel */
    public function create($data)
    {
        $data = $this->filterFillable($data);

        if (empty($data)) {
            return false;
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) .
               ") VALUES (" . implode(', ', $placeholders) . ")";

        $this->db->query($sql, $data);

        return $this->db->lastInsertId();
    }

    /** Update data berdasarkan ID */
    public function update($id, $data)
    {
        $data = $this->filterFillable($data);

        if (empty($data)) {
            return false;
        }

        $setClause = [];
        foreach ($data as $column => $value) {
            $setClause[] = "{$column} = :{$column}";
        }

        $data['id'] = $id;
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) .
               " WHERE {$this->primaryKey} = :id";

        $stmt = $this->db->query($sql, $data);
        return $stmt !== false;
    }

    /** Hapus data permanen berdasarkan ID */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt !== false;
    }

    /** Ambil data dengan pagination (per halaman) */
    public function paginate($page = 1, $limit = null, $conditions = [])
    {
        $limit = $limit ?? Config::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $whereClause = '';
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $whereClause = "WHERE " . implode(' AND ', $whereClauses);
        }

        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $countResult = $this->db->fetch($countSql, $params);
        $total = $countResult['total'] ?? 0;

        $sql = "SELECT * FROM {$this->table} {$whereClause}
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

    /** Jalankan query SQL mentah */
    public function raw($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }

    /** Hitung jumlah data dengan kondisi tertentu */
    public function count($conditions = [])
    {
        $whereClause = '';
        $params = [];

        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $whereClauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $whereClause = "WHERE " . implode(' AND ', $whereClauses);
        }

        $sql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $result = $this->db->fetch($sql, $params);
        return $result['total'] ?? 0;
    }

    /** Cek apakah data dengan ID tertentu ada */
    public function exists($id)
    {
        $record = $this->find($id);
        return !empty($record);
    }

    /** Ambil data paling terakhir */
    public function last()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT 1";
        return $this->db->fetch($sql);
    }

    /** Ambil data paling pertama */
    public function first()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} ASC LIMIT 1";
        return $this->db->fetch($sql);
    }

    /** Saring data, hanya ambil kolom yang boleh diisi (fillable) */
    private function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /** Sembunyikan kolom sensitif dari output (misal: password) */
    protected function hideFields($data)
    {
        if (empty($this->hidden)) {
            return $data;
        }

        if (is_array($data)) {
            foreach ($this->hidden as $field) {
                unset($data[$field]);
            }
        }

        return $data;
    }

    /** Mulai transaksi database */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /** Simpan transaksi database */
    public function commit()
    {
        return $this->db->commit();
    }

    /** Batalkan transaksi database */
    public function rollback()
    {
        return $this->db->rollback();
    }
}

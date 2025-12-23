<?php

/**
 * Base Model Class
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

    /**
     * Find record by ID
     */
    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Find record by column
     */
    public function findBy($column, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$column} = :value LIMIT 1";
        return $this->db->fetch($sql, ['value' => $value]);
    }

    /**
     * Get all records
     */
    public function all()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    /**
     * Get records with conditions
     */
    public function where($column, $operator = '=', $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $sql = "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value";
        return $this->db->fetchAll($sql, ['value' => $value]);
    }

    /**
     * Create new record
     */
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

    /**
     * Update record
     */
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

    /**
     * Delete record
     */
    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        return $stmt !== false;
    }

    /**
     * Get paginated results
     */
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

    /**
     * Execute raw query
     */
    public function raw($sql, $params = [])
    {
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Count records
     */
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

    /**
     * Check if record exists
     */
    public function exists($id)
    {
        $record = $this->find($id);
        return !empty($record);
    }

    /**
     * Get last record
     */
    public function last()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT 1";
        return $this->db->fetch($sql);
    }

    /**
     * Get first record
     */
    public function first()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} ASC LIMIT 1";
        return $this->db->fetch($sql);
    }

    /**
     * Filter only fillable fields from data
     */
    private function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Hide sensitive fields from output
     */
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

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->db->rollback();
    }
}
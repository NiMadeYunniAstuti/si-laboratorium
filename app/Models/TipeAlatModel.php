<?php

/**
 * Tipe Alat Model
 */
class TipeAlatModel extends BaseModel
{
    protected $table = 'tipe_alat';
    protected $fillable = [
        'name'
    ];

    /**
     * Get all tipe alat
     */
    public function getAllTipe()
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE deleted_at IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get tipe by ID
     */
    public function getTipeById($id)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE id = :id AND deleted_at IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Create new tipe
     */
    public function createTipe($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Update tipe
     */
    public function updateTipe($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Soft delete tipe
     */
    public function deleteTipe($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Get tipe with alat count
     */
    public function getTipeWithAlatCount()
    {
        $sql = "SELECT t.*, COUNT(a.id) as jumlah_alat
                FROM {$this->table} t
                LEFT JOIN alat a ON t.id = a.tipe_id AND a.deleted_at IS NULL
                WHERE t.deleted_at IS NULL
                GROUP BY t.id
                ORDER BY t.name ASC";

        return $this->db->fetchAll($sql);
    }
}
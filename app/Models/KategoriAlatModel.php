<?php

/**
 * Kategori Alat Model
 */
class KategoriAlatModel extends BaseModel
{
    protected $table = 'kategori_alat';
    protected $fillable = [
        'name'
    ];

    /**
     * Get all kategori alat
     */
    public function getAllKategori()
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE deletedAt IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql);
    }

    /**
     * Get kategori by ID
     */
    public function getKategoriById($id)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE id = :id AND deletedAt IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Create new kategori
     */
    public function createKategori($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Update kategori
     */
    public function updateKategori($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /**
     * Soft delete kategori
     */
    public function deleteKategori($id)
    {
        $sql = "UPDATE {$this->table} SET deletedAt = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /**
     * Get kategori with alat count
     */
    public function getKategoriWithAlatCount()
    {
        $sql = "SELECT k.*, COUNT(a.id) as jumlah_alat
                FROM {$this->table} k
                LEFT JOIN alat a ON k.id = a.kategori_id AND a.deletedAt IS NULL
                WHERE k.deletedAt IS NULL
                GROUP BY k.id
                ORDER BY k.name ASC";

        return $this->db->fetchAll($sql);
    }
}
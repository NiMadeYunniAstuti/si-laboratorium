<?php

/**
 * Model untuk mengelola data kategori alat dan ruangan
 */
class KategoriModel extends BaseModel
{
    protected $table = 'kategori';
    protected $fillable = [
        'name'
    ];

    /** Ambil semua kategori */
    public function getAllKategori()
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE deleted_at IS NULL
                ORDER BY name ASC";

        return $this->db->fetchAll($sql);
    }

    /** Ambil kategori berdasarkan ID */
    public function getKategoriById($id)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE id = :id AND deleted_at IS NULL";

        return $this->db->fetch($sql, ['id' => $id]);
    }

    /** Tambah kategori baru */
    public function createKategori($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /** Update kategori */
    public function updateKategori($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($id, $data);
    }

    /** Hapus kategori (soft delete) */
    public function deleteKategori($id)
    {
        $sql = "UPDATE {$this->table} SET deleted_at = NOW() WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }

    /** Ambil kategori beserta jumlah alat di masing-masing */
    public function getKategoriWithAlatCount()
    {
        $sql = "SELECT k.*, COUNT(a.id) as jumlah_alat
                FROM {$this->table} k
                LEFT JOIN alat a ON k.id = a.kategori_id AND a.deleted_at IS NULL
                WHERE k.deleted_at IS NULL
                GROUP BY k.id
                ORDER BY k.name ASC";

        return $this->db->fetchAll($sql);
    }
}

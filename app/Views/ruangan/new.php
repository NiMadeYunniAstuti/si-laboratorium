<?php
$title = 'Tambah Ruangan Baru - LBMS';
$current_route = '/ruangan';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="main-content" id="mainContent">
    <div class="d-flex align-items-center mb-4">
        <a href="/ruangan" class="btn btn-outline-secondary btn-sm rounded-pill me-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <h1 class="page-title h3 fw-bold mb-0">Tambah Ruangan Baru</h1>
            <p class="text-muted mb-0">Daftarkan fasilitas ruangan baru ke dalam sistem</p>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-white border-bottom p-4">
            <h5 class="fw-bold mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Formulir Penambahan Ruangan</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/ruangan/create" id="tambahRuanganForm" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="kode_ruangan" class="form-label small fw-semibold">Kode Ruangan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="kode_ruangan" name="kode_ruangan" 
                                           placeholder="Contoh: R001" value="<?= htmlspecialchars($oldInput['kode_ruangan'] ?? '') ?>" required>
                                </div>
                                <div class="form-text smaller text-warning"><i class="bi bi-exclamation-circle me-1"></i>Kode ruangan tidak dapat diubah setelah disimpan.</div>
                            </div>

                            <div class="col-md-6">
                                <label for="nama_ruangan" class="form-label small fw-semibold">Nama Ruangan</label>
                                <input type="text" class="form-control" id="nama_ruangan" name="nama_ruangan" 
                                       placeholder="Masukkan nama ruangan" value="<?= htmlspecialchars($oldInput['nama_ruangan'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label small fw-semibold">Jenis Ruangan</label>
                                <select class="form-select select2-enable" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Jenis Ruangan</option>
                                    <?php if (!empty($kategoriList)): ?>
                                        <?php foreach ($kategoriList as $kategori): ?>
                                            <option value="<?= $kategori['id'] ?>" <?= (isset($oldInput['kategori_id']) && $oldInput['kategori_id'] == $kategori['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($kategori['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="kapasitas" class="form-label small fw-semibold">Kapasitas (Orang)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="kapasitas" name="kapasitas" 
                                           min="1" value="<?= htmlspecialchars($oldInput['kapasitas'] ?? '1') ?>" required>
                                    <span class="input-group-text bg-light text-muted small">Pax</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="lantai" class="form-label small fw-semibold">Lantai</label>
                                <input type="number" class="form-control" id="lantai" name="lantai" 
                                       min="1" value="<?= htmlspecialchars($oldInput['lantai'] ?? '1') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="gedung" class="form-label small fw-semibold">Gedung</label>
                                <input type="text" class="form-control" id="gedung" name="gedung" 
                                       placeholder="Contoh: Gedung A" value="<?= htmlspecialchars($oldInput['gedung'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-12">
                                <label for="status" class="form-label small fw-semibold">Status Awal</label>
                                <select class="form-select select2-enable" id="status" name="status" required>
                                    <option value="tersedia" <?= (isset($oldInput['status']) && $oldInput['status'] === 'tersedia') ? 'selected' : '' ?> selected>Tersedia</option>
                                    <option value="dipinjam" <?= (isset($oldInput['status']) && $oldInput['status'] === 'dipinjam') ? 'selected' : '' ?>>Dipinjam</option>
                                    <option value="maintenance" <?= (isset($oldInput['status']) && $oldInput['status'] === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                                    <option value="rusak" <?= (isset($oldInput['status']) && $oldInput['status'] === 'rusak') ? 'selected' : '' ?>>Rusak</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label small fw-semibold">Deskripsi / Fasilitas</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                          placeholder="Tuliskan fasilitas yang tersedia di ruangan ini..."><?= htmlspecialchars($oldInput['deskripsi'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="bg-light rounded-4 p-4 h-100">
                            <h6 class="fw-bold mb-3">Foto Ruangan</h6>
                            <div class="mb-4 text-center">
                                <div class="mb-3">
                                    <div id="noImage" class="bg-white rounded-3 d-flex align-items-center justify-content-center border border-dashed" style="height: 200px;">
                                        <div class="text-muted text-center">
                                            <i class="bi bi-image fs-1 d-block mb-2"></i>
                                            <span class="small">Belum ada foto</span>
                                        </div>
                                    </div>
                                    <img src="" id="imgPreview" class="img-fluid rounded-3 shadow-sm border d-none" style="max-height: 250px; object-fit: cover;" alt="Preview">
                                </div>
                                <label for="gambar" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                    <i class="bi bi-camera me-2"></i>Unggah Foto
                                    <input type="file" class="d-none" id="gambar" name="gambar" accept="image/*">
                                </label>
                            </div>

                            <div class="alert alert-info border-0 shadow-sm smaller mb-0">
                                <i class="bi bi-lightbulb-fill me-2"></i>
                                Deskripsi ruangan yang lengkap akan memudahkan pengguna dalam mencari fasilitas yang sesuai dengan kebutuhan mereka.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                    <a href="/ruangan" class="btn btn-light rounded-pill px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">
                        <i class="bi bi-check-circle me-2"></i>Simpan Ruangan
                    </button>
                </div>
            </form>
        </div>
    </div>
</main>

<?php 
ob_start(); 
?>
<script>
    $(document).ready(function() {
        $('.select2-enable').select2({
            theme: 'bootstrap-5',
            width: '100%',
            dropdownParent: $('#mainContent')
        });

        $('#gambar').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imgPreview').attr('src', e.target.result).removeClass('d-none');
                    $('#noImage').addClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        $('#tambahRuanganForm').on('submit', function(e) {
            const kapasitas = parseInt($('#kapasitas').val());
            if (isNaN(kapasitas) || kapasitas < 1) {
                e.preventDefault();
                alert('Kapasitas minimal 1 orang.');
                return false;
            }
            return true;
        });
    });
</script>
<?php
$extra_js = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php';
echo $extra_js;
?>

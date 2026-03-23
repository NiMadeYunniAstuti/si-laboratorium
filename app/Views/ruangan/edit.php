<?php
$title = 'Edit Ruangan - LBMS';
$current_route = '/ruangan';
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/navbar.php';
?>

<main class="main-content" id="mainContent">
    <div class="d-flex align-items-center mb-4">
        <a href="/ruangan/<?= $ruanganDetail['id'] ?? '' ?>/detail" class="btn btn-outline-secondary btn-sm rounded-pill me-3">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <div>
            <h1 class="page-title h3 fw-bold mb-0">Edit Ruangan</h1>
            <p class="text-muted mb-0">Perbarui informasi fasilitas ruangan</p>
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
            <h5 class="fw-bold mb-0"><i class="bi bi-door-open me-2 text-primary"></i>Data Ruangan</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="/ruangan/<?= $ruanganDetail['id'] ?? '' ?>/update" id="editRuanganForm" enctype="multipart/form-data">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="kode_ruangan" class="form-label small fw-semibold">Kode Ruangan</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-hash"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 bg-light" id="kode_ruangan" 
                                           value="<?= htmlspecialchars($ruanganDetail['kode_ruangan'] ?? '') ?>" readonly style="cursor: not-allowed;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="nama_ruangan" class="form-label small fw-semibold">Nama Ruangan</label>
                                <input type="text" class="form-control" id="nama_ruangan" name="nama_ruangan" 
                                       value="<?= htmlspecialchars($ruanganDetail['nama_ruangan'] ?? '') ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="kategori_id" class="form-label small fw-semibold">Kategori</label>
                                <select class="form-select select2-enable" id="kategori_id" name="kategori_id" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php if (!empty($kategoriList)): ?>
                                        <?php foreach ($kategoriList as $kategori): ?>
                                            <option value="<?= $kategori['id'] ?>" <?= ($ruanganDetail['kategori_id'] ?? '') == $kategori['id'] ? 'selected' : '' ?>>
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
                                           min="1" value="<?= htmlspecialchars($ruanganDetail['kapasitas'] ?? '1') ?>" required>
                                    <span class="input-group-text bg-light text-muted small">Pax</span>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label small fw-semibold">Status Ruangan</label>
                                <select class="form-select select2-enable" id="status" name="status" required>
                                    <option value="TERSEDIA" <?= strtoupper($ruanganDetail['status'] ?? '') === 'TERSEDIA' ? 'selected' : '' ?>>Tersedia</option>
                                    <option value="DIPINJAM" <?= strtoupper($ruanganDetail['status'] ?? '') === 'DIPINJAM' ? 'selected' : '' ?>>Dipinjam</option>
                                    <option value="MAINTENANCE" <?= strtoupper($ruanganDetail['status'] ?? '') === 'MAINTENANCE' ? 'selected' : '' ?>>Maintenance</option>
                                    <option value="RUSAK" <?= strtoupper($ruanganDetail['status'] ?? '') === 'RUSAK' ? 'selected' : '' ?>>Rusak</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="lantai" class="form-label small fw-semibold">Lantai</label>
                                <input type="text" class="form-control" id="lantai" name="lantai" 
                                       placeholder="Contoh: 2" value="<?= htmlspecialchars($ruanganDetail['lantai'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label for="gedung" class="form-label small fw-semibold">Gedung</label>
                                <input type="text" class="form-control" id="gedung" name="gedung" 
                                       placeholder="Contoh: Gedung A" value="<?= htmlspecialchars($ruanganDetail['gedung'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label small fw-semibold">Deskripsi / Fasilitas</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" 
                                          placeholder="Tuliskan fasilitas yang tersedia di ruangan ini..."><?= htmlspecialchars($ruanganDetail['deskripsi'] ?? '') ?></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="bg-light rounded-4 p-4 h-100">
                            <h6 class="fw-bold mb-3">Foto Ruangan</h6>
                            <div class="mb-4 text-center">
                                <div class="mb-3">
                                    <?php if (!empty($ruanganDetail['gambar'])): ?>
                                        <img src="/<?= ltrim(htmlspecialchars($ruanganDetail['gambar']), '/') ?>" id="imgPreview" 
                                             class="img-fluid rounded-3 shadow-sm border" style="max-height: 250px; object-fit: cover;" alt="Current photo">
                                    <?php else: ?>
                                        <div id="noImage" class="bg-white rounded-3 d-flex align-items-center justify-content-center border border-dashed" style="height: 200px;">
                                            <div class="text-muted text-center">
                                                <i class="bi bi-image fs-1 d-block mb-2"></i>
                                                <span class="small">Belum ada foto</span>
                                            </div>
                                        </div>
                                        <img src="" id="imgPreview" class="img-fluid rounded-3 shadow-sm border d-none" style="max-height: 250px; object-fit: cover;" alt="Preview">
                                    <?php endif; ?>
                                </div>
                                <label for="gambar" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                                    <i class="bi bi-camera me-2"></i><?= (!empty($ruanganDetail['gambar'])) ? 'Ganti Foto' : 'Unggah Foto' ?>
                                    <input type="file" class="d-none" id="gambar" name="gambar" accept="image/*">
                                </label>
                            </div>

                            <div class="alert alert-warning border-0 shadow-sm smaller mb-0">
                                <i class="bi bi-lightbulb-fill me-2"></i>
                                Deskripsi ruangan yang jelas membantu peminjam memahami fasilitas yang tersedia.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5 pt-4 border-top d-flex justify-content-end">
                    <a href="/ruangan/<?= $ruanganDetail['id'] ?? '' ?>/detail" class="btn btn-light rounded-pill px-4 me-2">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-5">
                        <i class="bi bi-check-circle me-2"></i>Simpan Perubahan
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
    });
</script>
<?php 
$extra_js = ob_get_clean();
require_once __DIR__ . '/../layouts/footer.php';
echo $extra_js;
?>

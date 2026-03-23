-- Development Seed Data for LBMS
-- This file contains sample data for development purposes

-- Insert sample users with Indonesian names
INSERT INTO users (name, email, password_hash, role, status, foto, created_at, updated_at) VALUES
('Budi Santoso', 'budi.santoso@lbms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER', 'ACTIVE', 'uploads/foto_profil/user1-avatar.jpg', NOW(), NOW()),
('Dewi Kartika', 'dewi.kartika@lbms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER', 'ACTIVE', 'uploads/foto_profil/user2-avatar.jpg', NOW(), NOW()),
('Ahmad Hidayat', 'ahmad.hidayat@lbms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER', 'INACTIVE', 'uploads/foto_profil/user3-avatar.jpg', NOW(), NOW()),
('Siti Rahayu', 'siti.rahayu@lbms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'USER', 'BLACKLIST', 'uploads/foto_profil/user4-avatar.jpg', NOW(), NOW());

-- Insert sample alat with realistic data
INSERT INTO alat (kode_alat, nama_alat, kategori_id, tahun_pembelian, jumlah, kondisi, gambar, deskripsi, status, created_at, updated_at) VALUES
('ELE-001', 'Oscilloscope Tektronix TBS1102C', 1, 2022, 2, 'BAIK', 'uploads/alat/oscilloscope-001.jpg', 'Oscilloscope digital 2 channel dengan layar LCD 7 inci, bandwidth 100 MHz', 'TERSEDIA', NOW(), NOW()),
('ELE-002', 'Multimeter Digital Fluke 87V', 1, 2021, 5, 'BAIK', 'uploads/alat/multimeter-002.jpg', 'Multimeter digital True RMS dengan fungsi pengukuran tegangan, arus, dan resistansi', 'TERSEDIA', NOW(), NOW()),
('ELE-003', 'Power Supply Rigol DP832', 1, 2023, 3, 'BAIK', 'uploads/alat/powersupply-003.jpg', 'Power supply programmable 30V/3A dengan 3 output channel', 'TERSEDIA', NOW(), NOW()),
('ELE-004', 'Signal Generator Rigol DG4062', 1, 2022, 1, 'BAIK', 'uploads/alat/generator-004.jpg', 'Function generator dengan output dual-channel, 60 MHz bandwidth', 'DIPINJAM', NOW(), NOW()),
('SIP-001', 'Total Station Topcon ES-105', 2, 2021, 2, 'BAIK', 'uploads/alat/totalstation-001.jpg', 'Total station electronic dengan accuracy 5 detik untuk survey dan pemetaan', 'TERSEDIA', NOW(), NOW()),
('SIP-002', 'Theodolite Leica Builder 509', 2, 2020, 3, 'BAIK', 'uploads/alat/theodolite-002.jpg', 'Theodolite electronic untuk pengukuran sudut presisi', 'TERSEDIA', NOW(), NOW()),
('SIP-003', 'Laser Distance Meter Bosch GLM 50', 2, 2023, 8, 'BAIK', 'uploads/alat/laser-003.jpg', 'Laser distance meter dengan range 50m dan accuracy ±1.5mm', 'TERSEDIA', NOW(), NOW()),
('TI-001', 'Laptop Dell XPS 15', 3, 2023, 4, 'BAIK', 'uploads/alat/laptop-001.jpg', 'Laptop high performance dengan Intel Core i7, 16GB RAM, 512GB SSD untuk programming dan analisis data', 'TERSEDIA', NOW(), NOW()),
('TI-002', 'Switch Cisco Catalyst 2960-24TC', 3, 2022, 2, 'BAIK', 'uploads/alat/switch-002.jpg', 'Managed switch 24-port Gigabit Ethernet dengan 2 port SFP untuk jaringan', 'TERSEDIA', NOW(), NOW()),
('TI-003', 'Raspberry Pi 4 Model B', 3, 2022, 10, 'BAIK', 'uploads/alat/raspberry-003.jpg', 'Single board computer untuk IoT projects dan embedded programming', 'TERSEDIA', NOW(), NOW()),
('LNG-001', 'pH Meter Digital Hanna HI98103', 4, 2022, 6, 'BAIK', 'uploads/alat/phmeter-001.jpg', 'pH meter waterproof untuk pengukuran kadar asam basa dalam larutan', 'TERSEDIA', NOW(), NOW()),
('LNG-002', 'Water Quality Meter Aquaread AQ600', 4, 2023, 2, 'BAIK', 'uploads/alat/watermeter-002.jpg', 'Multi-parameter water quality meter untuk pengukuran pH, TDS, suhu, dan oksigen terlarut', 'DIPINJAM', NOW(), NOW());

-- Insert sample ruangan data
INSERT INTO ruangan (kode_ruangan, nama_ruangan, kategori_id, kapasitas, lantai, gedung, deskripsi, status, created_at, updated_at) VALUES
('RGN-001', 'Ruang Kelas A101', 5, 40, 1, 'Gedung A', 'Ruang kelas standar dengan AC dan proyektor', 'TERSEDIA', NOW(), NOW()),
('RGN-002', 'Ruang Laboratorium Komputer', 6, 30, 2, 'Gedung B', 'Lab komputer dengan 30 unit PC', 'TERSEDIA', NOW(), NOW()),
('RGN-003', 'Ruang Rapat Utama', 7, 20, 1, 'Gedung A', 'Ruang rapat dengan meja conference dan layar', 'TERSEDIA', NOW(), NOW()),
('RGN-004', 'Ruang Perpustakaan', 8, 50, 1, 'Gedung C', 'Ruang perpustakaan dengan koleksi buku teknik', 'TERSEDIA', NOW(), NOW());

-- Insert sample peminjaman data
-- Note: nama_peminjam must match user's actual name
INSERT INTO peminjaman (user_id, nama_peminjam, alat_id, tanggal_pinjam, tanggal_kembali, status, keterangan, created_at, updated_at) VALUES
(2, 'Budi Santoso', 1, '2024-01-15', '2024-01-20', 'SELESAI', 'Peminjaman untuk praktikum elektronika dasar', NOW(), NOW()),
(3, 'Dewi Kartika', 2, '2024-01-16', '2024-01-23', 'SELESAI', 'Pengukuran resistansi komponen elektronik', NOW(), NOW()),
(2, 'Budi Santoso', 5, '2024-01-17', '2024-01-24', 'DIPINJAM', 'Survey area kampus untuk tugas akhir', NOW(), NOW()),
(3, 'Dewi Kartika', 8, '2024-01-18', '2024-01-25', 'SELESAI', 'Pengembangan aplikasi monitoring sensor IoT', NOW(), NOW()),
(2, 'Budi Santoso', 10, '2024-01-19', '2024-01-26', 'PENDING', 'Pengukuran pH air sungai untuk penelitian lingkungan', NOW(), NOW()),
(3, 'Dewi Kartika', 9, '2024-01-20', '2024-01-27', 'DITOLAK', 'Setup jaringan untuk event hacking competition', NOW(), NOW()),
(2, 'Budi Santoso', 3, '2024-01-21', '2024-01-28', 'DIPINJAM', 'Testing power supply untuk project robotika', NOW(), NOW());

-- Insert sample notifications
INSERT INTO notifikasi (title, description, is_read, created_at, updated_at) VALUES
('Welcome to LBMS', 'Selamat datang di Sistem Manajemen Peminjaman Laboratorium', TRUE, NOW(), NOW()),
('Peminjaman Disetujui', 'Peminjaman Oscilloscope Tektronix TBS1102C telah disetujui', FALSE, NOW(), NOW()),
('Peminjaman Akan Kadaluarsa', 'Peminjaman anda akan kadaluarsa dalam 2 hari', FALSE, NOW(), NOW()),
('Alat Baru Ditambahkan', 'Signal Generator Rigol DG4062 telah ditambahkan ke inventaris', FALSE, NOW(), NOW()),
('Maintenance Reminder', 'Total Station Topcon ES-105 perlu maintenance rutin', TRUE, NOW(), NOW());

-- Assign notifications to users
INSERT INTO notifikasi_users (notifikasi_id, user_id, is_read, created_at) VALUES
(1, 2, TRUE, NOW()),
(1, 3, TRUE, NOW()),
(2, 2, FALSE, NOW()),
(3, 2, FALSE, NOW()),
(4, 3, FALSE, NOW()),
(5, 3, TRUE, NOW());

-- Update some alat status based on peminjaman
UPDATE alat SET status = 'TERSEDIA' WHERE id IN (1, 2, 8, 10, 11);
UPDATE alat SET status = 'DIPINJAM' WHERE id IN (5, 3, 12);

-- Inisialisasi Database LBMS
-- Struktur database untuk Sistem Manajemen Peminjaman Laboratorium

-- Tabel Users (autentikasi dan autorisasi)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('ADMIN', 'USER') DEFAULT 'USER',
    status ENUM('ACTIVE', 'INACTIVE', 'BLACKLIST') DEFAULT 'ACTIVE',
    foto VARCHAR(255),
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletedAt TIMESTAMP NULL
);

-- Tabel Kategori Alat
CREATE TABLE IF NOT EXISTS kategori_alat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletedAt TIMESTAMP NULL
);

-- Tabel Tipe Alat
CREATE TABLE IF NOT EXISTS tipe_alat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletedAt TIMESTAMP NULL
);

-- Tabel Alat (inventaris utama)
CREATE TABLE IF NOT EXISTS alat (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_alat VARCHAR(50) NOT NULL UNIQUE,
    nama_alat VARCHAR(255) NOT NULL,
    kategori_id INT,
    tipe_id INT,
    tahun_pembelian INT,
    jumlah INT DEFAULT 1,
    kondisi ENUM('BAIK', 'RUSAK', 'HILANG') DEFAULT 'BAIK',
    gambar VARCHAR(255),
    deskripsi TEXT,
    status ENUM('TERSEDIA', 'DIPINJAM', 'MAINTENANCE', 'RUSAK') DEFAULT 'TERSEDIA',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletedAt TIMESTAMP NULL,
    FOREIGN KEY (kategori_id) REFERENCES kategori_alat(id) ON DELETE SET NULL,
    FOREIGN KEY (tipe_id) REFERENCES tipe_alat(id) ON DELETE SET NULL
);

-- Tabel Peminjaman (manajemen peminjaman)
CREATE TABLE IF NOT EXISTS peminjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    alat_id INT NOT NULL,
    tanggal_pinjam DATE NOT NULL,
    tanggal_kembali DATE NOT NULL,
    tanggal_pengembalian DATE NULL,
    status ENUM('DIPINJAM', 'PENDING', 'DITOLAK', 'SELESAI') DEFAULT 'PENDING',
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletedAt TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (alat_id) REFERENCES alat(id) ON DELETE RESTRICT
);

-- Tabel Notifikasi
CREATE TABLE IF NOT EXISTS notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    peminjaman_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deletedAt TIMESTAMP NULL,
    FOREIGN KEY (peminjaman_id) REFERENCES peminjaman(id) ON DELETE SET NULL
);

-- Tabel Notifikasi Users (many-to-many)
CREATE TABLE IF NOT EXISTS notifikasi_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notifikasi_id INT NOT NULL,
    user_id INT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (notifikasi_id) REFERENCES notifikasi(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_notif_user (notifikasi_id, user_id)
);

-- Data default
INSERT IGNORE INTO users (name, email, password_hash, role, status) VALUES
('Administrator', 'admin@lbms.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'ADMIN', 'ACTIVE');

-- Kategori alat default
INSERT IGNORE INTO kategori_alat (name) VALUES
('ALAT_TEKNIK_ELEKTRO'),
('ALAT_TEKNIK_SIPIL'),
('ALAT_TEKNOLOGI_INFORMASI'),
('ALAT_TEKNIK_LINGKUNGAN');

-- Tipe alat default
INSERT IGNORE INTO tipe_alat (name) VALUES
('ALAT'),
('RUANGAN');

-- Index untuk performa
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);

CREATE INDEX IF NOT EXISTS idx_alat_kode ON alat(kode_alat);
CREATE INDEX IF NOT EXISTS idx_alat_kategori ON alat(kategori_id);
CREATE INDEX IF NOT EXISTS idx_alat_tipe ON alat(tipe_id);
CREATE INDEX IF NOT EXISTS idx_alat_status ON alat(status);

CREATE INDEX IF NOT EXISTS idx_peminjaman_user ON peminjaman(user_id);
CREATE INDEX IF NOT EXISTS idx_peminjaman_alat ON peminjaman(alat_id);
CREATE INDEX IF NOT EXISTS idx_peminjaman_status ON peminjaman(status);
CREATE INDEX IF NOT EXISTS idx_peminjaman_tanggal ON peminjaman(tanggal_pinjam);

CREATE INDEX IF NOT EXISTS idx_notifikasi_read ON notifikasi(is_read);
CREATE INDEX IF NOT EXISTS idx_notif_users_notif ON notifikasi_users(notifikasi_id);
CREATE INDEX IF NOT EXISTS idx_notif_users_user ON notifikasi_users(user_id);
CREATE INDEX IF NOT EXISTS idx_notif_users_read ON notifikasi_users(is_read);
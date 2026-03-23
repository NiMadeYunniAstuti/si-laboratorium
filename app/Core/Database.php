<?php

require_once __DIR__ . '/../Config/Config.php';

/**
 * Kelas untuk mengelola koneksi ke database
 * Menggunakan pola Singleton supaya koneksi cuma dibuat sekali
 */
class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . Config::DB_HOST . ";dbname=" . Config::DB_NAME . ";charset=" . Config::DB_CHARSET;

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => true
            ];

            $this->connection = new PDO($dsn, Config::DB_USER, Config::DB_PASS, $options);

        } catch (PDOException $e) {
            if (Config::APP_DEBUG) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }

    /** Ambil instance database (Singleton) */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /** Ambil objek koneksi PDO */
    public function getConnection()
    {
        return $this->connection;
    }

    /** Jalankan query SQL dengan parameter */
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            if (Config::APP_DEBUG) {
                die("Query failed: " . $e->getMessage());
            } else {
                error_log("Database error: " . $e->getMessage());
                return false;
            }
        }
    }

    /** Ambil satu baris data */
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : null;
    }

    /** Ambil semua baris data */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : [];
    }

    /** Ambil ID terakhir yang baru saja di-insert */
    public function lastInsertId()
    {
        return $this->connection->lastInsertId();
    }

    /** Mulai transaksi database */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /** Simpan semua perubahan dalam transaksi */
    public function commit()
    {
        return $this->connection->commit();
    }

    /** Batalkan semua perubahan dalam transaksi */
    public function rollback()
    {
        return $this->connection->rollback();
    }
}

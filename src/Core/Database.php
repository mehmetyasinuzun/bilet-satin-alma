<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Singleton Class
 * 
 * OOP Prensipleri:
 * - Singleton Pattern: Tek bir veritabanı bağlantısı garantisi
 * - Encapsulation: PDO instance'ı private olarak korunur
 * - Static method kullanımı: getInstance() ile global erişim
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $connection;
    private string $dbPath;

    /**
     * Private constructor - Singleton pattern için dışarıdan erişim engellenir
     */
    private function __construct()
    {
        $dataDir = dirname(__DIR__, 2) . '/data';
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }
        $this->dbPath = $dataDir . '/database.sqlite';
        $this->connect();
    }

    /**
     * Clone'lamayı engelle - Singleton pattern koruması
     */
    private function __clone() {}

    /**
     * Unserialize'ı engelle - Singleton pattern koruması
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize singleton");
    }

    /**
     * Singleton instance'ı döndürür
     * 
     * @return Database
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Veritabanı bağlantısını oluşturur
     */
    private function connect(): void
    {
        try {
            $this->connection = new PDO('sqlite:' . $this->dbPath);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \RuntimeException("Veritabanı bağlantı hatası: " . $e->getMessage());
        }
    }

    /**
     * PDO bağlantısını döndürür
     * 
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Transaction başlatır
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Transaction'ı onaylar
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }

    /**
     * Transaction'ı geri alır
     */
    public function rollBack(): bool
    {
        return $this->connection->rollBack();
    }

    /**
     * Prepared statement oluşturur
     */
    public function prepare(string $sql): \PDOStatement
    {
        return $this->connection->prepare($sql);
    }

    /**
     * Direkt sorgu çalıştırır
     */
    public function query(string $sql): \PDOStatement
    {
        return $this->connection->query($sql);
    }

    /**
     * Son eklenen ID'yi döndürür
     */
    public function lastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }
}

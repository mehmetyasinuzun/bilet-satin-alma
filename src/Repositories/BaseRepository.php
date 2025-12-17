<?php
namespace App\Repositories;

use App\Core\Database;
use App\Core\Helpers;
use App\Interfaces\RepositoryInterface;

/**
 * Abstract Base Repository Class
 * 
 * OOP Prensipleri:
 * - Abstract Class: Tüm repository'ler için ortak davranışları tanımlar
 * - Template Method Pattern: findById, findAll gibi metodlar override edilebilir
 * - Inheritance: Child repository'ler bu sınıftan türer
 * - Composition: Database sınıfını içerir (Dependency Injection)
 * - Polymorphism: Interface implementasyonu sayesinde farklı repository'ler aynı şekilde kullanılabilir
 */
abstract class BaseRepository implements RepositoryInterface
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';

    /**
     * Constructor - Dependency Injection ile Database alır
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * ID ile kayıt bulur
     */
    public function findById(string $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Tüm kayıtları döndürür
     */
    public function findAll(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Koşula göre tek kayıt bulur
     */
    public function findOneBy(array $criteria): ?array
    {
        $where = $this->buildWhereClause($criteria);
        $sql = "SELECT * FROM {$this->table} WHERE {$where['clause']} LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($where['params']);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Koşula göre çoklu kayıt bulur
     */
    public function findBy(array $criteria, ?string $orderBy = null, ?int $limit = null): array
    {
        $where = $this->buildWhereClause($criteria);
        $sql = "SELECT * FROM {$this->table} WHERE {$where['clause']}";
        
        if ($orderBy) {
            $sql .= " ORDER BY {$orderBy}";
        }
        
        if ($limit) {
            $sql .= " LIMIT {$limit}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($where['params']);
        return $stmt->fetchAll();
    }

    /**
     * Yeni kayıt oluşturur
     */
    public function create(array $data): string
    {
        if (!isset($data['id'])) {
            $data['id'] = Helpers::generateId();
        }
        
        if (!isset($data['created_at'])) {
            $data['created_at'] = Helpers::getCurrentDateTime();
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $data['id'];
    }

    /**
     * Timestamp olmadan kayıt oluşturur (Trips gibi farklı timestamp sütunu olan tablolar için)
     */
    public function createWithoutTimestamp(array $data): string
    {
        if (!isset($data['id'])) {
            $data['id'] = Helpers::generateId();
        }
        
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        return $data['id'];
    }

    /**
     * Kayıt günceller
     */
    public function update(string $id, array $data): bool
    {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = "{$column} = ?";
        }
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . 
               " WHERE {$this->primaryKey} = ?";
        
        $params = array_values($data);
        $params[] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Kayıt siler
     */
    public function delete(string $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Kayıt sayısını döndürür
     */
    public function count(array $criteria = []): int
    {
        if (empty($criteria)) {
            $sql = "SELECT COUNT(*) FROM {$this->table}";
            return (int) $this->db->query($sql)->fetchColumn();
        }
        
        $where = $this->buildWhereClause($criteria);
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$where['clause']}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($where['params']);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Kayıt var mı kontrol eder
     */
    public function exists(string $id): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return (bool) $stmt->fetch();
    }

    /**
     * WHERE clause oluşturur
     */
    protected function buildWhereClause(array $criteria): array
    {
        $clauses = [];
        $params = [];
        
        foreach ($criteria as $column => $value) {
            if ($value === null) {
                $clauses[] = "{$column} IS NULL";
            } else {
                $clauses[] = "{$column} = ?";
                $params[] = $value;
            }
        }
        
        return [
            'clause' => implode(' AND ', $clauses),
            'params' => $params
        ];
    }

    /**
     * Transaction başlatır
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Transaction onaylar
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Transaction geri alır
     */
    public function rollBack(): bool
    {
        return $this->db->rollBack();
    }
}

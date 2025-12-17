<?php
namespace App\Entities;

use App\Interfaces\ValidatableInterface;

/**
 * Abstract Base Entity Class
 * 
 * OOP Prensipleri:
 * - Abstract Class: Ortak entity davranışlarını tanımlar
 * - Inheritance: Tüm entity'ler bu sınıftan türer
 * - Encapsulation: Protected ve private property'ler
 * - Template Method Pattern: validate() metodu child class'larda implement edilir
 */
abstract class BaseEntity implements ValidatableInterface
{
    protected ?string $id = null;
    protected ?string $createdAt = null;
    protected array $errors = [];

    /**
     * Constructor - Diziden entity oluşturur
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            $this->fill($data);
        }
    }

    /**
     * Diziden property'leri doldurur
     */
    public function fill(array $data): void
    {
        foreach ($data as $key => $value) {
            $property = $this->snakeToCamel($key);
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     * Entity'yi diziye çevirir
     */
    public function toArray(): array
    {
        $properties = get_object_vars($this);
        $result = [];
        
        foreach ($properties as $key => $value) {
            if ($key !== 'errors') {
                $result[$this->camelToSnake($key)] = $value;
            }
        }
        
        return $result;
    }

    /**
     * snake_case'i camelCase'e çevirir
     */
    protected function snakeToCamel(string $str): string
    {
        return lcfirst(str_replace('_', '', ucwords($str, '_')));
    }

    /**
     * camelCase'i snake_case'e çevirir
     */
    protected function camelToSnake(string $str): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    // Getter ve Setter metodları (Encapsulation)
    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function setCreatedAt(string $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Validasyon hatalarını döndürür
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Hata ekler
     */
    protected function addError(string $field, string $message): void
    {
        $this->errors[$field] = $message;
    }

    /**
     * Entity geçerli mi kontrol eder
     */
    public function isValid(): bool
    {
        $this->errors = [];
        $this->validate();
        return empty($this->errors);
    }

    /**
     * Abstract validate metodu - her entity implement etmeli
     */
    abstract public function validate(): array;
}

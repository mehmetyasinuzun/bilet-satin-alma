<?php
namespace App\Entities;

/**
 * BusCompany Entity Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseEntity'den türer
 * - Encapsulation: Property'ler protected, getter/setter ile erişim
 */
class BusCompany extends BaseEntity
{
    protected ?string $name = null;
    protected ?string $logoPath = null;

    /**
     * Validasyon kurallarını uygular
     */
    public function validate(): array
    {
        $this->errors = [];

        if (empty($this->name)) {
            $this->addError('name', 'Firma adı gereklidir.');
        } elseif (strlen($this->name) < 2) {
            $this->addError('name', 'Firma adı en az 2 karakter olmalıdır.');
        }

        return $this->errors;
    }

    // Getter metodları
    public function getName(): ?string
    {
        return $this->name;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    // Setter metodları
    public function setName(string $name): void
    {
        $this->name = trim($name);
    }

    public function setLogoPath(?string $logoPath): void
    {
        $this->logoPath = $logoPath;
    }
}

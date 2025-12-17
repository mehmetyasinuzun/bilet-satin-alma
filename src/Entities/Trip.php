<?php
namespace App\Entities;

/**
 * Trip Entity Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseEntity'den türer
 * - Encapsulation: Property'ler protected, getter/setter ile erişim
 * - Composition: BusCompany entity'si ile ilişkili (company_id)
 */
class Trip extends BaseEntity
{
    protected ?string $companyId = null;
    protected ?string $departureCity = null;
    protected ?string $destinationCity = null;
    protected ?string $departureTime = null;
    protected ?string $arrivalTime = null;
    protected float $price = 0.0;
    protected int $capacity = 0;
    protected ?string $createdDate = null;

    // İlişkili veriler (Composition)
    protected ?string $companyName = null;
    protected int $bookedSeats = 0;

    /**
     * Validasyon kurallarını uygular
     */
    public function validate(): array
    {
        $this->errors = [];

        if (empty($this->companyId)) {
            $this->addError('company_id', 'Firma seçimi gereklidir.');
        }

        if (empty($this->departureCity)) {
            $this->addError('departure_city', 'Kalkış şehri gereklidir.');
        }

        if (empty($this->destinationCity)) {
            $this->addError('destination_city', 'Varış şehri gereklidir.');
        }

        if (empty($this->departureTime)) {
            $this->addError('departure_time', 'Kalkış zamanı gereklidir.');
        }

        if (empty($this->arrivalTime)) {
            $this->addError('arrival_time', 'Varış zamanı gereklidir.');
        }

        if (!empty($this->departureTime) && !empty($this->arrivalTime)) {
            if (strtotime($this->departureTime) >= strtotime($this->arrivalTime)) {
                $this->addError('arrival_time', 'Varış zamanı kalkış zamanından sonra olmalıdır.');
            }
        }

        if ($this->price <= 0) {
            $this->addError('price', 'Fiyat pozitif olmalıdır.');
        }

        if ($this->capacity <= 0) {
            $this->addError('capacity', 'Kapasite pozitif olmalıdır.');
        }

        return $this->errors;
    }

    /**
     * Müsait koltuk sayısını hesaplar
     */
    public function getAvailableSeats(): int
    {
        return max(0, $this->capacity - $this->bookedSeats);
    }

    /**
     * Sefer dolu mu kontrol eder
     */
    public function isFull(): bool
    {
        return $this->getAvailableSeats() <= 0;
    }

    /**
     * Koltuk müsait mi kontrol eder
     */
    public function hasAvailableSeats(int $count = 1): bool
    {
        return $this->getAvailableSeats() >= $count;
    }

    /**
     * Yolculuk süresi (dakika)
     */
    public function getDurationInMinutes(): int
    {
        if (empty($this->departureTime) || empty($this->arrivalTime)) {
            return 0;
        }
        return (strtotime($this->arrivalTime) - strtotime($this->departureTime)) / 60;
    }

    /**
     * Yolculuk süresi formatı
     */
    public function getFormattedDuration(): string
    {
        $minutes = $this->getDurationInMinutes();
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%d saat %d dakika', $hours, $mins);
    }

    // Getter metodları
    public function getCompanyId(): ?string
    {
        return $this->companyId;
    }

    public function getDepartureCity(): ?string
    {
        return $this->departureCity;
    }

    public function getDestinationCity(): ?string
    {
        return $this->destinationCity;
    }

    public function getDepartureTime(): ?string
    {
        return $this->departureTime;
    }

    public function getArrivalTime(): ?string
    {
        return $this->arrivalTime;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getBookedSeats(): int
    {
        return $this->bookedSeats;
    }

    // Setter metodları
    public function setCompanyId(string $companyId): void
    {
        $this->companyId = $companyId;
    }

    public function setDepartureCity(string $city): void
    {
        $this->departureCity = trim($city);
    }

    public function setDestinationCity(string $city): void
    {
        $this->destinationCity = trim($city);
    }

    public function setDepartureTime(string $time): void
    {
        $this->departureTime = $time;
    }

    public function setArrivalTime(string $time): void
    {
        $this->arrivalTime = $time;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function setCapacity(int $capacity): void
    {
        $this->capacity = $capacity;
    }

    public function setCompanyName(string $name): void
    {
        $this->companyName = $name;
    }

    public function setBookedSeats(int $count): void
    {
        $this->bookedSeats = $count;
    }
}

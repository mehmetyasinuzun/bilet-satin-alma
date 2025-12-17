<?php
namespace App\Entities;

/**
 * Ticket Entity Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseEntity'den türer
 * - Encapsulation: Property'ler protected, getter/setter ile erişim
 * - Composition: Trip ve User entity'leri ile ilişkili
 */
class Ticket extends BaseEntity
{
    protected ?string $tripId = null;
    protected ?string $userId = null;
    protected int $seatNumber = 0;
    protected string $status = 'active';
    protected float $totalPrice = 0.0;

    // Composition - İlişkili entity bilgileri
    protected ?string $departureCity = null;
    protected ?string $destinationCity = null;
    protected ?string $departureTime = null;
    protected ?string $arrivalTime = null;
    protected ?string $companyName = null;
    protected ?string $fullName = null;
    protected ?string $email = null;

    // Durum sabitleri
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_USED = 'used';

    /**
     * Validasyon kurallarını uygular
     */
    public function validate(): array
    {
        $this->errors = [];

        if (empty($this->tripId)) {
            $this->addError('trip_id', 'Sefer seçimi gereklidir.');
        }

        if (empty($this->userId)) {
            $this->addError('user_id', 'Kullanıcı bilgisi gereklidir.');
        }

        if ($this->seatNumber <= 0) {
            $this->addError('seat_number', 'Geçerli bir koltuk numarası seçin.');
        }

        if ($this->totalPrice < 0) {
            $this->addError('total_price', 'Fiyat negatif olamaz.');
        }

        if (!in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_CANCELLED, self::STATUS_USED])) {
            $this->addError('status', 'Geçersiz bilet durumu.');
        }

        return $this->errors;
    }

    /**
     * Bilet aktif mi kontrol eder
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Bilet iptal edilmiş mi kontrol eder
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Bilet iptal edilebilir mi kontrol eder (kalkışa 1 saatten fazla varsa)
     */
    public function canBeCancelled(): bool
    {
        if (!$this->isActive() || empty($this->departureTime)) {
            return false;
        }
        
        $departureTime = strtotime($this->departureTime);
        $hoursDiff = ($departureTime - time()) / 3600;
        
        return $hoursDiff > 1;
    }

    /**
     * Bileti iptal eder
     */
    public function cancel(): void
    {
        $this->status = self::STATUS_CANCELLED;
    }

    /**
     * Kalkışa kalan saat
     */
    public function getHoursUntilDeparture(): float
    {
        if (empty($this->departureTime)) {
            return 0;
        }
        return (strtotime($this->departureTime) - time()) / 3600;
    }

    // Getter metodları
    public function getTripId(): ?string
    {
        return $this->tripId;
    }

    public function getUserId(): ?string
    {
        return $this->userId;
    }

    public function getSeatNumber(): int
    {
        return $this->seatNumber;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTotalPrice(): float
    {
        return $this->totalPrice;
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

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    // Setter metodları
    public function setTripId(string $tripId): void
    {
        $this->tripId = $tripId;
    }

    public function setUserId(string $userId): void
    {
        $this->userId = $userId;
    }

    public function setSeatNumber(int $seatNumber): void
    {
        $this->seatNumber = $seatNumber;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setTotalPrice(float $price): void
    {
        $this->totalPrice = $price;
    }
}

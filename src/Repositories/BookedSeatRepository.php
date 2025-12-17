<?php
namespace App\Repositories;

use App\Core\Helpers;

/**
 * BookedSeat Repository Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseRepository'den türer
 */
class BookedSeatRepository extends BaseRepository
{
    protected string $table = 'Booked_Seats';

    /**
     * Sefer için dolu koltukları getirir
     */
    public function findBookedSeatsByTrip(string $tripId): array
    {
        $sql = "SELECT seat_number FROM {$this->table} WHERE trip_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tripId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Koltuk rezervasyonu oluşturur
     */
    public function bookSeat(string $tripId, string $ticketId, int $seatNumber): string
    {
        $data = [
            'id' => Helpers::generateId('bs'),
            'trip_id' => $tripId,
            'ticket_id' => $ticketId,
            'seat_number' => $seatNumber
        ];
        
        return $this->create($data);
    }

    /**
     * Koltuk rezervasyonunu iptal eder
     */
    public function cancelByTicket(string $ticketId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE ticket_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$ticketId]);
    }

    /**
     * Koltuk dolu mu kontrol eder
     */
    public function isSeatBooked(string $tripId, int $seatNumber): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE trip_id = ? AND seat_number = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tripId, $seatNumber]);
        return (bool) $stmt->fetch();
    }
}

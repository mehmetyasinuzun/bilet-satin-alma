<?php
namespace App\Repositories;

use App\Entities\Ticket;
use App\Core\Helpers;

/**
 * Ticket Repository Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseRepository'den türer
 * - Polymorphism: Ticket özelinde sorgular
 */
class TicketRepository extends BaseRepository
{
    protected string $table = 'Tickets';

    /**
     * Kullanıcının biletlerini getirir
     */
    public function findByUser(string $userId): array
    {
        $sql = "SELECT t.*, 
                tr.departure_city, tr.destination_city, tr.departure_time, tr.arrival_time,
                bc.name as company_name
                FROM {$this->table} t
                JOIN Trips tr ON t.trip_id = tr.id
                JOIN Bus_Company bc ON tr.company_id = bc.id
                WHERE t.user_id = ?
                ORDER BY t.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Bilet detaylarını getirir
     */
    public function findTicketWithDetails(string $ticketId, string $userId): ?Ticket
    {
        $sql = "SELECT t.*, tr.departure_time, tr.price, tr.departure_city, tr.destination_city, tr.arrival_time,
                bc.name as company_name, u.full_name, u.email
                FROM {$this->table} t
                JOIN Trips tr ON t.trip_id = tr.id
                JOIN Bus_Company bc ON tr.company_id = bc.id
                JOIN User u ON t.user_id = u.id
                WHERE t.id = ? AND t.user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId, $userId]);
        $data = $stmt->fetch();
        
        return $data ? new Ticket($data) : null;
    }

    /**
     * Ticket entity'den kayıt oluşturur
     */
    public function createFromEntity(Ticket $ticket): string
    {
        $data = [
            'id' => $ticket->getId() ?? Helpers::generateId('ticket'),
            'trip_id' => $ticket->getTripId(),
            'user_id' => $ticket->getUserId(),
            'seat_number' => $ticket->getSeatNumber(),
            'total_price' => $ticket->getTotalPrice(),
            'status' => $ticket->getStatus()
        ];
        
        return $this->create($data);
    }

    /**
     * Bileti iptal eder
     */
    public function cancelTicket(string $ticketId): bool
    {
        return $this->update($ticketId, ['status' => 'cancelled']);
    }

    /**
     * Aktif bilet sayısı
     */
    public function countActiveByUser(string $userId): int
    {
        return $this->count(['user_id' => $userId, 'status' => 'active']);
    }

    /**
     * İptal edilen bilet sayısı
     */
    public function countCancelledByUser(string $userId): int
    {
        return $this->count(['user_id' => $userId, 'status' => 'cancelled']);
    }

    /**
     * Toplam aktif bilet sayısı
     */
    public function countActiveTickets(): int
    {
        return $this->count(['status' => 'active']);
    }

    /**
     * Aktif bilet mi ve kullanıcıya ait mi kontrol eder
     */
    public function findActiveTicketByUser(string $ticketId, string $userId): ?array
    {
        $sql = "SELECT t.*, tr.departure_time, tr.price
                FROM {$this->table} t
                JOIN Trips tr ON t.trip_id = tr.id
                WHERE t.id = ? AND t.user_id = ? AND t.status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$ticketId, $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}

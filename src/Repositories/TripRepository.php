<?php
namespace App\Repositories;

use App\Entities\Trip;
use App\Core\Helpers;

/**
 * Trip Repository Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseRepository'den türer
 * - Polymorphism: Özelleştirilmiş sorgular ile base metodları genişletir
 */
class TripRepository extends BaseRepository
{
    protected string $table = 'Trips';

    /**
     * Seferler ile birlikte firma bilgisi ve dolu koltuk sayısını getirir
     */
    public function findAllWithDetails(): array
    {
        $sql = "SELECT t.*, bc.name as company_name,
                (SELECT COUNT(*) FROM Booked_Seats bs WHERE bs.trip_id = t.id) as booked_seats
                FROM {$this->table} t
                JOIN Bus_Company bc ON t.company_id = bc.id
                ORDER BY t.departure_time ASC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    /**
     * Arama kriterleriyle sefer bulur
     */
    public function search(?string $departureCity, ?string $destinationCity, ?string $departureDate): array
    {
        $sql = "SELECT t.*, bc.name as company_name,
                (SELECT COUNT(*) FROM Booked_Seats bs WHERE bs.trip_id = t.id) as booked_seats
                FROM {$this->table} t
                JOIN Bus_Company bc ON t.company_id = bc.id
                WHERE 1=1";
        $params = [];

        if (!empty($departureCity)) {
            $sql .= " AND t.departure_city LIKE ?";
            $params[] = "%{$departureCity}%";
        }

        if (!empty($destinationCity)) {
            $sql .= " AND t.destination_city LIKE ?";
            $params[] = "%{$destinationCity}%";
        }

        if (!empty($departureDate)) {
            $sql .= " AND DATE(t.departure_time) = ?";
            $params[] = $departureDate;
        }

        $sql .= " ORDER BY t.departure_time ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Sefer detaylarını getirir
     */
    public function findTripWithDetails(string $tripId): ?Trip
    {
        $sql = "SELECT t.*, bc.name as company_name,
                (SELECT COUNT(*) FROM Booked_Seats bs WHERE bs.trip_id = t.id) as booked_seats
                FROM {$this->table} t
                JOIN Bus_Company bc ON t.company_id = bc.id
                WHERE t.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$tripId]);
        $data = $stmt->fetch();
        
        return $data ? new Trip($data) : null;
    }

    /**
     * Firma bazlı seferleri getirir
     */
    public function findByCompany(string $companyId): array
    {
        $sql = "SELECT t.*, 
                (SELECT COUNT(*) FROM Booked_Seats bs WHERE bs.trip_id = t.id) as booked_seats,
                (SELECT COUNT(*) FROM Tickets tk WHERE tk.trip_id = t.id AND tk.status = 'active') as active_tickets
                FROM {$this->table} t
                WHERE t.company_id = ?
                ORDER BY t.departure_time DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$companyId]);
        return $stmt->fetchAll();
    }

    /**
     * Trip entity'den kayıt oluşturur
     */
    public function createFromEntity(Trip $trip): string
    {
        $data = [
            'id' => $trip->getId() ?? Helpers::generateId('trip'),
            'company_id' => $trip->getCompanyId(),
            'departure_city' => $trip->getDepartureCity(),
            'destination_city' => $trip->getDestinationCity(),
            'departure_time' => $trip->getDepartureTime(),
            'arrival_time' => $trip->getArrivalTime(),
            'price' => $trip->getPrice(),
            'capacity' => $trip->getCapacity(),
            'created_date' => Helpers::getCurrentDateTime()
        ];
        
        return $this->createWithoutTimestamp($data);
    }

    /**
     * Benzersiz şehirleri getirir (otomatik tamamlama için)
     */
    public function getUniqueCities(): array
    {
        $sql = "SELECT DISTINCT departure_city FROM {$this->table} 
                UNION 
                SELECT DISTINCT destination_city FROM {$this->table} 
                ORDER BY departure_city";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Toplam sefer sayısı
     */
    public function countTrips(): int
    {
        return $this->count();
    }

    /**
     * Firma seferini siler (sadece o firmaya ait ise)
     */
    public function deleteCompanyTrip(string $tripId, string $companyId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ? AND company_id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$tripId, $companyId]);
    }
}

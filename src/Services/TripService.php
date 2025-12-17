<?php
namespace App\Services;

use App\Entities\Trip;
use App\Repositories\TripRepository;
use App\Core\Helpers;

/**
 * Trip Service Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseService'den türer
 * - Composition: TripRepository'yi içerir
 * - Single Responsibility: Sadece sefer işlemleri
 */
class TripService extends BaseService
{
    private TripRepository $tripRepository;

    public function __construct()
    {
        $this->tripRepository = new TripRepository();
    }

    /**
     * Tüm seferleri getirir
     */
    public function getAllTrips(): array
    {
        return $this->tripRepository->findAllWithDetails();
    }

    /**
     * Sefer arar
     */
    public function searchTrips(?string $departureCity, ?string $destinationCity, ?string $departureDate): array
    {
        return $this->tripRepository->search(
            $departureCity ? Helpers::sanitize($departureCity) : null,
            $destinationCity ? Helpers::sanitize($destinationCity) : null,
            $departureDate
        );
    }

    /**
     * Sefer detaylarını getirir
     */
    public function getTripDetails(string $tripId): ?Trip
    {
        return $this->tripRepository->findTripWithDetails($tripId);
    }

    /**
     * Firma seferlerini getirir
     */
    public function getCompanyTrips(string $companyId): array
    {
        return $this->tripRepository->findByCompany($companyId);
    }

    /**
     * Yeni sefer oluşturur
     */
    public function createTrip(array $data): ?Trip
    {
        $this->clearErrors();

        $trip = new Trip();
        $trip->setCompanyId($data['company_id'] ?? '');
        $trip->setDepartureCity($data['departure_city'] ?? '');
        $trip->setDestinationCity($data['destination_city'] ?? '');
        $trip->setDepartureTime($data['departure_time'] ?? '');
        $trip->setArrivalTime($data['arrival_time'] ?? '');
        $trip->setPrice((float)($data['price'] ?? 0));
        $trip->setCapacity((int)($data['capacity'] ?? 0));

        if (!$trip->isValid()) {
            $this->errors = array_values($trip->getErrors());
            return null;
        }

        $tripId = $this->tripRepository->createFromEntity($trip);
        $trip->setId($tripId);

        return $trip;
    }

    /**
     * Sefer siler
     */
    public function deleteTrip(string $tripId, string $companyId): bool
    {
        $this->clearErrors();

        if (!$this->tripRepository->deleteCompanyTrip($tripId, $companyId)) {
            $this->addError('Sefer silinemedi.');
            return false;
        }

        return true;
    }

    /**
     * Benzersiz şehirleri getirir
     */
    public function getUniqueCities(): array
    {
        return $this->tripRepository->getUniqueCities();
    }

    /**
     * Toplam sefer sayısı
     */
    public function getTotalTripsCount(): int
    {
        return $this->tripRepository->countTrips();
    }

    /**
     * Firma istatistiklerini hesaplar
     */
    public function calculateCompanyStats(array $trips): array
    {
        $totalTrips = count($trips);
        $totalTickets = array_sum(array_column($trips, 'active_tickets'));
        $totalRevenue = array_sum(array_map(function($t) {
            return $t['price'] * $t['active_tickets'];
        }, $trips));

        return [
            'total_trips' => $totalTrips,
            'total_tickets' => $totalTickets,
            'total_revenue' => $totalRevenue
        ];
    }
}

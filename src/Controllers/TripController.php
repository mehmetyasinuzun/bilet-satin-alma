<?php
namespace App\Controllers;

use App\Services\TripService;

/**
 * Trip Controller Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseController'dan türer
 * - Composition: TripService'i içerir
 */
class TripController extends BaseController
{
    private TripService $tripService;

    public function __construct()
    {
        parent::__construct();
        $this->tripService = new TripService();
    }

    /**
     * Ana sayfa - Sefer listesi
     */
    public function index(): array
    {
        $departureCity = trim($this->getQuery('departure_city', ''));
        $destinationCity = trim($this->getQuery('destination_city', ''));
        $departureDate = trim($this->getQuery('departure_date', ''));

        // Sefer arama veya tümünü getir
        if (!empty($departureCity) || !empty($destinationCity) || !empty($departureDate)) {
            $trips = $this->tripService->searchTrips($departureCity, $destinationCity, $departureDate);
        } else {
            $trips = $this->tripService->getAllTrips();
        }

        $cities = $this->tripService->getUniqueCities();

        return [
            'trips' => $trips,
            'cities' => $cities,
            'departureCity' => $departureCity,
            'destinationCity' => $destinationCity,
            'departureDate' => $departureDate
        ];
    }

    /**
     * Sefer detayı
     */
    public function show(string $tripId): ?array
    {
        $trip = $this->tripService->getTripDetails($tripId);

        if (!$trip) {
            $this->redirect('index.php');
            return null;
        }

        return ['trip' => $trip];
    }
}

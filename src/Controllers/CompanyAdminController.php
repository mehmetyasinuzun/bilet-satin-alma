<?php
namespace App\Controllers;

use App\Services\TripService;
use App\Services\BusCompanyService;
use App\Entities\User;

/**
 * CompanyAdmin Controller Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseController'dan türer
 * - Composition: TripService ve BusCompanyService'i içerir
 */
class CompanyAdminController extends BaseController
{
    private TripService $tripService;
    private BusCompanyService $companyService;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole(User::ROLE_COMPANY_ADMIN);
        
        $this->tripService = new TripService();
        $this->companyService = new BusCompanyService();
    }

    /**
     * Firma admin dashboard
     */
    public function dashboard(): array
    {
        $error = '';
        $success = '';
        $companyId = $this->session->get('company_id');

        // Firma bilgileri
        $company = $this->companyService->getCompanyById($companyId);

        // POST işlemleri
        if ($this->isPost()) {
            $action = $this->getPost('action');

            if ($action === 'add') {
                $trip = $this->tripService->createTrip([
                    'company_id' => $companyId,
                    'departure_city' => $this->getPost('departure_city'),
                    'destination_city' => $this->getPost('destination_city'),
                    'departure_time' => $this->getPost('departure_time'),
                    'arrival_time' => $this->getPost('arrival_time'),
                    'price' => $this->getPost('price'),
                    'capacity' => $this->getPost('capacity')
                ]);

                if ($trip) {
                    $success = 'Sefer başarıyla eklendi.';
                } else {
                    $error = $this->tripService->getFirstError();
                }
            } elseif ($action === 'delete') {
                if ($this->tripService->deleteTrip($this->getPost('trip_id'), $companyId)) {
                    $success = 'Sefer başarıyla silindi.';
                } else {
                    $error = $this->tripService->getFirstError();
                }
            }
        }

        // Firma seferleri
        $trips = $this->tripService->getCompanyTrips($companyId);
        $stats = $this->tripService->calculateCompanyStats($trips);

        return [
            'error' => $error,
            'success' => $success,
            'company' => $company,
            'trips' => $trips,
            'stats' => $stats
        ];
    }
}

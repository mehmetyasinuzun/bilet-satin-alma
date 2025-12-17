<?php
namespace App\Controllers;

use App\Services\AuthService;
use App\Services\BusCompanyService;
use App\Services\TripService;
use App\Services\TicketService;
use App\Services\CouponService;
use App\Repositories\UserRepository;
use App\Entities\User;

/**
 * Admin Controller Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseController'dan türer
 * - Composition: Birden fazla Service'i içerir
 * - Facade Pattern: Admin işlemlerini tek bir controller'da toplar
 */
class AdminController extends BaseController
{
    private BusCompanyService $companyService;
    private TripService $tripService;
    private TicketService $ticketService;
    private CouponService $couponService;
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole(User::ROLE_ADMIN);
        
        $this->companyService = new BusCompanyService();
        $this->tripService = new TripService();
        $this->ticketService = new TicketService();
        $this->couponService = new CouponService();
        $this->userRepository = new UserRepository();
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): array
    {
        $error = '';
        $success = '';

        // POST işlemleri
        if ($this->isPost()) {
            $action = $this->getPost('action');
            
            switch ($action) {
                case 'add_company':
                    $company = $this->companyService->createCompany([
                        'name' => $this->getPost('company_name')
                    ]);
                    if ($company) {
                        $this->session->flash('success', 'Firma başarıyla eklendi.');
                        $this->redirect('admin_dashboard.php');
                    } else {
                        $error = $this->companyService->getFirstError();
                    }
                    break;

                case 'delete_company':
                    if ($this->companyService->deleteCompany($this->getPost('company_id'))) {
                        $this->session->flash('success', 'Firma silindi.');
                        $this->redirect('admin_dashboard.php');
                    } else {
                        $error = $this->companyService->getFirstError();
                    }
                    break;

                case 'add_company_admin':
                    $user = $this->auth->createCompanyAdmin([
                        'full_name' => $this->getPost('full_name'),
                        'email' => $this->getPost('email'),
                        'password' => $this->getPost('password'),
                        'company_id' => $this->getPost('company_id')
                    ]);
                    if ($user) {
                        $this->session->flash('success', 'Firma Admin oluşturuldu.');
                        $this->redirect('admin_dashboard.php');
                    } else {
                        $error = $this->auth->getFirstError();
                    }
                    break;

                case 'add_coupon':
                    $coupon = $this->couponService->createCoupon([
                        'code' => $this->getPost('coupon_code'),
                        'discount' => $this->getPost('discount'),
                        'company_id' => $this->getPost('company_id'),
                        'usage_limit' => $this->getPost('usage_limit'),
                        'expire_date' => $this->getPost('expire_date')
                    ]);
                    if ($coupon) {
                        $this->session->flash('success', 'Kupon oluşturuldu.');
                        $this->redirect('admin_dashboard.php');
                    } else {
                        $error = $this->couponService->getFirstError();
                    }
                    break;

                case 'delete_coupon':
                    if ($this->couponService->deleteCoupon($this->getPost('coupon_id'))) {
                        $this->session->flash('success', 'Kupon silindi.');
                        $this->redirect('admin_dashboard.php');
                    } else {
                        $error = $this->couponService->getFirstError();
                    }
                    break;
            }
        }

        // Flash mesajları al
        $success = $this->session->getFlash('success') ?? '';

        // Verileri topla
        $companies = $this->companyService->getAllCompanies();
        $companyAdmins = $this->userRepository->findCompanyAdmins();
        $coupons = $this->couponService->getAllCoupons();

        $stats = [
            'total_users' => $this->userRepository->countUsers(),
            'total_companies' => count($companies),
            'total_trips' => $this->tripService->getTotalTripsCount(),
            'total_tickets' => $this->ticketService->getTotalActiveTickets(),
        ];

        return [
            'error' => $error,
            'success' => $success,
            'companies' => $companies,
            'companyAdmins' => $companyAdmins,
            'coupons' => $coupons,
            'stats' => $stats
        ];
    }
}

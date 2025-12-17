<?php
namespace App\Controllers;

use App\Services\TicketService;
use App\Repositories\UserRepository;
use App\Entities\User;

/**
 * User Controller Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseController'dan türer
 * - Composition: TicketService ve UserRepository'yi içerir
 */
class UserController extends BaseController
{
    private TicketService $ticketService;
    private UserRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole(User::ROLE_USER);
        
        $this->ticketService = new TicketService();
        $this->userRepository = new UserRepository();
    }

    /**
     * Kullanıcı dashboard
     */
    public function dashboard(): array
    {
        $userId = $this->session->getUserId();

        // Bakiye güncelle
        $balance = $this->userRepository->getBalance($userId);
        $this->auth->updateSessionBalance($balance);

        // İstatistikler
        $activeTickets = $this->ticketService->getActiveTicketCount($userId);
        $cancelledTickets = $this->ticketService->getCancelledTicketCount($userId);

        return [
            'balance' => $balance,
            'activeTickets' => $activeTickets,
            'cancelledTickets' => $cancelledTickets
        ];
    }
}

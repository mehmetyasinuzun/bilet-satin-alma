<?php
namespace App\Controllers;

use App\Services\TicketService;
use App\Services\TripService;
use App\Entities\User;

/**
 * Ticket Controller Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseController'dan türer
 * - Composition: TicketService ve TripService'i içerir
 */
class TicketController extends BaseController
{
    private TicketService $ticketService;
    private TripService $tripService;

    public function __construct()
    {
        parent::__construct();
        $this->requireRole(User::ROLE_USER);
        $this->ticketService = new TicketService();
        $this->tripService = new TripService();
    }

    /**
     * Bilet satın alma sayfası
     */
    public function purchase(): array
    {
        $tripId = $this->getQuery('trip_id');
        $error = '';

        if (!$tripId) {
            $this->redirect('index.php');
        }

        $trip = $this->tripService->getTripDetails($tripId);
        if (!$trip) {
            $this->redirect('index.php');
        }

        $bookedSeats = $this->ticketService->getBookedSeats($tripId);

        if ($this->isPost()) {
            $seatNumber = (int) $this->getPost('seat_number', 0);
            $couponCode = trim($this->getPost('coupon_code', ''));

            $ticket = $this->ticketService->purchaseTicket(
                $this->session->getUserId(),
                $tripId,
                $seatNumber,
                $couponCode ?: null
            );

            if ($ticket) {
                // Bakiye güncelle
                $this->auth->updateSessionBalance(
                    $this->session->get('balance') - $ticket->getTotalPrice()
                );
                $this->redirect('my_tickets.php?success=1');
            } else {
                $error = $this->ticketService->getFirstError();
            }
        }

        return [
            'trip' => $trip,
            'bookedSeats' => $bookedSeats,
            'error' => $error
        ];
    }

    /**
     * Biletlerim sayfası
     */
    public function myTickets(): array
    {
        $tickets = $this->ticketService->getUserTickets($this->session->getUserId());
        $success = $this->getQuery('success');

        return [
            'tickets' => $tickets,
            'success' => $success
        ];
    }

    /**
     * Bilet iptali
     */
    public function cancel(): ?array
    {
        $ticketId = $this->getQuery('ticket_id');
        $error = '';

        if (!$ticketId) {
            $this->redirect('my_tickets.php');
        }

        $result = $this->ticketService->cancelTicket($ticketId, $this->session->getUserId());

        if ($result) {
            // Bakiyeyi güncelle
            $ticket = $this->ticketService->getTicketDetails($ticketId, $this->session->getUserId());
            if ($ticket) {
                $newBalance = $this->session->get('balance') + $ticket->getTotalPrice();
                $this->auth->updateSessionBalance($newBalance);
            }
            $this->redirect('my_tickets.php?cancelled=1');
        } else {
            $error = $this->ticketService->getFirstError();
        }

        return ['error' => $error];
    }
}

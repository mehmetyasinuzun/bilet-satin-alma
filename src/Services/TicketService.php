<?php
namespace App\Services;

use App\Entities\Ticket;
use App\Repositories\TicketRepository;
use App\Repositories\BookedSeatRepository;
use App\Repositories\UserRepository;
use App\Repositories\TripRepository;

/**
 * Ticket Service Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseService'den türer
 * - Composition: Birden fazla Repository'yi içerir
 * - Facade Pattern: Karmaşık bilet işlemlerini basit API'ye dönüştürür
 * - Transaction Management: Atomik işlemler için transaction kullanır
 */
class TicketService extends BaseService
{
    private TicketRepository $ticketRepository;
    private BookedSeatRepository $bookedSeatRepository;
    private UserRepository $userRepository;
    private TripRepository $tripRepository;
    private CouponService $couponService;

    public function __construct()
    {
        $this->ticketRepository = new TicketRepository();
        $this->bookedSeatRepository = new BookedSeatRepository();
        $this->userRepository = new UserRepository();
        $this->tripRepository = new TripRepository();
        $this->couponService = new CouponService();
    }

    /**
     * Bilet satın alır
     * 
     * Bu metod birden fazla repository ve servisi koordine eder (Facade Pattern)
     */
    public function purchaseTicket(string $userId, string $tripId, int $seatNumber, ?string $couponCode = null): ?Ticket
    {
        $this->clearErrors();

        // Sefer kontrolü
        $trip = $this->tripRepository->findTripWithDetails($tripId);
        if (!$trip) {
            $this->addError('Sefer bulunamadı.');
            return null;
        }

        // Koltuk kontrolü
        if ($seatNumber < 1 || $seatNumber > $trip->getCapacity()) {
            $this->addError('Geçersiz koltuk numarası.');
            return null;
        }

        // Koltuk dolu mu kontrolü
        if ($this->bookedSeatRepository->isSeatBooked($tripId, $seatNumber)) {
            $this->addError('Bu koltuk zaten dolu.');
            return null;
        }

        // Fiyat hesaplama
        $totalPrice = $trip->getPrice();
        $couponId = null;

        // Kupon kontrolü
        if (!empty($couponCode)) {
            $couponResult = $this->couponService->validateAndApplyCoupon(
                $couponCode, 
                $userId, 
                $trip->getCompanyId(), 
                $totalPrice
            );

            if ($this->couponService->hasErrors()) {
                $this->errors = $this->couponService->getErrors();
                return null;
            }

            $totalPrice = $couponResult['discounted_price'];
            $couponId = $couponResult['coupon_id'];
        }

        // Bakiye kontrolü
        $currentBalance = $this->userRepository->getBalance($userId);
        if ($currentBalance < $totalPrice) {
            $this->addError('Yetersiz bakiye. Bakiyeniz: ' . number_format($currentBalance, 2, ',', '.') . ' ₺');
            return null;
        }

        // Transaction başlat
        $this->ticketRepository->beginTransaction();

        try {
            // Bilet oluştur
            $ticket = new Ticket();
            $ticket->setTripId($tripId);
            $ticket->setUserId($userId);
            $ticket->setSeatNumber($seatNumber);
            $ticket->setTotalPrice($totalPrice);
            $ticket->setStatus(Ticket::STATUS_ACTIVE);

            $ticketId = $this->ticketRepository->createFromEntity($ticket);
            $ticket->setId($ticketId);

            // Koltuk rezervasyonu
            $this->bookedSeatRepository->bookSeat($tripId, $ticketId, $seatNumber);

            // Bakiye düşür
            $this->userRepository->deductBalance($userId, $totalPrice);

            // Kupon kullanımını kaydet
            if ($couponId) {
                $this->couponService->recordUsage($userId, $couponId);
            }

            $this->ticketRepository->commit();

            return $ticket;

        } catch (\Exception $e) {
            $this->ticketRepository->rollBack();
            $this->addError('Bilet alımı sırasında hata oluştu: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Bilet iptal eder
     */
    public function cancelTicket(string $ticketId, string $userId): bool
    {
        $this->clearErrors();

        // Bilet kontrolü
        $ticketData = $this->ticketRepository->findActiveTicketByUser($ticketId, $userId);
        if (!$ticketData) {
            $this->addError('Bilet bulunamadı veya zaten iptal edilmiş.');
            return false;
        }

        // İptal süresi kontrolü (kalkışa 1 saatten az kaldıysa iptal edilemez)
        $departureTime = strtotime($ticketData['departure_time']);
        $hoursDiff = ($departureTime - time()) / 3600;

        if ($hoursDiff <= 1) {
            $this->addError('Kalkışa 1 saatten az kaldığı için bilet iptal edilemez.');
            return false;
        }

        // Transaction başlat
        $this->ticketRepository->beginTransaction();

        try {
            // Bileti iptal et
            $this->ticketRepository->cancelTicket($ticketId);

            // Koltuk rezervasyonunu kaldır
            $this->bookedSeatRepository->cancelByTicket($ticketId);

            // İade yap
            $this->userRepository->addBalance($userId, $ticketData['total_price']);

            $this->ticketRepository->commit();

            return true;

        } catch (\Exception $e) {
            $this->ticketRepository->rollBack();
            $this->addError('Bilet iptali sırasında hata oluştu: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Kullanıcının biletlerini getirir
     */
    public function getUserTickets(string $userId): array
    {
        return $this->ticketRepository->findByUser($userId);
    }

    /**
     * Bilet detaylarını getirir
     */
    public function getTicketDetails(string $ticketId, string $userId): ?Ticket
    {
        return $this->ticketRepository->findTicketWithDetails($ticketId, $userId);
    }

    /**
     * Sefer için dolu koltukları getirir
     */
    public function getBookedSeats(string $tripId): array
    {
        return $this->bookedSeatRepository->findBookedSeatsByTrip($tripId);
    }

    /**
     * Kullanıcı aktif bilet sayısı
     */
    public function getActiveTicketCount(string $userId): int
    {
        return $this->ticketRepository->countActiveByUser($userId);
    }

    /**
     * Kullanıcı iptal edilen bilet sayısı
     */
    public function getCancelledTicketCount(string $userId): int
    {
        return $this->ticketRepository->countCancelledByUser($userId);
    }

    /**
     * Toplam aktif bilet sayısı
     */
    public function getTotalActiveTickets(): int
    {
        return $this->ticketRepository->countActiveTickets();
    }
}

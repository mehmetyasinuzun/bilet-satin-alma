<?php
namespace App\Services;

use App\Entities\User;
use App\Repositories\UserRepository;
use App\Core\Session;
use App\Core\Helpers;
use App\Interfaces\AuthServiceInterface;

/**
 * Authentication Service Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseService'den türer
 * - Composition: UserRepository ve Session sınıflarını içerir
 * - Interface Implementation: AuthServiceInterface'i implement eder
 * - Single Responsibility: Sadece kimlik doğrulama işlemleri
 * - Dependency Injection: Repository constructor'da alınır
 */
class AuthService extends BaseService implements AuthServiceInterface
{
    private UserRepository $userRepository;
    private Session $session;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->session = Session::getInstance();
    }

    /**
     * Kullanıcı girişi yapar
     */
    public function login(string $email, string $password): ?User
    {
        $this->clearErrors();

        if (empty($email) || empty($password)) {
            $this->addError('E-posta ve şifre gereklidir.');
            return null;
        }

        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            $this->addError('E-posta veya şifre hatalı.');
            return null;
        }

        // Session'a kullanıcı bilgilerini yaz
        $this->createSession($user);

        return $user;
    }

    /**
     * Session oluşturur
     */
    private function createSession(User $user): void
    {
        $this->session->set('user_id', $user->getId());
        $this->session->set('full_name', $user->getFullName());
        $this->session->set('email', $user->getEmail());
        $this->session->set('role', $user->getRole());
        $this->session->set('company_id', $user->getCompanyId());
        $this->session->set('balance', $user->getBalance());
    }

    /**
     * Çıkış yapar
     */
    public function logout(): void
    {
        $this->session->destroy();
    }

    /**
     * Yeni kullanıcı kaydı yapar
     */
    public function register(array $data): User
    {
        $this->clearErrors();

        $user = new User();
        $user->setFullName(Helpers::sanitize($data['full_name'] ?? ''));
        $user->setEmail($data['email'] ?? '');
        $user->setPassword($data['password'] ?? '');
        $user->setRole(User::ROLE_USER);
        $user->setBalance(1500); // Başlangıç bakiyesi

        // Validasyon
        if (!$user->isValid()) {
            $this->errors = array_values($user->getErrors());
            return $user;
        }

        // Şifre onayı kontrolü
        if (($data['password'] ?? '') !== ($data['confirm_password'] ?? '')) {
            $this->addError('Şifreler eşleşmiyor.');
            return $user;
        }

        // Email benzersizlik kontrolü
        if ($this->userRepository->emailExists($user->getEmail())) {
            $this->addError('Bu e-posta adresi zaten kayıtlı.');
            return $user;
        }

        // Kullanıcıyı kaydet
        $userId = $this->userRepository->createFromEntity($user);
        $user->setId($userId);

        return $user;
    }

    /**
     * Firma admin oluşturur
     */
    public function createCompanyAdmin(array $data): ?User
    {
        $this->clearErrors();

        $user = new User();
        $user->setFullName(Helpers::sanitize($data['full_name'] ?? ''));
        $user->setEmail($data['email'] ?? '');
        $user->setPassword($data['password'] ?? '');
        $user->setRole(User::ROLE_COMPANY_ADMIN);
        $user->setCompanyId($data['company_id'] ?? null);
        $user->setBalance(0);

        // Validasyon
        if (!$user->isValid()) {
            $this->errors = array_values($user->getErrors());
            return null;
        }

        if (empty($data['company_id'])) {
            $this->addError('Firma seçimi gereklidir.');
            return null;
        }

        // Email benzersizlik kontrolü
        if ($this->userRepository->emailExists($user->getEmail())) {
            $this->addError('Bu e-posta adresi zaten kayıtlı.');
            return null;
        }

        // Kullanıcıyı kaydet
        $userId = $this->userRepository->createFromEntity($user);
        $user->setId($userId);

        return $user;
    }

    /**
     * Giriş yapmış kullanıcıyı döndürür
     */
    public function getCurrentUser(): ?User
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return $this->userRepository->findUserById($this->session->getUserId());
    }

    /**
     * Kullanıcı giriş yapmış mı
     */
    public function isLoggedIn(): bool
    {
        return $this->session->isLoggedIn();
    }

    /**
     * Kullanıcı admin mi
     */
    public function isAdmin(): bool
    {
        return $this->session->hasRole(User::ROLE_ADMIN);
    }

    /**
     * Kullanıcı firma admin mi
     */
    public function isCompanyAdmin(): bool
    {
        return $this->session->hasRole(User::ROLE_COMPANY_ADMIN);
    }

    /**
     * Kullanıcı normal user mı
     */
    public function isUser(): bool
    {
        return $this->session->hasRole(User::ROLE_USER);
    }

    /**
     * Giriş gerektirir (değilse yönlendirir)
     */
    public function requireLogin(): void
    {
        if (!$this->isLoggedIn()) {
            header('Location: login.php');
            exit;
        }
    }

    /**
     * Belirli rol gerektirir
     */
    public function requireRole(string $role): void
    {
        $this->requireLogin();
        if (!$this->session->hasRole($role)) {
            header('Location: index.php');
            exit;
        }
    }

    /**
     * Session bakiyesini günceller
     */
    public function updateSessionBalance(float $balance): void
    {
        $this->session->set('balance', $balance);
    }

    /**
     * Session'dan company_id'yi döndürür
     */
    public function getCompanyId(): ?string
    {
        return $this->session->get('company_id');
    }
}

<?php
namespace App\Core;

/**
 * Session Manager Class
 * 
 * OOP Prensipleri:
 * - Singleton Pattern: Tek session yönetimi
 * - Encapsulation: Session verilerine kontrollü erişim
 * - Facade Pattern: Session işlemlerini basitleştirir
 */
class Session
{
    private static ?Session $instance = null;
    private bool $started = false;

    private function __construct()
    {
        $this->configure();
        $this->start();
    }

    private function __clone() {}

    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Session güvenlik ayarlarını yapılandırır
     */
    private function configure(): void
    {
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_secure', '0');
    }

    /**
     * Session başlatır
     */
    public function start(): void
    {
        if (!$this->started && session_status() === PHP_SESSION_NONE) {
            session_start();
            $this->started = true;
        }
    }

    /**
     * Session değeri atar
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Session değeri okur
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Session değeri var mı kontrol eder
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Session değeri siler
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Tüm session'ı temizler
     */
    public function destroy(): void
    {
        session_destroy();
        $this->started = false;
        $_SESSION = [];
    }

    /**
     * Flash mesaj kaydeder (bir kez gösterilir)
     */
    public function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    /**
     * Flash mesaj okur ve siler
     */
    public function getFlash(string $key): ?string
    {
        $message = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $message;
    }

    /**
     * Kullanıcı giriş yapmış mı kontrol eder
     */
    public function isLoggedIn(): bool
    {
        return $this->has('user_id');
    }

    /**
     * Kullanıcı ID'sini döndürür
     */
    public function getUserId(): ?string
    {
        return $this->get('user_id');
    }

    /**
     * Kullanıcı rolünü döndürür
     */
    public function getRole(): ?string
    {
        return $this->get('role');
    }

    /**
     * Belirli bir role sahip mi kontrol eder
     */
    public function hasRole(string $role): bool
    {
        return $this->isLoggedIn() && $this->getRole() === $role;
    }
}

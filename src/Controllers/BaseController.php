<?php
namespace App\Controllers;

use App\Core\Session;
use App\Core\Helpers;
use App\Services\AuthService;

/**
 * Abstract Base Controller Class
 * 
 * OOP Prensipleri:
 * - Abstract Class: Tüm controller'lar için ortak davranışları tanımlar
 * - Template Method Pattern: render(), redirect() gibi ortak metodlar
 * - Inheritance: Child controller'lar bu sınıftan türer
 * - Composition: Session ve AuthService sınıflarını içerir
 */
abstract class BaseController
{
    protected Session $session;
    protected AuthService $auth;
    protected array $data = [];

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->auth = new AuthService();
    }

    /**
     * View render eder
     */
    protected function render(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        $viewPath = dirname(__DIR__, 2) . '/views/' . $view . '.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \RuntimeException("View not found: {$view}");
        }
    }

    /**
     * JSON response döndürür
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Başka sayfaya yönlendirir
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * POST request mi kontrol eder
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * GET request mi kontrol eder
     */
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * POST verisini alır
     */
    protected function getPost(string $key, $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * GET verisini alır
     */
    protected function getQuery(string $key, $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * XSS korumalı çıktı
     */
    protected function escape(?string $str): string
    {
        return Helpers::escape($str);
    }

    /**
     * Flash mesaj kaydeder
     */
    protected function setFlash(string $type, string $message): void
    {
        $this->session->flash($type, $message);
    }

    /**
     * Flash mesaj alır
     */
    protected function getFlash(string $type): ?string
    {
        return $this->session->getFlash($type);
    }

    /**
     * Başarı mesajı
     */
    protected function success(string $message): void
    {
        $this->setFlash('success', $message);
    }

    /**
     * Hata mesajı
     */
    protected function error(string $message): void
    {
        $this->setFlash('error', $message);
    }

    /**
     * Kullanıcı giriş yapmış mı kontrol eder
     */
    protected function isLoggedIn(): bool
    {
        return $this->auth->isLoggedIn();
    }

    /**
     * Giriş gerektirir
     */
    protected function requireLogin(): void
    {
        $this->auth->requireLogin();
    }

    /**
     * Belirli rol gerektirir
     */
    protected function requireRole(string $role): void
    {
        $this->auth->requireRole($role);
    }
}

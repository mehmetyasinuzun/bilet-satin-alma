<?php
namespace App\Controllers;

use App\Entities\User;

/**
 * Auth Controller Class
 * 
 * OOP Prensipleri:
 * - Inheritance: BaseController'dan türer
 * - Polymorphism: Farklı action metodları
 */
class AuthController extends BaseController
{
    /**
     * Giriş sayfası
     */
    public function login(): void
    {
        $error = '';

        if ($this->isPost()) {
            $email = trim($this->getPost('email', ''));
            $password = $this->getPost('password', '');

            $user = $this->auth->login($email, $password);

            if ($user) {
                // Rol bazlı yönlendirme
                switch ($user->getRole()) {
                    case User::ROLE_ADMIN:
                        $this->redirect('admin_dashboard.php');
                        break;
                    case User::ROLE_COMPANY_ADMIN:
                        $this->redirect('company_admin_dashboard.php');
                        break;
                    default:
                        $this->redirect('user_dashboard.php');
                }
            } else {
                $error = $this->auth->getFirstError();
            }
        }

        // View'a veri gönder (legacy uyumluluk için)
        $GLOBALS['error'] = $error;
    }

    /**
     * Kayıt sayfası
     */
    public function register(): void
    {
        $error = '';
        $success = '';

        if ($this->isPost()) {
            $data = [
                'full_name' => $this->getPost('full_name'),
                'email' => $this->getPost('email'),
                'password' => $this->getPost('password'),
                'confirm_password' => $this->getPost('confirm_password')
            ];

            $user = $this->auth->register($data);

            if (!$this->auth->hasErrors()) {
                $success = 'Kayıt başarılı! Giriş yapabilirsiniz.';
            } else {
                $error = $this->auth->getFirstError();
            }
        }

        $GLOBALS['error'] = $error;
        $GLOBALS['success'] = $success;
    }

    /**
     * Çıkış
     */
    public function logout(): void
    {
        $this->auth->logout();
        $this->redirect('index.php');
    }
}

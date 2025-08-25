<?php

namespace App\Lib;

class View
{
    private $data = [];

    public function render($template, $data = [])
    {
        $this->data = array_merge($this->data, $data);
        
        $templatePath = __DIR__ . '/../Views/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \Exception("Template {$template} not found");
        }

        // Extract data to variables for use in template
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the template
        include $templatePath;
        
        // Return the buffered content
        return ob_get_clean();
    }

    public function partial($template, $data = [])
    {
        return $this->render('partials/' . $template, $data);
    }

    public function escape($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    public function formatDate($date, $format = 'd/m/Y')
    {
        if (!$date) return '';
        
        if (is_string($date)) {
            $date = new \DateTime($date);
        }
        
        return $date->format($format);
    }

    public function formatTime($time, $format = 'H:i')
    {
        if (!$time) return '';
        
        if (is_string($time)) {
            $time = new \DateTime($time);
        }
        
        return $time->format($format);
    }

    public function formatMoney($amount, $currency = 'EGP')
    {
        return number_format($amount, 2) . ' ' . $currency;
    }

    public function getStatusBadgeClass($status)
    {
        $classes = [
            'Booked' => 'badge bg-primary',
            'CheckedIn' => 'badge bg-info',
            'InProgress' => 'badge bg-warning',
            'Completed' => 'badge bg-success',
            'Cancelled' => 'badge bg-danger',
            'NoShow' => 'badge bg-secondary',
            'Rescheduled' => 'badge bg-info'
        ];
        
        return $classes[$status] ?? 'badge bg-secondary';
    }

    public function getVisitTypeBadgeClass($type)
    {
        $classes = [
            'New' => 'badge bg-primary',
            'FollowUp' => 'badge bg-success',
            'Procedure' => 'badge bg-warning'
        ];
        
        return $classes[$type] ?? 'badge bg-secondary';
    }

    public function getPaymentMethodIcon($method)
    {
        $icons = [
            'Cash' => 'bi-cash-coin',
            'Card' => 'bi-credit-card',
            'Wallet' => 'bi-wallet2',
            'Transfer' => 'bi-bank'
        ];
        
        return $icons[$method] ?? 'bi-question-circle';
    }

    public function isActiveRoute($route)
    {
        $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        return strpos($currentPath, $route) === 0;
    }

    public function getCurrentUser()
    {
        return $_SESSION['user'] ?? null;
    }

    public function hasPermission($permission)
    {
        $user = $this->getCurrentUser();
        if (!$user) return false;
        
        // Simple permission check - can be enhanced with RBAC
        switch ($permission) {
            case 'cancel_appointments':
                return in_array($user['role'], ['doctor', 'admin']);
            case 'approve_discounts':
                return in_array($user['role'], ['doctor', 'admin']);
            case 'lock_daily':
                return in_array($user['role'], ['doctor', 'admin']);
            case 'delete_bookings':
                return $user['role'] === 'admin';
            default:
                return true;
        }
    }

    public function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public function csrfField()
    {
        $token = $this->generateCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $this->escape($token) . '">';
    }
}

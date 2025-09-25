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
    
    public function url($path = '')
    {
        return \App\Lib\UrlHelper::url($path);
    }
    
    public function getTimelineEventColor($eventType)
    {
        $colors = [
            'appointment_booked' => 'primary',
            'consultation' => 'success',
            'payment' => 'info',
            'prescription' => 'warning',
            'cancellation' => 'danger',
            'default' => 'secondary'
        ];
        
        return $colors[$eventType] ?? $colors['default'];
    }
    
    public function getTimelineEventIcon($eventType)
    {
        $icons = [
            'appointment_booked' => 'calendar-plus',
            'consultation' => 'person-check',
            'payment' => 'credit-card',
            'prescription' => 'prescription2',
            'cancellation' => 'x-circle',
            'default' => 'circle'
        ];
        
        return $icons[$eventType] ?? $icons['default'];
    }
    
    public function getStatusColor($status)
    {
        $colors = [
            'Booked' => 'primary',
            'CheckedIn' => 'info', 
            'InProgress' => 'warning',
            'Completed' => 'success',
            'Cancelled' => 'danger',
            'NoShow' => 'secondary',
            'Rescheduled' => 'info',
            'scheduled' => 'primary',
            'confirmed' => 'info',
            'in_progress' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'no_show' => 'secondary',
            'default' => 'secondary'
        ];
        return $colors[$status] ?? $colors['default'];
    }

    public function getPaymentTypeBadgeClass($type)
    {
        $classes = [
            'new_booking' => 'badge bg-primary',
            'followup' => 'badge bg-success',
            'consultation' => 'badge bg-info',
            'procedure' => 'badge bg-warning',
            'other' => 'badge bg-secondary',
            'Booking' => 'badge bg-primary',
            'FollowUp' => 'badge bg-success',
            'Consultation' => 'badge bg-info',
            'Procedure' => 'badge bg-warning',
            'Other' => 'badge bg-secondary'
        ];
        
        return $classes[$type] ?? 'badge bg-secondary';
    }

    public function getPaymentTypeText($type)
    {
        $texts = [
            'new_booking' => 'حجز جديد',
            'followup' => 'إعادة كشف',
            'consultation' => 'استشارة طبية',
            'procedure' => 'إجراء طبي',
            'other' => 'أخرى',
            'Booking' => 'حجز جديد',
            'FollowUp' => 'إعادة كشف',
            'Consultation' => 'استشارة طبية',
            'Procedure' => 'إجراء طبي',
            'Other' => 'أخرى'
        ];
        
        return $texts[$type] ?? $type;
    }

    public function getPaymentMethodBadgeClass($method)
    {
        $classes = [
            'Cash' => 'badge bg-success',
            'Card' => 'badge bg-primary',
            'Transfer' => 'badge bg-info',
            'Wallet' => 'badge bg-secondary'
        ];
        
        return $classes[$method] ?? 'badge bg-secondary';
    }

    public function getPaymentMethodText($method)
    {
        $texts = [
            'Cash' => 'نقدي',
            'Card' => 'بطاقة ائتمان',
            'Transfer' => 'تحويل بنكي',
            'Wallet' => 'محفظة إلكترونية'
        ];
        
        return $texts[$method] ?? $method;
    }

    public function getStatusText($status)
    {
        $texts = [
            'Completed' => 'مكتمل',
            'Pending' => 'في الانتظار',
            'Failed' => 'فشل',
            'Cancelled' => 'ملغي',
            'Refunded' => 'مسترد'
        ];
        
        return $texts[$status] ?? $status;
    }

    public function getExpenseCategoryBadgeClass($category)
    {
        $classes = [
            'utilities' => 'badge bg-primary',
            'medical' => 'badge bg-danger',
            'maintenance' => 'badge bg-warning',
            'office' => 'badge bg-info',
            'salary' => 'badge bg-success',
            'other' => 'badge bg-secondary'
        ];
        
        return $classes[$category] ?? 'badge bg-secondary';
    }

    public function getExpenseCategoryText($category)
    {
        $texts = [
            'utilities' => 'مرافق عامة',
            'medical' => 'طبية',
            'maintenance' => 'صيانة',
            'office' => 'مكتبية',
            'salary' => 'راتب',
            'other' => 'أخرى'
        ];
        
        return $texts[$category] ?? $category;
    }

    public function getVisitTypeBadgeClass($type)
    {
        $classes = [
            'new_booking' => 'badge bg-primary',
            'followup' => 'badge bg-success',
            'consultation' => 'badge bg-info',
            'procedure' => 'badge bg-warning',
            'other' => 'badge bg-secondary'
        ];
        
        return $classes[$type] ?? 'badge bg-secondary';
    }

    public function getVisitTypeText($type)
    {
        $texts = [
            'new_booking' => 'حجز جديد',
            'followup' => 'إعادة كشف',
            'consultation' => 'استشارة طبية',
            'procedure' => 'إجراء طبي',
            'other' => 'أخرى'
        ];
        
        return $texts[$type] ?? $type;
    }

    public function getBookingStatusBadgeClass($status)
    {
        $classes = [
            'Booked' => 'badge bg-primary',
            'CheckedIn' => 'badge bg-success',
            'Completed' => 'badge bg-info',
            'Cancelled' => 'badge bg-danger',
            'NoShow' => 'badge bg-warning',
            'default' => 'badge bg-secondary'
        ];
        
        return $classes[$status] ?? $classes['default'];
    }

    public function getBookingStatusText($status)
    {
        $texts = [
            'Booked' => 'محجوز',
            'CheckedIn' => 'تم الحضور',
            'Completed' => 'مكتمل',
            'Cancelled' => 'ملغي',
            'NoShow' => 'لم يحضر',
            'default' => 'غير محدد'
        ];
        
        return $texts[$status] ?? $status;
    }

    public function calculateAge($dob)
    {
        if (!$dob) {
            return 'غير محدد';
        }
        
        $today = new \DateTime();
        $birthDate = new \DateTime($dob);
        $age = $today->diff($birthDate);
        
        return $age->y;
    }

    public function formatDateSimple($dateString)
    {
        if (!$dateString) {
            return 'غير محدد';
        }
        
        $date = new \DateTime($dateString);
        return $date->format('Y-m-d');
    }
}

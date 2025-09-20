<?php

namespace App\Controllers;

use App\Lib\Auth;
use App\Lib\View;
use App\Lib\Validator;
use App\Config\Database;
use App\Config\Constants;
use PDO;

class AdminController
{
    private $auth;
    private $view;
    private $validator;
    private $pdo;

    public function __construct()
    {
        $this->auth = new Auth();
        $this->view = new View();
        $this->validator = new Validator();
        $this->pdo = Database::getInstance()->getConnection();
        
        // Require admin authentication (handles View As mode automatically)
        $this->auth->requireRole(['admin']);
    }

    public function dashboard()
    {
        $user = $this->auth->user();
        
        // Get system statistics
        $stats = $this->getSystemStats();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get system health
        $systemHealth = $this->getSystemHealth();
        
        // Get View As status
        $viewAsStatus = $this->getViewAsStatus();
        
        $content = $this->view->render('admin/dashboard', [
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'systemHealth' => $systemHealth,
            'viewAsStatus' => $viewAsStatus
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Admin Dashboard',
            'pageTitle' => 'System Administration',
            'pageSubtitle' => 'Welcome back, ' . $user['name'],
            'content' => $content
        ]);
    }

    public function users()
    {
        $user = $this->auth->user();
        
        // Get all users with pagination
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $users = $this->getUsers($page, $search, $role);
        
        $content = $this->view->render('admin/users', [
            'users' => $users,
            'currentPage' => $page,
            'search' => $search,
            'role' => $role
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'User Management - Admin Dashboard',
            'pageTitle' => 'User Management',
            'pageSubtitle' => 'Manage system users and permissions',
            'content' => $content
        ]);
    }

    public function createUser()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Validate input
            $rules = [
                'name' => 'required|max:100',
                'username' => 'required|min:3|max:20',
                'email' => 'required|email|unique:users,email',
                'role' => 'required|in:doctor,secretary,admin',
                'password' => 'required|min:8'
            ];
            
            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                throw new \Exception('Validation failed');
            }
            
            // Create user
            $userId = $this->createUserRecord($data);
            
            if ($userId) {
                // If doctor, create doctor record
                if ($data['role'] === 'doctor') {
                    $this->createDoctorRecord($userId, $data);
                }
                
                header('Location: /admin/users?success=User created successfully');
                exit;
            } else {
                throw new \Exception('Failed to create user');
            }
            
        } catch (\Exception $e) {
            header('Location: /admin/users?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function updateUser($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Validate input
            $rules = [
                'name' => 'required|max:100',
                'username' => 'required|min:3|max:20',
                'email' => 'required|email',
                'role' => 'required|in:doctor,secretary,admin',
                'is_active' => 'boolean'
            ];
            
            $data = $_POST;
            if (!$this->validator->validate($data, $rules)) {
                throw new \Exception('Validation failed');
            }
            
            // Update user
            $result = $this->updateUserRecord($id, $data);
            
            if ($result) {
                header('Location: /admin/users?success=User updated successfully');
                exit;
            } else {
                throw new \Exception('Failed to update user');
            }
            
        } catch (\Exception $e) {
            header('Location: /admin/users?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function deleteUser($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            // Validate CSRF token
            if (!$this->validateCsrfToken()) {
                throw new \Exception('Invalid CSRF token');
            }
            
            // Check if user can be deleted
            if (!$this->canDeleteUser($id)) {
                throw new \Exception('User cannot be deleted (has associated records)');
            }
            
            // Delete user
            $result = $this->deleteUserRecord($id);
            
            if ($result) {
                header('Location: /admin/users?success=User deleted successfully');
                exit;
            } else {
                throw new \Exception('Failed to delete user');
            }
            
        } catch (\Exception $e) {
            header('Location: /admin/users?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function reports()
    {
        $user = $this->auth->user();
        
        // Get report parameters
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-t');
        $reportType = $_GET['type'] ?? 'revenue';
        
        // Generate report data
        $reportData = $this->generateReport($reportType, $startDate, $endDate);
        
        $content = $this->view->render('admin/reports', [
            'reportData' => $reportData,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType
        ]);
        
        echo $this->view->render('layouts/main', [
            'title' => 'Reports - Admin Dashboard',
            'pageTitle' => 'System Reports',
            'pageSubtitle' => 'Generate and view system reports',
            'content' => $content
        ]);
    }

    public function exportReport()
    {
        try {
            $reportType = $_GET['type'] ?? 'revenue';
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            $format = $_GET['format'] ?? 'csv';
            
            // Generate report data
            $reportData = $this->generateReport($reportType, $startDate, $endDate);
            
            // Export based on format
            if ($format === 'csv') {
                $this->exportToCsv($reportData, $reportType, $startDate, $endDate);
            } else {
                throw new \Exception('Unsupported export format');
            }
            
        } catch (\Exception $e) {
            header('Location: /admin/reports?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    public function settings()
    {
        $user = $this->auth->user();
        
        try {
            // Get system settings
            $settings = $this->getSystemSettings();
            
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$this->validateCsrfToken()) {
                    throw new Exception('Invalid CSRF token');
                }
                
                // Handle file uploads
                $this->handleLogoUploads();
                
                $this->updateSystemSettings($_POST);
                $_SESSION['success_message'] = 'Settings updated successfully';
                header('Location: /admin/settings');
                exit;
            }

            // Generate CSRF token
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

            $content = $this->view->render('admin/settings', [
                'settings' => $settings,
                'csrf_token' => $_SESSION['csrf_token']
            ]);
            
            echo $this->view->render('layouts/main', [
                'title' => 'System Settings',
                'pageTitle' => 'System Settings',
                'pageSubtitle' => 'Manage system configuration',
                'content' => $content
            ]);
        } catch (Exception $e) {
            error_log("Settings error: " . $e->getMessage());
            $_SESSION['error_message'] = 'Failed to load settings: ' . $e->getMessage();
            header('Location: /admin/dashboard');
            exit;
        }
    }

    /**
     * View As functionality - Switch to different role view
     */
    public function viewAs()
    {
        $user = $this->auth->user();
        
        // Only allow admins to use View As
        if ($user['role'] !== 'admin') {
            http_response_code(403);
            echo "Access denied - View As is only available for administrators";
            return;
        }
        
        $role = $_GET['role'] ?? '';
        $allowedRoles = ['doctor', 'secretary'];
        
        if (!in_array($role, $allowedRoles)) {
            header('Location: /admin/dashboard?error=' . urlencode('Invalid role for View As'));
            exit;
        }
        
        try {
            // Start View As mode
            $this->auth->startViewAs($role);
            
            // Redirect to the appropriate dashboard
            if ($role === 'doctor') {
                header('Location: /doctor/dashboard?view_as=1');
            } elseif ($role === 'secretary') {
                header('Location: /secretary/dashboard?view_as=1');
            }
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/dashboard?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Stop View As mode and return to admin dashboard
     */
    public function stopViewAs()
    {
        $user = $this->auth->user();
        
        // Only allow admins to stop View As
        if ($user['role'] !== 'admin' && !$this->auth->isViewAsMode()) {
            http_response_code(403);
            echo "Access denied";
            return;
        }
        
        try {
            // Stop View As mode
            $this->auth->stopViewAs();
            
            // Redirect back to admin dashboard
            header('Location: /admin/dashboard?success=' . urlencode('Returned to admin view'));
            exit;
            
        } catch (\Exception $e) {
            header('Location: /admin/dashboard?error=' . urlencode($e->getMessage()));
            exit;
        }
    }

    /**
     * Get View As status for admin dashboard
     */
    public function getViewAsStatus()
    {
        $user = $this->auth->user();
        
        return [
            'isViewAsMode' => $this->auth->isViewAsMode(),
            'currentRole' => $this->auth->getCurrentRole(),
            'originalRole' => $this->auth->getOriginalRole(),
            'isAdmin' => $user['role'] === 'admin'
        ];
    }

    // Helper methods
    private function getSystemStats()
    {
        $stats = [];
        
        // User statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_users,
                SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
                SUM(CASE WHEN role = 'doctor' THEN 1 ELSE 0 END) as doctors,
                SUM(CASE WHEN role = 'secretary' THEN 1 ELSE 0 END) as secretaries
            FROM users
        ");
        $stmt->execute();
        $stats['users'] = $stmt->fetch();
        
        // Patient statistics
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as total_patients FROM patients");
        $stmt->execute();
        $stats['patients'] = $stmt->fetch();
        
        // Appointment statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) as total_appointments,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
            FROM appointments
            WHERE date >= CURDATE() - INTERVAL 30 DAY
        ");
        $stmt->execute();
        $stats['appointments'] = $stmt->fetch();
        
        // Financial statistics
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(amount) as total_revenue,
                SUM(discount_amount) as total_discounts,
                COUNT(*) as total_payments
            FROM payments
            WHERE DATE(created_at) >= CURDATE() - INTERVAL 30 DAY
        ");
        $stmt->execute();
        $stats['financial'] = $stmt->fetch();
        
        return $stats;
    }

    private function getRecentActivities()
    {
        $stmt = $this->pdo->prepare("
            SELECT al.*, u.name as user_name
            FROM audit_logs al
            JOIN users u ON al.user_id = u.id
            ORDER BY al.created_at DESC
            LIMIT 20
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getSystemHealth()
    {
        $health = [];
        
        // Database connection
        try {
            $this->pdo->query('SELECT 1');
            $health['database'] = 'Connected';
        } catch (\Exception $e) {
            $health['database'] = 'Error: ' . $e->getMessage();
        }
        
        // Storage space
        $storagePath = __DIR__ . '/../../storage';
        $freeSpace = disk_free_space($storagePath);
        $totalSpace = disk_total_space($storagePath);
        $usedSpace = $totalSpace - $freeSpace;
        $usagePercent = round(($usedSpace / $totalSpace) * 100, 2);
        
        $health['storage'] = [
            'free' => $this->formatBytes($freeSpace),
            'total' => $this->formatBytes($totalSpace),
            'used' => $this->formatBytes($usedSpace),
            'usage_percent' => $usagePercent
        ];
        
        // PHP version
        $health['php_version'] = PHP_VERSION;
        
        // Extensions
        $requiredExtensions = ['pdo', 'pdo_mysql', 'json', 'mbstring'];
        $health['extensions'] = [];
        foreach ($requiredExtensions as $ext) {
            $health['extensions'][$ext] = extension_loaded($ext) ? 'Loaded' : 'Missing';
        }
        
        return $health;
    }

    private function getUsers($page = 1, $search = '', $role = '')
    {
        $limit = Constants::ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;
        
        $whereClause = '';
        $params = [];
        
        if (!empty($search)) {
            $whereClause .= "WHERE name LIKE ? OR email LIKE ?";
            $searchTerm = "%{$search}%";
            $params = [$searchTerm, $searchTerm];
        }
        
        if (!empty($role)) {
            $whereClause = empty($whereClause) ? "WHERE role = ?" : $whereClause . " AND role = ?";
            $params[] = $role;
        }
        
        $sql = "
            SELECT u.*, 
                   COUNT(DISTINCT a.id) as total_appointments,
                   (SELECT COUNT(DISTINCT a2.patient_id) 
                    FROM appointments a2 
                    WHERE a2.doctor_id = u.id) as total_patients
            FROM users u
            LEFT JOIN appointments a ON u.id = a.doctor_id
            {$whereClause}
            GROUP BY u.id
            ORDER BY u.created_at DESC
            LIMIT ? OFFSET ?
        ";
        
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function createUserRecord($data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (name, username, email, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        
        $passwordHash = password_hash($data['password'], PASSWORD_ARGON2ID);
        
        $stmt->execute([
            $data['name'],
            $data['username'],
            $data['email'],
            $passwordHash,
            $data['role']
        ]);
        
        return $this->pdo->lastInsertId();
    }

    private function createDoctorRecord($userId, $data)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO doctors (user_id, display_name, specialty, license_number)
            VALUES (?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $data['name'],
            $data['specialization'] ?? 'Ophthalmology',
            $data['license_number'] ?? 'LIC-' . str_pad($userId, 6, '0', STR_PAD_LEFT)
        ]);
    }

    private function updateUserRecord($id, $data)
    {
        $stmt = $this->pdo->prepare("
            UPDATE users SET name = ?, username = ?, email = ?, role = ?, is_active = ?, updated_at = NOW()
            WHERE id = ?
        ");
        
        return $stmt->execute([
            $data['name'],
            $data['username'],
            $data['email'],
            $data['role'],
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    private function canDeleteUser($id)
    {
        // Check if user has associated records
        $stmt = $this->pdo->prepare("
            SELECT 
                (SELECT COUNT(*) FROM appointments WHERE doctor_id = ?) as appointments,
                (SELECT COUNT(*) FROM payments WHERE received_by = ?) as payments
        ");
        $stmt->execute([$id, $id]);
        $result = $stmt->fetch();
        
        return ($result['appointments'] == 0 && $result['payments'] == 0);
    }

    private function deleteUserRecord($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    private function generateReport($type, $startDate, $endDate)
    {
        switch ($type) {
            case 'revenue':
                return $this->generateRevenueReport($startDate, $endDate);
            case 'appointments':
                return $this->generateAppointmentsReport($startDate, $endDate);
            case 'patients':
                return $this->generatePatientsReport($startDate, $endDate);
            case 'doctors':
                return $this->generateDoctorsReport($startDate, $endDate);
            default:
                return $this->generateRevenueReport($startDate, $endDate);
        }
    }

    private function generateRevenueReport($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(p.created_at) as date,
                SUM(p.amount) as daily_revenue,
                COUNT(*) as transactions,
                SUM(p.discount_amount) as discounts
            FROM payments p
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateAppointmentsReport($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(a.date) as date,
                COUNT(*) as total_appointments,
                SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN a.status = 'NoShow' THEN 1 ELSE 0 END) as no_show
            FROM appointments a
            WHERE DATE(a.date) BETWEEN ? AND ?
            GROUP BY DATE(a.date)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generatePatientsReport($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                DATE(p.created_at) as date,
                COUNT(*) as new_patients,
                SUM(CASE WHEN p.gender = 'Male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN p.gender = 'Female' THEN 1 ELSE 0 END) as female
            FROM patients p
            WHERE DATE(p.created_at) BETWEEN ? AND ?
            GROUP BY DATE(p.created_at)
            ORDER BY date
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function generateDoctorsReport($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                d.display_name,
                COUNT(a.id) as total_appointments,
                SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                AVG(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) * 100 as completion_rate
            FROM doctors d
            LEFT JOIN appointments a ON d.id = a.doctor_id AND DATE(a.date) BETWEEN ? AND ?
            GROUP BY d.id, d.display_name
            ORDER BY total_appointments DESC
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    private function exportToCsv($data, $type, $startDate, $endDate)
    {
        $filename = "{$type}_report_{$startDate}_to_{$endDate}.csv";
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            // Write headers
            fputcsv($output, array_keys($data[0]));
            
            // Write data
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    private function validateCsrfToken()
    {
        if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }

    private function getSystemSettings()
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_key, setting_value, setting_type FROM settings");
            $stmt->execute();
            $settings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $result = [];
            foreach ($settings as $setting) {
                $key = $setting['setting_key'];
                $value = $setting['setting_value'];
                $type = $setting['setting_type'];
                
                // Convert value based on type
                switch ($type) {
                    case 'integer':
                        $result[$key] = (int) $value;
                        break;
                    case 'boolean':
                        $result[$key] = (bool) $value;
                        break;
                    case 'json':
                        $result[$key] = json_decode($value, true);
                        break;
                    default:
                        $result[$key] = $value;
                }
            }
            
            // Set defaults for missing settings
            $defaults = [
                'clinic_name' => 'Roaya Clinic',
                'clinic_name_arabic' => 'رؤية لطب وجراحة العيون',
                'clinic_email' => 'info@roayaclinic.com',
                'clinic_phone' => '+20 123 456 7890',
                'clinic_address' => 'Cairo, Egypt',
                'clinic_logo' => '/assets/images/Light.png',
                'clinic_logo_print' => '/assets/images/Light.png',
                'clinic_logo_watermark' => '/assets/images/Light.png',
                'new_visit_cost' => '100',
                'repeated_visit_cost' => '50',
                'timezone' => 'Africa/Cairo',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'items_per_page' => 10,
                'backup_frequency' => 'daily',
                'email_notifications' => true,
                'sms_notifications' => false,
                'maintenance_mode' => false
            ];
            
            return array_merge($defaults, $result);
        } catch (Exception $e) {
            error_log("Error getting system settings: " . $e->getMessage());
            // Return defaults if database error
            return [
                'clinic_name' => 'Roaya Clinic',
                'clinic_email' => 'info@roayaclinic.com',
                'clinic_phone' => '+20 123 456 7890',
                'clinic_address' => 'Cairo, Egypt',
                'timezone' => 'Africa/Cairo',
                'date_format' => 'Y-m-d',
                'time_format' => 'H:i',
                'items_per_page' => 10,
                'backup_frequency' => 'daily',
                'email_notifications' => true,
                'sms_notifications' => false,
                'maintenance_mode' => false
            ];
        }
    }

    private function updateSystemSettings($data)
    {
        $allowedSettings = [
            'clinic_name', 'clinic_email', 'clinic_phone', 'clinic_address',
            'timezone', 'date_format', 'time_format', 'items_per_page',
            'backup_frequency', 'email_notifications', 'sms_notifications', 'maintenance_mode'
        ];

        try {
            $this->pdo->beginTransaction();
            
            foreach ($data as $key => $value) {
                if (in_array($key, $allowedSettings)) {
                    // Validate and sanitize the value
                    if ($key === 'clinic_email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        throw new Exception('Invalid email address');
                    }
                    if ($key === 'items_per_page' && (!is_numeric($value) || $value < 1 || $value > 100)) {
                        throw new Exception('Items per page must be between 1 and 100');
                    }
                    
                    // Determine setting type and convert value
                    $settingType = 'string';
                    if (in_array($key, ['email_notifications', 'sms_notifications', 'maintenance_mode'])) {
                        $value = (bool) $value;
                        $settingType = 'boolean';
                    } elseif ($key === 'items_per_page') {
                        $value = (int) $value;
                        $settingType = 'integer';
                    }
                    
                    // Convert boolean to string for database storage
                    if ($settingType === 'boolean') {
                        $dbValue = $value ? '1' : '0';
                    } else {
                        $dbValue = (string) $value;
                    }
                    
                    // Insert or update setting
                    $stmt = $this->pdo->prepare("
                        INSERT INTO settings (setting_key, setting_value, setting_type) 
                        VALUES (?, ?, ?) 
                        ON DUPLICATE KEY UPDATE 
                        setting_value = VALUES(setting_value),
                        setting_type = VALUES(setting_type),
                        updated_at = CURRENT_TIMESTAMP
                    ");
                    $stmt->execute([$key, $dbValue, $settingType]);
                }
            }
            
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function handleLogoUploads()
    {
        $uploadDir = '/var/www/html/clinic/public/uploads/logos/';
        $allowedTypes = [
            'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $maxSize = 10 * 1024 * 1024; // 10MB for documents
        
        $logoFields = ['clinic_logo_print', 'clinic_logo_watermark']; // clinic_logo disabled
        
        foreach ($logoFields as $field) {
            // Handle file upload
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES[$field];
                
                // Validate file type
                if (!in_array($file['type'], $allowedTypes)) {
                    throw new \Exception("Invalid file type for {$field}. Only JPEG, PNG, GIF, and SVG are allowed.");
                }
                
                // Validate file size
                if ($file['size'] > $maxSize) {
                    throw new \Exception("File too large for {$field}. Maximum size is 5MB.");
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = $field . '_' . time() . '_' . uniqid() . '.' . $extension;
                $filepath = $uploadDir . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Update setting with new file path
                    $this->updateSetting($field, '/uploads/logos/' . $filename);
                } else {
                    throw new \Exception("Failed to upload {$field}");
                }
            }
            // Handle text path input
            elseif (isset($_POST[$field . '_path']) && !empty($_POST[$field . '_path'])) {
                $path = $_POST[$field . '_path'];
                // Validate that it's a valid path
                if (filter_var($path, FILTER_VALIDATE_URL) || (strpos($path, '/') === 0 && file_exists('/var/www/html/clinic/public' . $path))) {
                    $this->updateSetting($field, $path);
                }
            }
        }
    }

    private function updateSetting($key, $value)
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO settings (setting_key, setting_value, setting_type, updated_at) 
                VALUES (?, ?, 'string', NOW())
                ON DUPLICATE KEY UPDATE 
                setting_value = VALUES(setting_value), 
                updated_at = NOW()
            ");
            $stmt->execute([$key, $value]);
        } catch (\Exception $e) {
            error_log("Error updating setting {$key}: " . $e->getMessage());
            throw $e;
        }
    }
}

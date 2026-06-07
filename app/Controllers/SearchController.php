<?php
namespace App\Controllers;

use App\Config\Database;
use App\Lib\Auth;

class SearchController
{
    private $pdo;
    private $auth;

    public function __construct()
    {
        $this->pdo  = Database::getInstance()->getConnection();
        $this->auth = new Auth();
    }

    /**
     * GET /api/search/palette?q=&scope=
     * scopes: patients | pages | actions | todos | all (default)
     */
    public function palette()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
        header('Content-Type: application/json; charset=utf-8');

        $user = $this->auth->user();
        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $userId = (int) $user['id'];
        $q      = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $scope  = isset($_GET['scope']) ? strtolower(trim((string) $_GET['scope'])) : 'all';

        $allowedScopes = ['patients', 'pages', 'actions', 'todos', 'all'];
        if (!in_array($scope, $allowedScopes, true)) {
            $scope = 'all';
        }

        $results = [
            'patients' => [],
            'pages'    => [],
            'actions'  => [],
            'todos'    => [],
        ];

        // Empty query → return empty arrays for every scope (always success).
        if ($q === '') {
            echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        $limitPatients = ($scope === 'all') ? 3 : 5;
        $limitPages    = ($scope === 'all') ? 3 : 20;
        $limitActions  = ($scope === 'all') ? 3 : 20;
        $limitTodos    = ($scope === 'all') ? 3 : 5;

        try {
            if ($scope === 'patients' || $scope === 'all') {
                $results['patients'] = $this->searchPatients($q, $limitPatients);
            }
            if ($scope === 'pages' || $scope === 'all') {
                $results['pages'] = $this->searchPages($q, $limitPages);
            }
            if ($scope === 'actions' || $scope === 'all') {
                $results['actions'] = $this->searchActions($q, $limitActions);
            }
            if ($scope === 'todos' || $scope === 'all') {
                $results['todos'] = $this->searchTodos($userId, $q, $limitTodos);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Search failed',
                'error'   => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            return;
        }

        echo json_encode(['success' => true, 'results' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /* ---------------------------------------------------------------- */
    /* scope helpers                                                    */
    /* ---------------------------------------------------------------- */

    private function searchPatients(string $q, int $limit): array
    {
        $like = '%' . $q . '%';
        $sql  = "SELECT id, first_name, last_name, phone
                 FROM patients
                 WHERE (first_name LIKE ?
                        OR last_name LIKE ?
                        OR CONCAT(first_name, ' ', last_name) LIKE ?
                        OR phone LIKE ?)
                 LIMIT " . (int) $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$like, $like, $like, $like]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $out = [];
        foreach ($rows as $r) {
            $id   = (int) $r['id'];
            $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
            $out[] = [
                'id'       => $id,
                'label'    => $name !== '' ? $name : ('#' . $id),
                'sublabel' => $r['phone'] ?? '',
                'icon'     => 'person',
                'link'     => '/doctor/patients/' . $id,
                'type'     => 'patient',
            ];
        }
        return $out;
    }

    private function searchPages(string $q, int $limit): array
    {
        $pages = [
            ['id' => 'page-dashboard',     'label' => 'Dashboard',          'icon' => 'speedometer2',   'link' => '/doctor/dashboard'],
            ['id' => 'page-calendar',      'label' => 'Calendar',           'icon' => 'calendar3',      'link' => '/doctor/calendar'],
            ['id' => 'page-patients',      'label' => 'Patients',           'icon' => 'people',         'link' => '/doctor/patients'],
            ['id' => 'page-board',         'label' => 'Patients Board',     'icon' => 'kanban',         'link' => '/doctor/board'],
            ['id' => 'page-payments',      'label' => 'Payments',           'icon' => 'cash-coin',      'link' => '/doctor/payments'],
            ['id' => 'page-day-close',     'label' => 'Daily Closure',      'icon' => 'journal-check',  'link' => '/doctor/payments/day-close'],
            ['id' => 'page-reports',       'label' => 'Reports',            'icon' => 'bar-chart',      'link' => '/doctor/reports'],
            ['id' => 'page-alerts',        'label' => 'Alerts',             'icon' => 'bell',           'link' => '/doctor/alerts'],
            ['id' => 'page-todos',         'label' => 'To-Do',              'icon' => 'check2-square',  'link' => '/doctor/todos'],
            ['id' => 'page-settings',      'label' => 'Settings',           'icon' => 'gear',           'link' => '/doctor/settings'],
            ['id' => 'page-edit-consult',  'label' => 'Edit Consultation',  'icon' => 'pencil-square',  'link' => null],
            ['id' => 'page-notepad',       'label' => 'Notepad',            'icon' => 'sticky',         'link' => 'action:quick-note'],
        ];

        $needle = mb_strtolower($q);
        $out    = [];
        foreach ($pages as $p) {
            if (mb_strpos(mb_strtolower($p['label']), $needle) !== false) {
                $out[] = [
                    'id'    => $p['id'],
                    'label' => $p['label'],
                    'icon'  => $p['icon'],
                    'link'  => $p['link'],
                    'type'  => 'page',
                ];
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return $out;
    }

    private function searchActions(string $q, int $limit): array
    {
        // Keep in sync with assets/js/actions-registry.js (palette-visible actions).
        $actions = [
            ['id' => 'act-new-patient',    'label' => 'New Patient',            'icon' => 'person-plus',      'link' => 'action:new-patient'],
            ['id' => 'act-new-booking',    'label' => 'New Booking',            'icon' => 'calendar-plus',    'link' => 'action:new-booking'],
            ['id' => 'act-new-todo',       'label' => 'New To-Do',              'icon' => 'check2-square',    'link' => 'action:new-todo'],
            ['id' => 'act-new-alert',      'label' => 'New Alert',              'icon' => 'bell-fill',        'link' => 'action:new-alert'],
            ['id' => 'act-new-note',       'label' => 'New Note',               'icon' => 'sticky',           'link' => 'action:new-note'],
            ['id' => 'act-go-today',       'label' => 'Go to Today',            'icon' => 'calendar',         'link' => 'action:go-to-today'],
            ['id' => 'act-daily-closure',  'label' => 'Daily Closure',          'icon' => 'journal-check',    'link' => 'action:daily-closure'],
            ['id' => 'act-reports',        'label' => 'Reports',                'icon' => 'bar-chart',        'link' => 'action:reports'],
            ['id' => 'act-payments',       'label' => 'Payments',               'icon' => 'cash-coin',        'link' => 'action:payments'],
            ['id' => 'act-focus-mode',     'label' => 'Toggle Focus Mode',      'icon' => 'eye',              'link' => 'action:focus-mode'],
            ['id' => 'act-theme-picker',   'label' => 'Open Theme Picker',      'icon' => 'palette',          'link' => 'action:theme-picker'],
            ['id' => 'act-keyboard-help',  'label' => 'Open Keyboard Shortcuts','icon' => 'keyboard',         'link' => 'action:keyboard-help'],
        ];

        $needle = mb_strtolower($q);
        $out    = [];
        foreach ($actions as $a) {
            if (mb_strpos(mb_strtolower($a['label']), $needle) !== false) {
                $out[] = [
                    'id'    => $a['id'],
                    'label' => $a['label'],
                    'icon'  => $a['icon'],
                    'link'  => $a['link'],
                    'type'  => 'action',
                ];
                if (count($out) >= $limit) {
                    break;
                }
            }
        }
        return $out;
    }

    private function searchTodos(int $userId, string $q, int $limit): array
    {
        $like = '%' . $q . '%';
        $sql  = "SELECT id, title
                 FROM todos
                 WHERE user_id = ?
                   AND status = 'open'
                   AND title LIKE ?
                 LIMIT " . (int) $limit;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $like]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $out = [];
        foreach ($rows as $r) {
            $id    = (int) $r['id'];
            $title = (string) ($r['title'] ?? '');
            $out[] = [
                'id'    => $id,
                'label' => $title !== '' ? $title : ('To-Do #' . $id),
                'icon'  => 'check2-square',
                'link'  => '/doctor/todos?focus=' . $id,
                'type'  => 'todo',
            ];
        }
        return $out;
    }
}

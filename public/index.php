<?php
session_start();

$page = $_GET['page'] ?? (isset($_SESSION['user_id']) ? 'login' : 'accueil');
$action = $_GET['action'] ?? null;
$controllerPath = __DIR__ . '/../app/controllers/';

switch ($page) {
    case 'accueil':
        require_once __DIR__ . '/../app/views/Accueil/Accueil.php';
        exit;

    case 'login':
        require_once $controllerPath . 'LoginController.php';
        $controller = new LoginController();

        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $controller->logout();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->login();
        } else {
            $controller->showLoginForm();
        }
        exit;

    case 'register':
        require_once $controllerPath . 'RegisterController.php';
        $controller = new RegisterController();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && (($_GET['action'] ?? '') === 'register')) {
            $controller->register();
        } else {
            $controller->showForm();
        }
        exit;

    case 'patient_dashboard':
    case 'patient_appointments':
    case 'patient_ordonnances':
    case 'patient_medicaments':
        require_once __DIR__ . '/../config/database.php';
        require_once $controllerPath . 'PatientController.php';

        $db = Database::getInstance()->getConnection();
        $controller = new PatientController($db);

        if ($page === 'patient_appointments' && ($_GET['action'] ?? '') === 'slots' && $_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->appointmentSlots();
            exit;
        }

        if ($page === 'patient_appointments' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $actionParam = $_GET['action'] ?? 'book';
            if ($actionParam === 'cancel') {
                $controller->cancelAppointment();
                exit;
            }

            $controller->bookAppointment();
            exit;
        }

        $actionMap = [
            'patient_dashboard' => 'dashboard',
            'patient_appointments' => 'appointments',
            'patient_ordonnances' => 'ordonnances',
            'patient_medicaments' => 'medicaments',
        ];

        $action = $actionMap[$page] ?? 'dashboard';

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo 'Page non trouvée';
            exit;
        }

        $controller->$action();
        exit;

    case 'secretaire_dashboard':
    case 'secretaire_appointments':
    case 'secretaire_patients':
        require_once __DIR__ . '/../config/database.php';
        require_once $controllerPath . 'SecretaireController.php';

        $db = Database::getInstance()->getConnection();
        $controller = new SecretaireController($db);

        $actionMap = [
            'secretaire_dashboard' => 'dashboard',
            'secretaire_appointments' => 'appointments',
            'secretaire_patients' => 'patients',
        ];

        $action = $actionMap[$page] ?? 'dashboard';

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo 'Page non trouvée';
            exit;
        }

        $controller->$action();
        exit;

    case 'medecin_dashboard':
    case 'medecin_agenda':
    case 'medecin_patients':
    case 'medecin_ordonnances':
    case 'medecin_staff':
        require_once __DIR__ . '/../config/database.php';
        require_once $controllerPath . 'MedecinController.php';

        $db = Database::getInstance()->getConnection();
        $controller = new MedecinController($db);

        $actionMap = [
            'medecin_dashboard' => 'dashboard',
            'medecin_agenda' => 'agenda',
            'medecin_patients' => 'patients',
            'medecin_ordonnances' => 'ordonnances',
            'medecin_staff' => 'staff',
        ];

        $action = $actionMap[$page] ?? 'dashboard';

        if (!method_exists($controller, $action)) {
            http_response_code(404);
            echo 'Page non trouvée';
            exit;
        }

        $controller->$action();
        exit;

    case 'admin_dashboard':
    case 'admin_users':
    case 'admin_settings':
    case 'admin_medecins':
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
            header('Location: /Project/public/index.php?page=login');
            exit;
        }

        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../app/models/admin.php';
        require_once __DIR__ . '/../app/controllers/AdminDoctorController.php';
        require_once __DIR__ . '/../app/controllers/AdminUserController.php';

        $db = Database::getInstance()->getConnection();
        $adminModel = new Admin($db);
        $adminModel->id = (int) $_SESSION['user_id'];
        $adminUserController = new AdminUserController($db);

        try {
            $admin = $adminModel->readOne() ?: [];
        } catch (Throwable $e) {
            $admin = [];
        }

        $stats = [
            'usersTotal' => 0,
            'activeSessions' => 0,
            'pendingTickets' => 0,
            'doctorsVerified' => 0,
        ];

        try {
            $stats['usersTotal'] = (int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn();
            $stats['doctorsVerified'] = (int) $db->query("SELECT COUNT(*) FROM users WHERE role = 'medecin'")->fetchColumn();
        } catch (Throwable $e) {
            // valeurs par défaut conservées
        }

        $pendingDoctorCount = 0;
        try {
            $pendingDoctorCount = (int) $db->query("SELECT COUNT(*) FROM temp_medecin WHERE statut = 'pending'")->fetchColumn();
        } catch (Throwable $e) {
            $pendingDoctorCount = 0;
        }

        $systemHealth = [
            'uptime' => 'Indéterminé',
            'latency' => '—',
            'dbStatus' => 'Inconnu',
            'backup' => 'Planifié 02:00',
        ];

        try {
            $start = microtime(true);
            $db->query('SELECT 1');
            $latencyMs = round((microtime(true) - $start) * 1000, 2);
            $systemHealth['latency'] = $latencyMs . ' ms';
            $systemHealth['dbStatus'] = 'Opérationnel';

            $uptimeStmt = $db->query("SHOW STATUS LIKE 'Uptime'");
            $uptimeRow = $uptimeStmt ? $uptimeStmt->fetch(PDO::FETCH_ASSOC) : null;
            if ($uptimeRow && isset($uptimeRow['Value'])) {
                $seconds = (int) $uptimeRow['Value'];
                $hours = floor($seconds / 3600);
                $minutes = floor(($seconds % 3600) / 60);
                $systemHealth['uptime'] = sprintf('%dh %02dm', $hours, $minutes);
            }
        } catch (Throwable $e) {
            $systemHealth['dbStatus'] = 'Erreur: ' . $e->getCode();
        }

        if ($page === 'admin_dashboard') {
            $recentUsers = [];
            $activities = [];
            try {
                $stmt = $db->query('SELECT id, nom, prenom, email, role, date_creation FROM users ORDER BY id DESC LIMIT 5');
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $recentUsers[] = [
                        'id' => $row['id'],
                        'name' => trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? '')) ?: 'Utilisateur',
                        'role' => $row['role'] ?? '—',
                        'created_at' => $row['date_creation'] ?? date('Y-m-d'),
                    ];
                }
            } catch (Throwable $e) {
                $recentUsers = [];
            }
            $activities = [
                [
                    'title' => 'Candidatures médecins en attente',
                    'time' => date('d/m H:i'),
                    'detail' => $pendingDoctorCount . ' dossier(s) nécessitent une revue',
                    'link' => '/Project/public/index.php?page=admin_medecins',
                ],
            ];
            require_once __DIR__ . '/../app/views/Admin/admin_dashboard.php';
            exit;
        }

        if ($page === 'admin_users') {
            $filters = [
                'search' => trim($_GET['search'] ?? ''),
                'role' => $_GET['role'] ?? 'all',
                'status' => $_GET['status'] ?? 'all',
            ];

            $validStatuses = ['all', 'active', 'pending', 'suspended'];
            if (!in_array($filters['status'], $validStatuses, true)) {
                $filters['status'] = 'all';
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'set_status') {
                $userId = (int) ($_POST['user_id'] ?? 0);
                $newStatus = $_POST['new_status'] ?? 'active';
                $result = $adminUserController->updateStatus($userId, $newStatus);
                $_SESSION['admin_user_flash'] = $result;

                $redirectParams = ['page' => 'admin_users'];
                $filterRole = $_POST['filter_role'] ?? 'all';
                $filterStatus = $_POST['filter_status'] ?? 'all';
                $filterSearch = trim($_POST['filter_search'] ?? '');

                if ($filterRole !== 'all') {
                    $redirectParams['role'] = $filterRole;
                }
                if ($filterStatus !== 'all') {
                    $redirectParams['status'] = $filterStatus;
                }
                if ($filterSearch !== '') {
                    $redirectParams['search'] = $filterSearch;
                }

                $redirectUrl = '/Project/public/index.php?' . http_build_query($redirectParams);
                header('Location: ' . $redirectUrl);
                exit;
            }

            $users = $adminUserController->listUsers($filters);
            $flash = $_SESSION['admin_user_flash'] ?? null;
            unset($_SESSION['admin_user_flash']);

            require_once __DIR__ . '/../app/views/Admin/admin_users.php';
            exit;
        }

        if ($page === 'admin_medecins') {
            $doctorController = new AdminDoctorController($db);

            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id'], $_POST['action'])) {
                $applicationId = (int) $_POST['application_id'];
                $action = $_POST['action'];
                $result = $doctorController->handleDecision($applicationId, $action, (int) $_SESSION['user_id']);
                $_SESSION['admin_doctor_flash'] = $result;
                header('Location: /Project/public/index.php?page=admin_medecins&status=' . urlencode($_GET['status'] ?? 'pending'));
                exit;
            }

            $status = $_GET['status'] ?? 'pending';
            $applications = $doctorController->listApplications($status);
            $flash = $_SESSION['admin_doctor_flash'] ?? null;
            unset($_SESSION['admin_doctor_flash']);

            require_once __DIR__ . '/../app/views/Admin/admin_medecins.php';
            exit;
        }

        // admin_settings
        $platformSettings = [];
        $securityRules = [];
        require_once __DIR__ . '/../app/views/Admin/admin_settings.php';
        exit;

    case 'notifications':
        require_once __DIR__ . '/../config/database.php';
        require_once $controllerPath . 'NotificationController.php';

        $db = Database::getInstance()->getConnection();
        $controller = new NotificationController($db);

        if ($action === 'badge') {
            $controller->badge();
        } elseif ($action === 'mark-all') {
            $controller->markAll();
        } elseif ($action === 'mark-read') {
            $controller->markRead();
        } else {
            $controller->list();
        }
        exit;

    case 'medecin_billing':
    case 'secretaire_billing':
    case 'patient_billing':
        require_once __DIR__ . '/../config/database.php';
        require_once $controllerPath . 'BillingController.php';

        $db = Database::getInstance()->getConnection();
        $controller = new BillingController($db);

        $action = $_GET['action'] ?? match($page) {
            'medecin_billing' => 'create',
            'secretaire_billing' => 'manage',
            'patient_billing' => 'invoices',
            default => 'manage'
        };
        $methodMap = [
            'medecin_billing' => ['create'],
            'secretaire_billing' => ['manage', 'review'],
            'patient_billing' => ['invoices', 'view', 'pay'],
        ];

        if (!in_array($action, $methodMap[$page] ?? [], true) || !method_exists($controller, $action)) {
            http_response_code(404);
            echo 'Action non trouvée';
            exit;
        }

        $controller->$action();
        exit;

    default:
        http_response_code(404);
        echo 'Page non trouvée';
        exit;
}

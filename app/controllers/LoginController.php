<?php
require_once __DIR__ . '/../../config/database.php';

class LoginController {
    private $pdo;

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
    }

    public function showLoginForm($errors = []) {
        // Si l'utilisateur est déjà connecté, on le redirige
        if ($this->isLoggedIn()) {
            $this->redirectBasedOnRole();
            return;
        }
        require_once __DIR__ . '/../views/Auth/login.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $errors = [];

            // Validation basique
            if (empty($email) || empty($password)) {
                $errors[] = "Veuillez remplir tous les champs.";
            }

            if (empty($errors)) {
                try {
                    // Vérification des identifiants
                    $user = $this->authenticate($email, $password);
                    
                    if ($user) {
                        // Création de la session
                        $this->createUserSession($user);
                        
                        // Redirection en fonction du rôle
                        $this->redirectBasedOnRole($user['role'] ?? null);
                        return;
                    } else {
                        $errors[] = "Email ou mot de passe incorrect.";
                    }
                } catch (PDOException $e) {
                    error_log("Erreur de connexion : " . $e->getMessage());
                    $errors[] = "Une erreur est survenue lors de la connexion. Veuillez réessayer.";
                }
            }
        }
        
        // Si on arrive ici, c'est qu'il y a eu une erreur
        $this->showLoginForm($errors);
    }

    public function logout() {
        // Suppression des variables de session
        session_unset();
        
        // Destruction de la session
        session_destroy();
        
        // Redirection vers la page de connexion
        header('Location: /Project/public/index.php?page=login');
        exit();
    }

    private function authenticate($email, $password) {
        // Recherche dans la table users indépendamment du rôle
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérification du mot de passe
        if ($user && password_verify($password, $user['motDePasse'])) {
            $role = $user['role'] ?? null;

            // Récupérer les données spécifiques au rôle si nécessaire
            if ($role === 'patient') {
                $stmt = $this->pdo->prepare("SELECT * FROM patient WHERE id = ?");
                $stmt->execute([$user['id']]);
                $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($userDetails) {
                    $user = array_merge($user, $userDetails);
                }
            } elseif ($role === 'medecin') {
                $stmt = $this->pdo->prepare("SELECT * FROM medecin WHERE id = ?");
                $stmt->execute([$user['id']]);
                $userDetails = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($userDetails) {
                    $user = array_merge($user, $userDetails);
                }
            }
            return $user;
        }
        
        return false;
    }

    private function createUserSession($user) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'] ?? 'patient';
        $_SESSION['user_name'] = $user['nom'] ?? $user['nom_complet'] ?? 'Utilisateur';
    }

    private function redirectBasedOnRole($role = null) {
        $role = $role ?? ($_SESSION['user_role'] ?? 'patient');
        $redirects = [
            'patient' => '/Project/public/index.php?page=patient_dashboard',
            'medecin' => '/Project/public/index.php?page=medecin_dashboard',
            'secretaire' => '/Project/public/index.php?page=secretaire_dashboard',
            'admin' => '/Project/public/index.php?page=admin_dashboard'
        ];

        header('Location: ' . ($redirects[$role] ?? '/Project/public/index.php?page=login'));
        exit();
    }

    public function isLoggedIn() {
        // Vérifier la session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['user_id']);
    }
}

<?php
class AuthController {
    private $userModel;

    public function __construct() {
        require_once __DIR__ . '/../models/user.php';
        $this->userModel = new User();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            
            $user = $this->userModel->getByEmail($email);
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                
                $redirect = $_SESSION['redirect_after_login'] ?? '/';
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . $redirect);
                exit();
            } else {
                $error = 'Identifiants invalides';
            }
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nom' => $_POST['nom'],
                'prenom' => $_POST['prenom'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                'role' => 'patient' 
            ];
            
            if ($this->userModel->create($data)) {
                $user = $this->userModel->getByEmail($data['email']);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
                header('Location: /');
                exit();
            } 
            else {
                $error = 'Une erreur est survenue lors de l\'inscription';
            }
        }
        
        require_once __DIR__ . '/../views/auth/register.php';
    }

    public function logout() {
        session_destroy();
        header('Location: /connexion');
        exit();
    }

    public function forgotPassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $user = $this->userModel->getByEmail($email);
            
            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                $this->userModel->setResetToken($user['id'], $token, $expires);
                
                $this->sendResetEmail($user['email'], $token);
                
                $message = 'Un email de réinitialisation a été envoyé à votre adresse.';
            } else {
                $error = 'Aucun compte n\'est associé à cette adresse email.';
            }
        }
        
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }

    public function resetPassword($token) {
        $user = $this->userModel->getByResetToken($token);
        
        if (!$user) {
            $error = 'Le lien de réinitialisation est invalide ou a expiré.';
            require_once __DIR__ . '/../views/auth/reset-password-error.php';
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($password === $confirm_password) {
                $this->userModel->updatePassword($user['id'], password_hash($password, PASSWORD_DEFAULT));

                $this->userModel->clearResetToken($user['id']);
                

                $_SESSION['success_message'] = 'Votre mot de passe a été réinitialisé avec succès.';
                header('Location: /connexion');
                exit();
            } else {
                $error = 'Les mots de passe ne correspondent pas.';
            }
        }
        
        require_once __DIR__ . '/../views/auth/reset-password.php';
    }
}
?>
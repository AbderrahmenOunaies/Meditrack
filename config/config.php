<?php
class Config{
    //session
    const SESSION_NAME='MEDICAL_SESSION';
    const SESSION_LIFETIME=3600; //1 heure en secondes
    const SESSION_SECURE=false; //mere à true en production HTTPS
    const SESSION_HTTPONLY=true;
    //securite
    const CSRF_TOKEN_NAME='csrf_token';
    const CSRF_TOKEN_LIFETIME=1800; //30 minutes
    //tentatives de connexion
    const MAX_LOGIN_ATTEMPTS=5;
    const LOCKOUT_TIME=900; // 15 minutes en secondes
    //mots de passe
    const MIN_PASSWORD_LENGTH=8;
    const PASSWORD_RESET_EXPIRY=3600; // 1 heure
    //roles
    const ROLE_PATIENT='patient';
    const ROLE_MEDECIN='medecin';
    const ROLE_SECRETAIRE='secretaire';
    const ROLE_ADMIN='admin';
    //chemins
    const LOGIN_URL='/login.php';
    const DASHBOARD_URLS=[
        self::ROLE_PATIENT=>'/patient/dashboard.php',
        self::ROLE_MEDECIN=>'/medecin/dashboard.php',
        self::ROLE_SECRETAIRE=>'/secretaire/dashboard.php',
        self::ROLE_ADMIN=>'/admin/dashboard.php'
    ];
    //initialiser la session scurisée
    public static function initSession(){
        session_name(self::SESSION_NAME);
        session_set_cookie_params([
            'lifetime'=>self::SESSION_LIFETIME,
            'path'=>'/',
            'domain'=>$_SERVER['HTTP_HOST'] ?? 'localhost',
            'secure'=>self::SESSION_SECURE,
            'httponly'=>self::SESSION_HTTPONLY,
            'samesite'=>'Strict'
        ]);
        session_start();
        //régénérer l'ID de session périodiquement
        if(!isset($_SESSION['last_regeneration'])){
            session_regenerate_id(true);
            $_SESSION['last_regeneration']=time();
        }elseif(time() - $_SESSION['last_regeneration']>300){ // 5 minutes
            session_regenerate_id(true);
            $_SESSION['last_regeneration']=time();
        }
    }
    //générer token CSRF
    public static function generateCSRFToken(){
        if(empty($_SESSION[self::CSRF_TOKEN_NAME])){
            $_SESSION[self::CSRF_TOKEN_NAME]=bin2hex(random_bytes(32));
            $_SESSION[self::CSRF_TOKEN_NAME . '_time']=time();
        }
        return $_SESSION[self::CSRF_TOKEN_NAME];
    }
    // vérifier token CSRF
    public static function verifyCSRFToken($token){
        if(!isset($_SESSION[self::CSRF_TOKEN_NAME]) ||
        !isset($_SESSION[self::CSRF_TOKEN_NAME.'_time'])){
            return false;
        }
        $storedToken=$_SESSION[self::CSRF_TOKEN_NAME];
        $tokenTime=$_SESSION[self::CSRF_TOKEN_NAME.'_time'];
        // Token expiré ?
        if(time()-$tokenTime>self::CSRF_TOKEN_LIFETIME){
            unset($_SESSION[self::CSRF_TOKEN_NAME]);
            unset($_SESSION[self::CSRF_TOKEN_NAME.'_time']);
            return false;
        }
        //comparaison en temps constant pour éviter les attaques timing
        return hash_equals($storedToken, $token);
    }
    // nettoyer l'input
    public static function sanitize($input){
        if (is_array($input)){
            return array_map([self::class, 'sanitize'], $input);
        }
        $input=trim($input);
        $input=stripslashes($input);
        $input=htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        return $input;
    }
    //valider email
    public static function validateEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    //valider mot de passe
    public static function validatePassword($password){
        if (strlen($password) < self::MIN_PASSWORD_LENGTH){
            return false;
        }
        //au moins une lettre majuscule, une minuscule, un chiffre
        if(!preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)){
            return false;
        }
        return true;
    }
}
?>
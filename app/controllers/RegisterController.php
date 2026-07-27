<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../services/NotificationService.php';

class RegisterController {
    private $pdo;
    private NotificationService $notifier;

    public function __construct() {
        $db = Database::getInstance();
        $this->pdo = $db->getConnection();
        $this->notifier = new NotificationService($this->pdo);
    }

    public function showForm(array $errors = [], array $old = []) {
        $doctors = $this->getDoctorsForSecretaryApplication();
        require __DIR__ . '/../views/Auth/register.php';
    }

    public function register(){
        $errors = [];
        $old = [];
        // On vérifie que c'est bien un POST
        if ($_SERVER['REQUEST_METHOD']==='POST'){
            if(isset($_POST['nom'])){
                $nom=trim($_POST['nom']);
            }else{
                $nom='';
            }
            if(isset($_POST['prenom'])){
                $prenom=trim($_POST['prenom']);
            }else{
                $prenom='';
            }
            if(isset($_POST['email'])){
                $email=trim($_POST['email']);
            }else{
                $email='';
            }
            if(isset($_POST['motDePasse'])){
                $motDePasse=$_POST['motDePasse'];
            }else{
                $motDePasse='';
            }
            if(isset($_POST['role'])){
                $role=$_POST['role'];
            }else{
                $role='';
            }
            $old=[
                'nom'=>$nom,
                'prenom'=>$prenom,
                'email'=>$email,
                'role'=>$role,
                'telephone'=>$_POST['telephone'] ?? '',
                'medecin_id'=>$_POST['medecin_id'] ?? '',
                'experience'=>$_POST['experience'] ?? '',
                'motivation'=>$_POST['motivation'] ?? '',
                'specialite'=>$_POST['specialite'] ?? '',
                'numero_ordre'=>$_POST['numero_ordre'] ?? '',
                'pieces_justificatives'=>$_POST['pieces_justificatives'] ?? ''
            ];
            // Validation basique
            if(!$nom || !$prenom || !$email || !$motDePasse || !$role){
                $errors[]="Tous les champs obligatoires doivent être remplis.";
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $errors[]="Adresse e-mail invalide.";
            }
            if ($this->emailExists($email)) {
                $errors[] = "Cet email est déjà utilisé.";
            }
            //champs supplémentaires selon le rôle
            $champSupp=[];
            if($role==='patient'){
                $champSupp['date_naissance']=!empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
                $champSupp['adresse']=isset($_POST['adresse']) && trim($_POST['adresse'])!=='' ? trim($_POST['adresse']) : null;
                $champSupp['telephone']=isset($_POST['telephone']) && trim($_POST['telephone'])!=='' ? trim($_POST['telephone']) : null;
                $champSupp['assurance_maladie']=isset($_POST['assurance_maladie']) && trim($_POST['assurance_maladie'])!=='' ? trim($_POST['assurance_maladie']) : null;
            }
            elseif($role==='medecin'){
                $champSupp['specialite']=isset($_POST['specialite']) && trim($_POST['specialite'])!=='' ? trim($_POST['specialite']) : null;
                $champSupp['numero_ordre']=isset($_POST['numero_ordre']) && trim($_POST['numero_ordre'])!=='' ? trim($_POST['numero_ordre']) : null;
                $champSupp['pieces_justificatives']=isset($_POST['pieces_justificatives']) && trim($_POST['pieces_justificatives'])!=='' ? trim($_POST['pieces_justificatives']) : null;

                if (!$champSupp['specialite'] || !$champSupp['numero_ordre']) {
                    $errors[] = "La spécialité et le numéro d'ordre sont obligatoires.";
                }

                if ($this->tempDoctorEmailExists($email)) {
                    $errors[] = "Une candidature médecin avec cet email est déjà en attente.";
                }
            }elseif($role==='secretaire'){
                $champSupp['medecin_id'] = isset($_POST['medecin_id']) ? (int) $_POST['medecin_id'] : 0;
                $champSupp['telephone'] = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
                $champSupp['experience'] = isset($_POST['experience']) ? trim($_POST['experience']) : '';
                $champSupp['motivation'] = isset($_POST['motivation']) ? trim($_POST['motivation']) : '';

                if ($champSupp['medecin_id'] <= 0) {
                    $errors[] = "Veuillez sélectionner le médecin avec lequel vous souhaitez travailler.";
                }

                if ($this->tempSecretaryEmailExists($email)) {
                    $errors[] = "Une candidature avec cet email est déjà en attente.";
                }
            }
            //si erreurs, réafficher le formulaire
            if(!empty($errors)){
                return $this->showForm($errors, $old);
            }
            //hash mot de passe
            $motDePasseHash=password_hash($motDePasse, PASSWORD_DEFAULT);
            try{
                if ($role==='secretaire') {
                    $this->handleSecretaryApplication([
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'motDePasseHash' => $motDePasseHash,
                    ], $champSupp);
                    return;
                }

                if ($role==='medecin') {
                    $this->handleDoctorApplication([
                        'nom' => $nom,
                        'prenom' => $prenom,
                        'email' => $email,
                        'motDePasseHash' => $motDePasseHash,
                    ], $champSupp);
                    return;
                }
                $this->pdo->beginTransaction();
                //création User
                $stmt=$this->pdo->prepare("INSERT INTO users (nom, prenom, email, motDePasse, role)
                VALUES(:nom, :prenom, :email, :motDePasse, :role)");
                $stmt->execute([
                    ':nom'=>$nom,
                    ':prenom'=>$prenom,
                    ':email'=>$email,
                    ':motDePasse'=>$motDePasseHash,
                    ':role'=>$role
                ]);
                $userId=$this->pdo->lastInsertId();
                // Ajout dans la table de sous-rôle
                if($role==='patient'){
                    $stmt=$this->pdo->prepare("INSERT INTO patient(id, date_naissance, adresse, telephone, assurance_maladie)
                    VALUES(:id, :date_naissance, :adresse, :telephone, :assurance_maladie)");
                    $stmt->execute([
                        ':id'=>$userId,
                        ':date_naissance'=>$champSupp['date_naissance'],
                        ':adresse'=>$champSupp['adresse'],
                        ':telephone'=>$champSupp['telephone'],
                        ':assurance_maladie'=>$champSupp['assurance_maladie']
                    ]);
                }elseif($role==='medecin'){
                    $stmt=$this->pdo->prepare("INSERT INTO medecin(id, specialite, numero_ordre)
                    VALUES(:id, :specialite, :numero_ordre)");
                    $stmt->execute([
                        ':id'=>$userId,
                        ':specialite'=>$champSupp['specialite'],
                        ':numero_ordre'=>$champSupp['numero_ordre']
                    ]);
                }elseif($role==='admin'){
                    $stmt=$this->pdo->prepare("INSERT INTO admin (id)
                    VALUES(:id)");
                    $stmt->execute([':id'=>$userId]);
                }else{
                    throw new Exception("Rôle inconnu");
                }
                $this->pdo->commit();
                // Après inscription, rediriger vers la connexion avec un indicateur de succès
                header("Location: /Project/public/index.php?page=login&registered=1");
                exit;
            }catch(PDOException $e){
                $this->pdo->rollBack();
                if($e->getCode()==23000){
                    $errors[]="Cet email est déjà utilisé.";
                }else{
                    $errors[]="Erreur d'enregistrement : ".$e->getMessage();
                }
                return $this->showForm($errors, $old);
            }catch(Exception $e){
                $this->pdo->rollBack();
                $errors[]="Erreur : ".$e->getMessage();
                return $this->showForm($errors, $old);
            }
        }else{
            //si GET, afficher le formulaire
            return $this->showForm();
        }
    }

    private function getDoctorsForSecretaryApplication(): array {
        $sql = "
            SELECT m.id,
                   CONCAT(u.prenom, ' ', u.nom) AS full_name,
                   m.recrute_secretaire,
                   CASE WHEN s.id IS NULL THEN 0 ELSE 1 END AS has_secretary
            FROM medecin m
            JOIN users u ON u.id = m.id
            LEFT JOIN secretaire s ON s.medecin_id = m.id
            ORDER BY u.nom ASC, u.prenom ASC
        ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function handleSecretaryApplication(array $baseData, array $extra): void {
        $doctor = $this->fetchDoctorById($extra['medecin_id']);

        if (!$doctor) {
            $errors = ["Médecin sélectionné invalide."];
            $old = [
                'nom' => $baseData['nom'],
                'prenom' => $baseData['prenom'],
                'email' => $baseData['email'],
                'role' => 'secretaire',
                'telephone' => $extra['telephone'] ?? '',
                'medecin_id' => $extra['medecin_id'] ?? '',
                'experience' => $extra['experience'] ?? '',
                'motivation' => $extra['motivation'] ?? ''
            ];
            $this->showForm($errors, $old);
            return;
        }

        $hasSecretary = $this->doctorHasSecretary((int) $doctor['id']);
        $isRecruiting = (int) ($doctor['recrute_secretaire'] ?? 0) === 1;

        $status = 'pending';
        $message = "Merci pour votre candidature. Le médecin va consulter votre dossier et vous communiquera la réponse finale.";

        if ($hasSecretary || !$isRecruiting) {
            $status = $hasSecretary ? 'refusee' : 'en_attente';
            $message = "Nous sommes désolés de vous informer que ce médecin n'a pas demandé une secrétaire pour le moment.";
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO temp_secretaire (medecin_id, nom, prenom, email, telephone, experience, motivation, mot_de_passe, statut)
            VALUES (:medecin, :nom, :prenom, :email, :telephone, :experience, :motivation, :password, :statut)
        ");

        $stmt->execute([
            ':medecin' => $doctor['id'],
            ':nom' => $baseData['nom'],
            ':prenom' => $baseData['prenom'],
            ':email' => $baseData['email'],
            ':telephone' => $extra['telephone'] ?: null,
            ':experience' => $extra['experience'] ?: null,
            ':motivation' => $extra['motivation'] ?: null,
            ':password' => $baseData['motDePasseHash'],
            ':statut' => $status
        ]);

        $applicationId = (int) $this->pdo->lastInsertId();

        $this->createDoctorNotification(
            (int) $doctor['id'],
            $applicationId,
            $baseData['nom'],
            $baseData['prenom'],
            $status
        );

        $this->renderSecretaryResult($message, $status, $doctor, $baseData, $extra);
    }

    private function renderSecretaryResult(string $message, string $status, array $doctor, array $baseData, array $extra): void {
        $doctorName = trim(($doctor['full_name'] ?? '') !== '' ? $doctor['full_name'] : 'Docteur');
        require __DIR__ . '/../views/Auth/register_secretaire_result.php';
    }

    private function createDoctorNotification(int $doctorId, int $applicationId, string $nom, string $prenom, string $status): void {
        $statusLabels = [
            'pending' => "à valider",
            'en_attente' => "en attente (médecin non recruteur)",
            'refusee' => "non traitée (déjà pourvu)",
        ];

        $statusText = $statusLabels[$status] ?? $status;

        $stmt = $this->pdo->prepare("
            INSERT INTO notification (iduser, titre, contenu, type, lien)
            VALUES (:user, :titre, :contenu, :type, :lien)
        ");

        $titre = "Nouvelle candidature de secrétaire";
        $contenu = sprintf(
            'Candidature reçue de %s %s (%s). Consultez le dossier pour donner votre réponse.',
            $prenom,
            $nom,
            $statusText
        );

        $stmt->execute([
            ':user' => $doctorId,
            ':titre' => $titre,
            ':contenu' => $contenu,
            ':type' => 'systeme',
            ':lien' => '/Project/public/index.php?page=medecin_dashboard&tab=secretaires&application=' . $applicationId,
        ]);
    }

    private function fetchDoctorById(int $doctorId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT m.id,
                   CONCAT(u.prenom, ' ', u.nom) AS full_name,
                   m.recrute_secretaire
            FROM medecin m
            JOIN users u ON u.id = m.id
            WHERE m.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $doctorId]);

        $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

        return $doctor ?: null;
    }

    private function doctorHasSecretary(int $doctorId): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM secretaire WHERE medecin_id = :id');
        $stmt->execute([':id' => $doctorId]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function emailExists(string $email): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function tempSecretaryEmailExists(string $email): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM temp_secretaire WHERE email = :email AND statut IN ("pending", "en_attente")');
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function tempDoctorEmailExists(string $email): bool {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM temp_medecin WHERE email = :email AND statut = "pending"');
        $stmt->execute([':email' => $email]);

        return (int) $stmt->fetchColumn() > 0;
    }

    private function handleDoctorApplication(array $baseData, array $extra): void {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO temp_medecin (nom, prenom, email, mot_de_passe, specialite, numero_ordre, pieces_justificatives)
                VALUES (:nom, :prenom, :email, :password, :specialite, :numero, :pieces)
            ");
            $stmt->execute([
                ':nom' => $baseData['nom'],
                ':prenom' => $baseData['prenom'],
                ':email' => $baseData['email'],
                ':password' => $baseData['motDePasseHash'],
                ':specialite' => $extra['specialite'] ?? null,
                ':numero' => $extra['numero_ordre'] ?? null,
                ':pieces' => $extra['pieces_justificatives'] ?? null,
            ]);

            $applicationId = (int) $this->pdo->lastInsertId();
            $reference = sprintf('DOC-%05d', $applicationId);
            $message = "Votre candidature a été transmise à l'équipe d'administration. Vous recevrez un email après vérification de vos accréditations.";

            require __DIR__ . '/../views/Auth/register_medecin_result.php';
            exit;
        } catch (Throwable $e) {
            $errors = ["Impossible d'enregistrer votre candidature : " . $e->getMessage()];
            $old = [
                'nom' => $baseData['nom'],
                'prenom' => $baseData['prenom'],
                'email' => $baseData['email'],
                'role' => 'medecin',
                'specialite' => $extra['specialite'] ?? '',
                'numero_ordre' => $extra['numero_ordre'] ?? '',
                'pieces_justificatives' => $extra['pieces_justificatives'] ?? '',
            ];
            $this->showForm($errors, $old);
        }
    }
}
?>
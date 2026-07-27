<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediTrack - Tableau de bord Patient</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #9b59b6;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: var(--primary-color);
            color: white;
            height: 100vh;
            position: fixed;
            padding: 20px 0;
        }
        
        .sidebar-brand {
            padding: 0 20px 30px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        
        .menu-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .menu-item:hover, .menu-item.active {
            background-color: rgba(255,255,255,0.1);
            border-left: 4px solid var(--accent-color);
        }
        
        .menu-item i {
            width: 25px;
            margin-right: 10px;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 20px;
        }
        
        /* Header */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #3498db, #9b59b6);
            color: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .appointment-card {
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            padding: 15px;
            margin-top: 20px;
        }
        
        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Notifications Section */
        .notifications-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .notification-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        /* Profile Section */
        .profile-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: var(--accent-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        
        .profile-info p {
            margin-bottom: 5px;
        }
        
        .profile-info i {
            width: 20px;
            margin-right: 10px;
            color: var(--accent-color);
        }
        
        .btn-purple {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .btn-purple:hover {
            background-color: #8e44ad;
            color: white;
        }
        
        .badge-purple {
            background-color: #f0e6f5;
            color: var(--accent-color);
            padding: 5px 10px;
            border-radius: 15px;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <h4>MediTrack</h4>
            <small class="text-muted">Système de Gestion Médicale</small>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active">
                <a href="#" class="text-white text-decoration-none">
                    <i class="fas fa-tachometer-alt"></i> Tableau de bord
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="text-white text-decoration-none">
                    <i class="fas fa-users"></i> Patients
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="text-white text-decoration-none">
                    <i class="far fa-calendar-alt"></i> Rendez-vous
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="text-white text-decoration-none">
                    <i class="fas fa-notes-medical"></i> Consultations
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="text-white text-decoration-none">
                    <i class="far fa-file-alt"></i> Documents
                </a>
            </li>
            <li class="menu-item">
                <a href="#" class="text-white text-decoration-none">
                    <i class="fas fa-cog"></i> Paramètres
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h4>Cabinet MediTrack, Tunis</h4>
            <div class="user-profile">
                <div class="user-avatar">A</div>
                <div>
                    <div class="fw-bold">Ahmed Ben Ali</div>
                    <small class="text-muted">Patient</small>
                </div>
            </div>
        </div>

        <!-- Welcome Banner -->
        <div class="welcome-banner">
            <div class="row">
                <div class="col-md-8">
                    <h2>Bienvenue, Ahmed</h2>
                    <p class="mb-4">Votre santé, notre priorité</p>
                    <p><i class="far fa-calendar-alt"></i> Samedi, 16 Novembre 2025</p>
                </div>
                <div class="col-md-4">
                    <div class="appointment-card">
                        <small>Prochain RDV</small>
                        <h4>20 Nov à 10:30</h4>
                        <p class="mb-1">Dr. Salma Mansour</p>
                        <small>Médecine Générale</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">2</div>
                    <div class="stats-label">RDV à venir</div>
                    <small class="text-muted">Prochain: 20 Nov</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">2</div>
                    <div class="stats-label">Ordonnances actives</div>
                    <small class="text-muted">À renouveler bientôt</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">8</div>
                    <div class="stats-label">Consultations</div>
                    <small class="text-muted">Cette année</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-number">A+</div>
                    <div class="stats-label">Groupe sanguin</div>
                    <small class="text-muted">Patient depuis Janvier 2023</small>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Notifications Section -->
            <div class="col-md-8">
                <div class="notifications-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Notifications et Rappels <span class="badge bg-primary">3 nouvelles</span></h5>
                        <a href="#" class="text-decoration-none">Voir tout</a>
                    </div>
                    
                    <div class="notification-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <i class="far fa-bell text-warning me-2"></i>
                                <strong>Rappel: RDV avec Dr. Mansour</strong>
                            </div>
                            <small class="text-muted">Aujourd'hui, 08:30</small>
                        </div>
                        <p class="mb-0 mt-1">Votre consultation est prévue pour demain à 10h30</p>
                    </div>
                    
                    <div class="notification-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <i class="far fa-file-medical text-primary me-2"></i>
                                <strong>Ordonnance à renouveler</strong>
                            </div>
                            <small class="text-muted">Hier, 14:15</small>
                        </div>
                        <p class="mb-0 mt-1">Votre ordonnance expire dans 5 jours</p>
                    </div>
                    
                    <div class="notification-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <i class="fas fa-vial text-info me-2"></i>
                                <strong>Résultats d'analyses disponibles</strong>
                            </div>
                            <small class="text-muted">15 Nov, 10:00</small>
                        </div>
                        <p class="mb-0 mt-1">Vos résultats de prise de sang sont disponibles</p>
                    </div>
                </div>
                
                <!-- Upcoming Appointments -->
                <div class="notifications-section mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Prochains rendez-vous</h5>
                        <button class="btn btn-sm btn-purple">+ Prendre RDV</button>
                    </div>
                    
                    <div class="appointment-card p-3 mb-3">
                        <div class="row">
                            <div class="col-2 text-center">
                                <div class="text-muted">NOV</div>
                                <div class="h4 mb-0">20</div>
                                <small>10:30</small>
                            </div>
                            <div class="col-7">
                                <h6 class="mb-1">Dr. Salma Mansour</h6>
                                <p class="mb-1 text-muted">Consultation de suivi</p>
                                <small><i class="fas fa-map-marker-alt"></i> Cabinet MediTrack, Tunis</small>
                            </div>
                            <div class="col-3 text-end">
                                <span class="badge bg-success">Confirmé</span>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-outline-secondary">Modifier</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <a href="#" class="text-decoration-none">Voir tous mes rendez-vous <i class="fas fa-chevron-right"></i></a>
                </div>
            </div>
            
            <!-- Profile Section -->
            <div class="col-md-4">
                <div class="profile-section">
                    <div class="profile-avatar">A</div>
                    <h5 class="text-center mb-4">Ahmed Ben Ali</h5>
                    
                    <div class="profile-info">
                        <p><i class="fas fa-user"></i> 45 ans • Homme</p>
                        <p><i class="fas fa-envelope"></i> ahmed.benali@email.tn</p>
                        <p><i class="fas fa-phone"></i> (+216) 98 765 432</p>
                        <p><i class="fas fa-map-marker-alt"></i> Rue de la République, Tunis</p>
                        <p><i class="fas fa-tint"></i> <strong>Groupe sanguin:</strong> A+</p>
                        
                        <div class="d-grid mt-4">
                            <button class="btn btn-purple">Modifier mon profil</button>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <h6>Ordonnances actives <span class="badge bg-primary">2</span></h6>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title">Ramipril 10mg</h6>
                            <p class="card-text">1 comprimé/jour</p>
                            <p class="card-text text-muted small">Du 15/11/2025 au 15/02/2026</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge-purple">85 j. restants</span>
                                <button class="btn btn-sm btn-outline-primary">Télécharger</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mt-3">
                        <div class="card-body">
                            <h6 class="card-title">Amlodipine 5mg</h6>
                            <p class="card-text">1 comprimé/jour</p>
                            <p class="card-text text-muted small">Du 15/11/2025 au 15/02/2026</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge-purple">85 j. restants</span>
                                <button class="btn btn-sm btn-outline-primary">Télécharger</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="#" class="text-decoration-none">Voir toutes les ordonnances</a>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="profile-section mt-4">
                    <h6 class="mb-3">Actions rapides</h6>
                    
                    <button class="btn btn-outline-primary w-100 mb-2">
                        <i class="far fa-calendar-plus me-2"></i> Prendre rendez-vous
                    </button>
                    <button class="btn btn-outline-primary w-100 mb-2">
                        <i class="far fa-comment-dots me-2"></i> Contacter un médecin
                    </button>
                    <button class="btn btn-outline-primary w-100">
                        <i class="far fa-folder-open me-2"></i> Voir mon dossier
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

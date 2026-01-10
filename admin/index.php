<?php
require_once 'auth.php';
checkAdmin();

$currentUser = getCurrentUser();
$isSuperAdmin = $currentUser['role'] === ROLE_SUPERADMIN;
$canManageCourses = hasPermission('manage_courses');
$canManageUsers = hasPermission('manage_users');

// Statistiques
$stats = [];
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$stats['courses'] = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$stats['questions'] = $pdo->query("SELECT COUNT(*) FROM questions")->fetchColumn();
$stats['progressions'] = $pdo->query("SELECT COUNT(*) FROM progression WHERE is_completed = 1")->fetchColumn();

// Message d'erreur si pas de permission
$errorMsg = isset($_GET['error']) && $_GET['error'] === 'no_permission' ? "Vous n'avez pas accès à cette section." : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - CyberSens</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin-style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="bg-grid"></div>

    <div class="app-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <div class="logo">
                <div class="logo-icon">
                    <i data-lucide="shield-check"></i>
                </div>
                <span class="logo-text">CyberSens</span>

            </div>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-item active">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                
                <?php if($canManageCourses): ?>
                <a href="cours.php" class="nav-item">
                    <i data-lucide="book-open"></i>
                    <span>Gestion Cours</span>
                </a>
                <a href="questions.php" class="nav-item">
                    <i data-lucide="help-circle"></i>
                    <span>Banque Questions</span>
                </a>
                <?php endif; ?>
                
                <?php if($canManageUsers): ?>
                <a href="users.php" class="nav-item">
                    <i data-lucide="users"></i>
                    <span>Utilisateurs</span>
                </a>
                <?php endif; ?>

                <div class="nav-divider"></div>

                <a href="../index.html" class="nav-item">
                    <i data-lucide="arrow-left"></i>
                    <span>Retour au site</span>
                </a>
            </div>
            
            <!-- User Profile Section -->
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                     <?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                    <div class="sidebar-user-role"><?php echo getRoleName($currentUser['role']); ?></div>
                </div>
            </div>
        </nav>

        <!-- Main Content Area -->
        <main class="main-content">
            <div class="page-header">
                <h1>Tableau de bord</h1>
                <p class="subtitle">Vue d'ensemble de la plateforme et statistiques.</p>
            </div>

            <?php if($errorMsg): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger-light); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="alert-circle"></i>
                <?php echo $errorMsg; ?>
            </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card-admin">
                    <div class="icon-box bg-primary-subtle">
                        <i data-lucide="users"></i>
                    </div>
                    <h3><?php echo $stats['users']; ?></h3>
                    <p>UTILISATEURS</p>
                </div>
                
                <div class="stat-card-admin">
                    <div class="icon-box bg-success-subtle">
                        <i data-lucide="book"></i>
                    </div>
                    <h3><?php echo $stats['courses']; ?></h3>
                    <p>COURS PUBLIÉS</p>
                </div>

                <div class="stat-card-admin">
                    <div class="icon-box bg-warning-subtle">
                        <i data-lucide="help-circle"></i>
                    </div>
                    <h3><?php echo $stats['questions']; ?></h3>
                    <p>QUESTIONS</p>
                </div>

                <div class="stat-card-admin">
                    <div class="icon-box bg-info-subtle">
                        <i data-lucide="award"></i>
                    </div>
                    <h3><?php echo $stats['progressions']; ?></h3>
                    <p>MODULES TERMINÉS</p>
                </div>
            </div>

            <div class="bento-grid" style="margin-top: 2rem;">
                <!-- Actions Rapides -->
                <div class="card" style="grid-column: span 1;">
                    <h3 style="margin-bottom: 1.5rem;">Actions Rapides</h3>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php if($canManageCourses): ?>
                        <a href="add_cours.php" class="btn btn-outline" style="justify-content: flex-start;">
                            <i data-lucide="plus-circle"></i> Nouveau Cours
                        </a>
                        <a href="add_question.php" class="btn btn-outline" style="justify-content: flex-start;">
                            <i data-lucide="plus-circle"></i> Nouvelle Question
                        </a>
                        <?php endif; ?>
                        
                        <?php if($canManageUsers): ?>
                        <a href="users.php" class="btn btn-outline" style="justify-content: flex-start;">
                            <i data-lucide="user-cog"></i> Gérer Utilisateurs
                        </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Courses -->
                <div class="card" style="grid-column: span 2;">
                    <h3 style="margin-bottom: 1.5rem;">Derniers Cours Ajoutés</h3>
                    <?php
                    $recent = $pdo->query("SELECT id, title, difficulty, created_at FROM courses ORDER BY created_at DESC LIMIT 5")->fetchAll();
                    if (count($recent) > 0):
                    ?>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Difficulté</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($recent as $r): ?>
                            <tr>
                                <td style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($r['title']); ?></td>
                                <td>
                                    <?php 
                                    $diffVal = $r['difficulty'];
                                    $diffClass = 'facile';
                                    if (stripos($diffVal, 'Interm') !== false) $diffClass = 'moyen';
                                    if (stripos($diffVal, 'Difficile') !== false) $diffClass = 'difficile';
                                    ?>
                                    <span class="difficulty-badge <?php echo $diffClass; ?>">
                                        <?php echo $diffVal; ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo date('d/m/Y', strtotime($r['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <p class="text-muted">Aucun cours pour le moment.</p>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

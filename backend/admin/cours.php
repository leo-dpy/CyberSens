<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

// Suppression d'un module
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: cours.php?msg=deleted");
    exit;
}

// Mise à jour de l'ordre via AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_order') {
    header('Content-Type: application/json');
    $orders = json_decode($_POST['orders'], true);
    
    if ($orders) {
        foreach ($orders as $item) {
            $stmt = $pdo->prepare("UPDATE modules SET display_order = ? WHERE id = ?");
            $stmt->execute([(int)$item['order'], (int)$item['id']]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    exit;
}

// Récupérer tous les modules triés par ordre d'affichage
$modules = $pdo->query("SELECT m.*, 
    (SELECT COUNT(*) FROM submodules WHERE module_id = m.id) as nb_submodules,
    (SELECT COUNT(*) FROM questions WHERE module_id = m.id) as nb_questions 
    FROM modules m ORDER BY m.display_order ASC, m.id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Modules - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body>
    <div class="bg-grid"></div>

    <div class="app-container">
        <!-- Barre latérale -->
        <nav class="sidebar">
            <div class="logo">
                <span class="logo-text">CyberSens</span>
            </div>
            
            <div class="nav-menu">
                <a href="index.php" class="nav-item">
                    <i data-lucide="layout-dashboard"></i>
                    <span>Dashboard</span>
                </a>
                
                <?php if(hasPermission('manage_courses')): ?>
                <a href="cours.php" class="nav-item active">
                    <i data-lucide="book-open"></i>
                    <span>Gestion Modules</span>
                </a>
                <a href="questions.php" class="nav-item">
                    <i data-lucide="help-circle"></i>
                    <span>Banque Questions</span>
                </a>
                <?php endif; ?>

                <?php if(hasPermission('manage_content')): ?>
                <a href="news.php" class="nav-item">
                    <i data-lucide="rss"></i>
                    <span>Actualités</span>
                </a>
                <?php endif; ?>

                <?php if(hasPermission('manage_users')): ?>
                <a href="users.php" class="nav-item">
                    <i data-lucide="users"></i>
                    <span>Utilisateurs</span>
                </a>
                <?php endif; ?>

                <div class="nav-divider"></div>

                <a href="../../index.html" class="nav-item">
                    <i data-lucide="arrow-left"></i>
                    <span>Retour au site</span>
                </a>
            </div>
            
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

        <!-- Contenu principal -->
        <main class="main-content">
            <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Modules</h1>
                    <p class="subtitle">Créez et organisez les modules de formation.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" id="toggleOrderMode">
                        <i data-lucide="arrow-up-down"></i> Réorganiser
                    </button>
                    <a href="add_cours.php" class="btn btn-primary">
                        <i data-lucide="plus-circle"></i> Nouveau module
                    </a>
                </div>
            </div>

            <!-- Mode réorganisation -->
            <div id="orderModePanel" class="card" style="display: none; margin-bottom: 2rem; border-color: var(--accent-primary);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="color: var(--accent-primary); margin-bottom: 0.5rem;"><i data-lucide="arrow-up-down" style="display: inline; width: 20px;"></i> Mode Réorganisation</h4>
                        <p class="text-muted" style="margin: 0;">Glissez-déposez les modules pour définir l'ordre.</p>
                    </div>
                    <button class="btn btn-success" id="saveOrder" disabled>
                        <i data-lucide="check"></i> Sauvegarder l'ordre
                    </button>
                </div>
            </div>

            <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                <?php 
                if($_GET['msg'] == 'created') echo 'Module créé avec succès !';
                if($_GET['msg'] == 'updated') echo 'Module mis à jour !';
                if($_GET['msg'] == 'deleted') echo 'Module supprimé !';
                ?>
            </div>
            <?php endif; ?>

            <div class="admin-table-container">
                <?php if(count($modules) > 0): ?>
                <table class="admin-table has-hidden-col">
                    <thead>
                        <tr>
                            <th class="order-handle-col" style="display: none; width: 50px;"></th>
                            <th>Ordre</th>
                            <th>Titre</th>
                            <th>Difficulté</th>
                            <th>Contenu</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="coursesTableBody">
                        <?php $order = 1; foreach($modules as $m): ?>
                        <tr data-id="<?php echo $m['id']; ?>">
                            <td class="order-handle-col" style="display: none; text-align: center;">
                                <i data-lucide="grip-vertical" style="cursor: grab; color: var(--accent-primary);"></i>
                            </td>
                            <td>
                                <span class="badge order-badge" style="background: var(--bg-tertiary); color: var(--text-primary); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $order++; ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($m['title']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;"><?php echo htmlspecialchars($m['description']); ?></div>
                            </td>
                            <td>
                                <?php 
                                $diffCss = 'facile';
                                if($m['difficulty'] == 'Intermédiaire' || $m['difficulty'] == 'Moyen') $diffCss = 'moyen';
                                if($m['difficulty'] == 'Difficile') $diffCss = 'difficile';
                                ?>
                                <span class="difficulty-badge <?php echo $diffCss; ?>">
                                    <?php echo $m['difficulty']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary); margin-right: 0.5rem;">
                                    <?php echo $m['nb_submodules']; ?> sous-modules
                                </span>
                                <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary);">
                                    <?php echo $m['nb_questions']; ?> questions
                                </span>
                            </td>
                            <td>
                                <?php if(empty($m['is_published']) || $m['is_published'] == 0): ?>
                                    <span class="badge" style="background: var(--bg-tertiary); color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.6rem;">
                                        <i data-lucide="eye-off" style="width: 14px; height: 14px;"></i> Brouillon
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-success" style="display: inline-flex; align-items: center; gap: 0.4rem;">
                                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Publié
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?php echo date('d/m/Y', strtotime($m['created_at'])); ?></td>
                            <td class="text-end actions-col">
                                <div class="admin-actions">
                                    <a href="edit_cours.php?id=<?php echo $m['id']; ?>" class="btn-icon edit" title="Modifier">
                                        <i data-lucide="pencil"></i>
                                    </a>
                                    <a href="questions.php?module_id=<?php echo $m['id']; ?>" class="btn-icon manage" title="Gérer les questions">
                                        <i data-lucide="help-circle"></i>
                                    </a>
                                    <a href="cours.php?delete=<?php echo $m['id']; ?>" class="btn-icon delete" title="Supprimer" onclick="return confirmAction(event, 'Êtes-vous sûr de vouloir supprimer ce module et tous ses sous-modules ?');">
                                        <i data-lucide="trash-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="padding: 4rem; text-align: center;">
                    <i data-lucide="book-open" style="width: 64px; height: 64px; color: var(--text-muted); opacity: 0.5; margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">Aucun module</h3>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">Commencez par créer votre premier module de formation.</p>
                    <a href="add_cours.php" class="btn btn-primary">
                        <i data-lucide="plus-circle"></i> Créer un module
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../frontend/js/admin/shared.js"></script>
    <script src="../../frontend/js/admin/cours.js"></script>
    <link rel="stylesheet" href="../../frontend/css/admin/cours.css">
</body>
</html>

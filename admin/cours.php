<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

// Suppression d'un cours
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM courses WHERE id = ?");
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
            $stmt = $pdo->prepare("UPDATE courses SET display_order = ? WHERE id = ?");
            $stmt->execute([(int)$item['order'], (int)$item['id']]);
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
    }
    exit;
}

// Récupérer tous les cours triés par ordre d'affichage
$cours = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM questions WHERE course_id = c.id) as nb_questions FROM courses c ORDER BY c.display_order ASC, c.id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Cours - Admin CyberSens</title>
    <link rel="stylesheet" href="../styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="admin-style.css?v=<?php echo time(); ?>">
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
                <div class="logo-icon">
                    <i data-lucide="shield-check"></i>
                </div>
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
                    <span>Gestion Cours</span>
                </a>
                <a href="questions.php" class="nav-item">
                    <i data-lucide="help-circle"></i>
                    <span>Banque Questions</span>
                </a>
                <?php endif; ?>
                
                <?php if(hasPermission('manage_users')): ?>
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
                    <h1>Cours</h1>
                    <p class="subtitle">Créez et organisez les modules de formation.</p>
                </div>
                <div style="display: flex; gap: 1rem;">
                    <button class="btn btn-outline" id="toggleOrderMode">
                        <i data-lucide="arrow-up-down"></i> Réorganiser
                    </button>
                    <a href="add_cours.php" class="btn btn-primary">
                        <i data-lucide="plus-circle"></i> Nouveau cours
                    </a>
                </div>
            </div>

            <!-- Mode réorganisation -->
            <div id="orderModePanel" class="card" style="display: none; margin-bottom: 2rem; border-color: var(--accent-primary);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="color: var(--accent-primary); margin-bottom: 0.5rem;"><i data-lucide="arrow-up-down" style="display: inline; width: 20px;"></i> Mode Réorganisation</h4>
                        <p class="text-muted" style="margin: 0;">Glissez-déposez les cours pour définir l'ordre.</p>
                    </div>
                    <button class="btn btn-success" id="saveOrder" disabled>
                        <i data-lucide="check"></i> Sauvegarder l'ordre
                    </button>
                </div>
            </div>

            <?php if(isset($_GET['msg'])): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="check-circle"></i>
                <?php 
                if($_GET['msg'] == 'created') echo 'Cours créé avec succès !';
                if($_GET['msg'] == 'updated') echo 'Cours mis à jour !';
                if($_GET['msg'] == 'deleted') echo 'Cours supprimé !';
                ?>
            </div>
            <?php endif; ?>

            <div class="admin-table-container">
                <?php if(count($cours) > 0): ?>
                <table class="admin-table has-hidden-col">
                    <thead>
                        <tr>
                            <th class="order-handle-col" style="display: none; width: 50px;"></th>
                            <th>Ordre</th>
                            <th>Titre</th>
                            <th>Difficulté</th>
                            <th>Contenu</th>
                            <th>Visibilité</th>
                            <th>Date</th>
                            <th class="text-end actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="coursesTableBody">
                        <?php $order = 1; foreach($cours as $c): ?>
                        <tr data-id="<?php echo $c['id']; ?>">
                            <td class="order-handle-col" style="display: none; text-align: center;">
                                <i data-lucide="grip-vertical" style="cursor: grab; color: var(--accent-primary);"></i>
                            </td>
                            <td>
                                <span class="badge order-badge" style="background: var(--bg-tertiary); color: var(--text-primary); border-radius: 50%; width: 24px; height: 24px; display: inline-flex; align-items: center; justify-content: center;"><?php echo $order++; ?></span>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($c['title']); ?></div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;"><?php echo htmlspecialchars($c['description']); ?></div>
                            </td>
                            <td>
                                <?php 
                                $diffClass = $c['difficulty'] == 'Facile' ? 'success' : ($c['difficulty'] == 'Intermédiaire' ? 'warning' : 'danger');
                                $bg = $diffClass == 'success' ? 'rgba(16, 185, 129, 0.15)' : ($diffClass == 'warning' ? 'rgba(245, 158, 11, 0.15)' : 'rgba(239, 68, 68, 0.15)');
                                $color = $diffClass == 'success' ? 'var(--success)' : ($diffClass == 'warning' ? 'var(--warning)' : 'var(--danger)');
                                ?>
                                <span class="badge" style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>;">
                                    <?php echo $c['difficulty']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary);">
                                    <?php echo $c['nb_questions']; ?> questions
                                </span>
                            </td>
                            <td>
                                <?php if(!empty($c['is_hidden']) && $c['is_hidden'] == 1): ?>
                                    <span class="badge" style="background: var(--bg-tertiary); color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.6rem;">
                                        <i data-lucide="eye-off" style="width: 14px; height: 14px;"></i> Caché
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.1); color: var(--success); display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.6rem;">
                                        <i data-lucide="eye" style="width: 14px; height: 14px;"></i> Visible
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted"><?php echo date('d/m/Y', strtotime($c['created_at'])); ?></td>
                            <td class="text-end actions-col">
                                <div class="admin-actions">
                                    <a href="edit_cours.php?id=<?php echo $c['id']; ?>" class="btn-icon edit" title="Modifier">
                                        <i data-lucide="pencil"></i>
                                    </a>
                                    <a href="questions.php?course_id=<?php echo $c['id']; ?>" class="btn-icon" style="color: var(--accent-primary);" title="Gérer les questions">
                                        <i data-lucide="help-circle"></i>
                                    </a>
                                    <a href="cours.php?delete=<?php echo $c['id']; ?>" class="btn-icon delete" title="Supprimer" onclick="return confirmAction(event, 'Êtes-vous sûr de vouloir supprimer ce cours et toutes ses questions ?');">
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
                    <h3 style="margin-bottom: 0.5rem;">Aucun cours</h3>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">Commencez par créer votre premier module de formation.</p>
                    <a href="add_cours.php" class="btn btn-primary">
                        <i data-lucide="plus-circle"></i> Créer un cours
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../js/admin/shared.js"></script>
    <script src="../js/admin/cours.js"></script>
    <link rel="stylesheet" href="../css/admin/cours.css">
</body>
</html>

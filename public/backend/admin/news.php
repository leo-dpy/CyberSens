<?php
require_once 'auth.php';
// Créateur, admin et superadmin peuvent gérer les news
checkContentAccess();

$currentUser = getCurrentUser();

// Suppression d'une actualité
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM news WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: news.php?msg=deleted");
    exit;
}

// Récupérer toutes les actualités triées par date
try {
    $newsList = $pdo->query("SELECT * FROM news ORDER BY event_date DESC, created_at DESC")->fetchAll();
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Actualités - Admin CyberSens</title>
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link rel="stylesheet" href="../../frontend/styles.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../frontend/css/admin/news.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
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
                <a href="cours.php" class="nav-item">
                    <i data-lucide="book-open"></i>
                    <span>Gestion Modules</span>
                </a>
                <a href="questions.php" class="nav-item">
                    <i data-lucide="help-circle"></i>
                    <span>Banque Questions</span>
                </a>
                <?php endif; ?>

                <a href="news.php" class="nav-item active">
                    <i data-lucide="rss"></i>
                    <span>Actualités</span>
                </a>
                
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

        <!-- Contenu Principal -->
        <main class="main-content">
            <div class="content-wrapper">
                <div class="page-header-content" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h1>Actualités & Hacks</h1>
                        <p class="text-muted">Gérez les incidents et actualités affichés sur le site.</p>
                    </div>
                    <div class="top-actions">
                        <a href="add_news.php" class="btn btn-primary">
                            <i data-lucide="plus"></i> Nouvelle Actu
                        </a>
                    </div>
                </div>

                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i> Actualité supprimée avec succès.
                </div>
                <?php endif; ?>

                 <?php if (isset($_GET['msg']) && $_GET['msg'] === 'created'): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i> Actualité créée avec succès.
                </div>
                <?php endif; ?>
                
                <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle"></i> Actualité mise à jour avec succès.
                </div>
                <?php endif; ?>

                <div class="card-grid">
                    <?php if (count($newsList) > 0): ?>
                        <div class="admin-table-container">
                            <table class="admin-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Source / Type</th>
                                        <th>Titre</th>
                                        <th>Description</th>
                                        <th style="text-align: right;">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($newsList as $item): ?>
                                    <tr>
                                        <td style="white-space: nowrap; color: var(--text-muted);">
                                            <?php echo htmlspecialchars($item['event_date']); ?>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">
                                                <?php echo htmlspecialchars($item['source']); ?>
                                            </span>
                                        </td>
                                        <td style="font-weight: 500;">
                                            <?php echo htmlspecialchars($item['title']); ?>
                                        </td>
                                        <td style="font-size: 0.9em; opacity: 0.8;">
                                            <?php echo htmlspecialchars($item['description']); ?>
                                        </td>
                                        <td class="text-end actions-col">
                                            <div class="admin-actions">
                                                <a href="edit_news.php?id=<?php echo $item['id']; ?>" class="btn-icon edit" title="Modifier">
                                                    <i data-lucide="edit-2"></i>
                                                </a>
                                                <a href="news.php?delete=<?php echo $item['id']; ?>" class="btn-icon delete" title="Supprimer" onclick="return confirmAction(event, 'Voulez-vous vraiment supprimer cet incident ?');">
                                                    <i data-lucide="trash-2"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i data-lucide="rss" class="empty-icon"></i>
                            <h3>Aucune actualité</h3>
                            <p>Commencez par ajouter un incident ou une actualité.</p>
                            <a href="add_news.php" class="btn btn-primary mt-3">Ajouter</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="../../frontend/js/admin/shared.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>

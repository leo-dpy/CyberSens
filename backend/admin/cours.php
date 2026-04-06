<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

// Suppression d'un module
if (isset($_GET['delete_module']) && is_numeric($_GET['delete_module'])) {
    $id = (int)$_GET['delete_module'];
    // Supprimer les sous-modules d'abord
    $pdo->prepare("DELETE FROM submodules WHERE module_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM questions WHERE module_id = ?")->execute([$id]);
    $pdo->prepare("DELETE FROM modules WHERE id = ?")->execute([$id]);
    header("Location: cours.php?msg=deleted");
    exit;
}

// Suppression d'un sous-module
if (isset($_GET['delete_submodule']) && is_numeric($_GET['delete_submodule'])) {
    $id = (int)$_GET['delete_submodule'];
    $pdo->prepare("DELETE FROM submodules WHERE id = ?")->execute([$id]);
    header("Location: cours.php?msg=sub_deleted");
    exit;
}

// Récupérer les modules avec stats
$modules = $pdo->query("
    SELECT m.*, 
           (SELECT COUNT(*) FROM submodules WHERE module_id = m.id) as nb_submodules,
           (SELECT COUNT(*) FROM questions WHERE module_id = m.id) as nb_questions
    FROM modules m 
    ORDER BY m.display_order, m.id
")->fetchAll();

// Récupérer tous les sous-modules groupés par module
$allSubmodules = [];
$submodulesQuery = $pdo->query("SELECT * FROM submodules ORDER BY display_order, id");
foreach ($submodulesQuery as $sub) {
    $allSubmodules[$sub['module_id']][] = $sub;
}

// Icônes
$icons = [
    'shield' => '🛡️', 'lock' => '🔒', 'key' => '🔑', 'bug' => '🐛', 'wifi' => '📶',
    'mail' => '📧', 'globe' => '🌐', 'smartphone' => '📱', 'database' => '🗄️',
    'cloud' => '☁️', 'code' => '💻', 'terminal' => '⌨️', 'alert-triangle' => '⚠️',
    'eye' => '👁️', 'users' => '👥', 'file-text' => '📄', 'book' => '📖', 'bookmark' => '🔖'
];

// Thèmes
$themes = [
    'blue' => '#3b82f6', 'purple' => '#8b5cf6', 'green' => '#10b981',
    'red' => '#ef4444', 'orange' => '#f59e0b', 'cyan' => '#06b6d4', 'pink' => '#ec4899'
];

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Modules - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .page-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: flex-start;
            margin-bottom: 2rem; 
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .page-header h1 { 
            margin: 0 0 0.5rem 0; 
            font-size: 1.75rem;
            font-weight: 700;
            color: #fff;
        }
        .page-header .subtitle { 
            color: #666; 
            margin: 0;
            font-size: 0.95rem;
        }
        .page-header .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: #fff;
            color: #000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .page-header .btn-primary:hover {
            background: #ccc;
            transform: translateY(-1px);
        }
        .page-header .btn-primary i {
            width: 18px;
            height: 18px;
        }
        
        .modules-grid { display: flex; flex-direction: column; gap: 1rem; margin-top: 1.5rem; }
        
        .module-card { 
            background: rgba(10, 10, 12, 0.9); 
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px; 
            overflow: hidden;
            transition: all 0.3s ease;
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }
        .module-card:hover { 
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            transform: translateY(-2px);
        }
        
        .module-header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 1.25rem 1.5rem; 
            cursor: pointer;
            gap: 1rem;
        }
        .module-header:hover { background: rgba(255,255,255,0.02); }
        
        .module-info { display: flex; align-items: center; gap: 1.25rem; flex: 1; min-width: 0; }
        
        .module-icon-large { 
            width: 52px; 
            height: 52px; 
            border-radius: 12px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .module-details { min-width: 0; flex: 1; }
        .module-details h3 { margin: 0 0 0.35rem 0; font-size: 1.1rem; color: #fff; font-weight: 600; }
        .module-details p { margin: 0; font-size: 0.85rem; color: #999; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        
        .module-stats { 
            display: flex; 
            gap: 2rem; 
            margin: 0 1.5rem;
            flex-shrink: 0;
        }
        .stat-item { text-align: center; min-width: 70px; }
        .stat-value { font-size: 1.35rem; font-weight: 700; color: #fff; }
        .stat-label { font-size: 0.65rem; text-transform: uppercase; color: #666; letter-spacing: 0.5px; margin-top: 0.2rem; }
        
        .module-actions { 
            display: flex; 
            gap: 0.5rem; 
            margin-left: 1rem;
            flex-shrink: 0;
        }
        
        .expand-icon { transition: transform 0.3s; color: #666; margin-left: 0.5rem; }
        .module-card.expanded .expand-icon { transform: rotate(180deg); }
        
        .module-content { 
            display: none; 
            border-top: 1px solid rgba(255,255,255,0.1); 
            padding: 1.5rem; 
            background: rgba(0,0,0,0.4); 
        }
        .module-card.expanded .module-content { display: block; }
        
        .submodules-section h4 { 
            margin: 0 0 1.25rem 0; 
            display: flex; 
            align-items: center; 
            gap: 0.5rem; 
            color: #ccc; 
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        .submodules-list { display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 1.25rem; }
        
        .submodule-item { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 1rem 1.25rem; 
            background: rgba(10, 10, 12, 0.8); 
            border: 1px solid rgba(255, 255, 255, 0.15); 
            border-radius: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .submodule-item:hover { 
            border-color: rgba(255, 255, 255, 0.3); 
            background: rgba(20, 20, 26, 0.9);
            transform: translateY(-1px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        
        .submodule-info { display: flex; align-items: center; gap: 1rem; }
        .submodule-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
        .submodule-title { font-weight: 500; color: #fff; }
        .submodule-meta { font-size: 0.8rem; color: #666; margin-top: 0.2rem; }
        
        .empty-state { text-align: center; padding: 2.5rem; color: #666; }
        
        .btn-add-submodule { 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 0.5rem; 
            padding: 1rem; 
            border: 2px dashed rgba(255,255,255,0.15); 
            border-radius: 12px; 
            color: #666; 
            background: transparent; 
            text-decoration: none; 
            transition: all 0.2s; 
            width: 100%;
            font-weight: 500;
        }
        .btn-add-submodule:hover { 
            border-color: rgba(255,255,255,0.4); 
            color: #fff; 
            background: rgba(255,255,255,0.05); 
        }
        
        .status-published { 
            background: rgba(16, 185, 129, 0.15); 
            color: #10b981; 
            padding: 0.35rem 0.85rem; 
            border-radius: 2rem; 
            font-size: 0.75rem; 
            font-weight: 500;
            margin-right: 0.5rem;
        }
        .status-draft { 
            background: rgba(245, 158, 11, 0.15); 
            color: #f59e0b; 
            padding: 0.35rem 0.85rem; 
            border-radius: 2rem; 
            font-size: 0.75rem; 
            font-weight: 500;
            margin-right: 0.5rem;
        }
        
        .diff-facile { color: #10b981; font-weight: 600; }
        .diff-intermédiaire { color: #f59e0b; font-weight: 600; }
        .diff-difficile { color: #ef4444; font-weight: 600; }
        
        .btn-icon { 
            width: 38px; 
            height: 38px; 
            border-radius: 10px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: rgba(255,255,255,0.05); 
            border: 1px solid rgba(255,255,255,0.1); 
            color: #999; 
            cursor: pointer; 
            transition: all 0.2s; 
            text-decoration: none; 
        }
        .btn-icon:hover { 
            background: rgba(255,255,255,0.1); 
            border-color: rgba(255,255,255,0.3); 
            color: #fff; 
        }
        .btn-icon.danger:hover { 
            background: rgba(239, 68, 68, 0.1); 
            border-color: rgba(239, 68, 68, 0.4); 
            color: #ef4444; 
        }
        
        .alert { 
            padding: 1rem 1.25rem; 
            border-radius: 12px; 
            margin-bottom: 1.5rem; 
            display: flex; 
            align-items: center; 
            gap: 0.75rem; 
        }
        .alert-success { 
            background: rgba(16, 185, 129, 0.1); 
            border: 1px solid rgba(16, 185, 129, 0.3); 
            color: #10b981; 
        }
    </style>
</head>
<body>
    <div class="bg-grid"></div>
    <div class="app-container">
        <nav class="sidebar">
            <div class="logo"><span class="logo-text">CyberSens</span></div>
            <div class="nav-menu">
                <a href="index.php" class="nav-item"><i data-lucide="layout-dashboard"></i><span>Dashboard</span></a>
                <a href="cours.php" class="nav-item active"><i data-lucide="book-open"></i><span>Gestion Modules</span></a>
                <a href="questions.php" class="nav-item"><i data-lucide="help-circle"></i><span>Banque Questions</span></a>
                <?php if (hasPermission('manage_content')): ?>
                <a href="news.php" class="nav-item"><i data-lucide="rss"></i><span>Actualités</span></a>
                <?php endif; ?>
                <?php if (hasPermission('manage_users')): ?>
                <a href="users.php" class="nav-item"><i data-lucide="users"></i><span>Utilisateurs</span></a>
                <?php endif; ?>
                <div class="nav-divider"></div>
                <a href="../../index.html" class="nav-item"><i data-lucide="arrow-left"></i><span>Retour au site</span></a>
            </div>
            <div class="sidebar-user">
                <div class="sidebar-user-avatar"><?php echo strtoupper(substr($currentUser['username'], 0, 1)); ?></div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                    <div class="sidebar-user-role"><?php echo getRoleName($currentUser['role']); ?></div>
                </div>
            </div>
        </nav>

        <main class="main-content">
            <div class="page-header">
                <div>
                    <h1>Gestion des modules</h1>
                    <p class="subtitle">Créez et organisez vos modules de formation et leurs sous-modules.</p>
                </div>
                <a href="add_cours.php" class="btn btn-primary"><i data-lucide="plus"></i> Nouveau module</a>
            </div>

            <?php if ($msg === 'created'): ?>
            <div class="alert alert-success"><i data-lucide="check-circle"></i> Module créé avec succès !</div>
            <?php elseif ($msg === 'updated'): ?>
            <div class="alert alert-success"><i data-lucide="check-circle"></i> Module mis à jour !</div>
            <?php elseif ($msg === 'deleted'): ?>
            <div class="alert alert-success"><i data-lucide="check-circle"></i> Module supprimé !</div>
            <?php elseif ($msg === 'sub_created'): ?>
            <div class="alert alert-success"><i data-lucide="check-circle"></i> Sous-module créé !</div>
            <?php elseif ($msg === 'sub_updated'): ?>
            <div class="alert alert-success"><i data-lucide="check-circle"></i> Sous-module mis à jour !</div>
            <?php elseif ($msg === 'sub_deleted'): ?>
            <div class="alert alert-success"><i data-lucide="check-circle"></i> Sous-module supprimé !</div>
            <?php endif; ?>

            <div class="modules-grid">
                <?php if (empty($modules)): ?>
                <div class="card">
                    <div class="empty-state">
                        <i data-lucide="book-open"></i>
                        <p>Aucun module créé. Cliquez sur "Nouveau module" pour commencer.</p>
                    </div>
                </div>
                <?php else: ?>
                <?php foreach ($modules as $module): 
                    $themeColor = $themes[$module['theme'] ?? 'blue'] ?? '#3b82f6';
                    $submodules = $allSubmodules[$module['id']] ?? [];
                ?>
                <div class="module-card" data-module-id="<?php echo $module['id']; ?>">
                    <div class="module-header" onclick="toggleModule(<?php echo $module['id']; ?>)">
                        <div class="module-info">
                            <div class="module-icon-large" style="background: <?php echo $themeColor; ?>20; color: <?php echo $themeColor; ?>;">
                                <?php echo $icons[$module['icon'] ?? 'shield'] ?? '🛡️'; ?>
                            </div>
                            <div class="module-details">
                                <h3><?php echo htmlspecialchars($module['title']); ?></h3>
                                <p><?php echo htmlspecialchars(substr($module['description'] ?? '', 0, 80)); ?></p>
                            </div>
                        </div>
                        
                        <div class="module-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $module['nb_submodules']; ?></div>
                                <div class="stat-label">Sous-modules</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?php echo $module['nb_questions']; ?></div>
                                <div class="stat-label">Questions</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value diff-<?php echo strtolower($module['difficulty'] ?? 'facile'); ?>"><?php echo $module['difficulty'] ?? 'Facile'; ?></div>
                                <div class="stat-label">Difficulté</div>
                            </div>
                        </div>
                        
                        <span class="<?php echo $module['is_published'] ? 'status-published' : 'status-draft'; ?>">
                            <?php echo $module['is_published'] ? 'Publié' : 'Brouillon'; ?>
                        </span>
                        
                        <div class="module-actions" onclick="event.stopPropagation();">
                            <a href="edit_cours.php?id=<?php echo $module['id']; ?>" class="btn-icon" title="Modifier"><i data-lucide="edit"></i></a>
                            <a href="questions.php?module_id=<?php echo $module['id']; ?>" class="btn-icon" title="Questions"><i data-lucide="help-circle"></i></a>
                            <a href="cours.php?delete_module=<?php echo $module['id']; ?>" class="btn-icon danger" title="Supprimer" onclick="return confirm('Supprimer ce module et tous ses sous-modules ?');"><i data-lucide="trash-2"></i></a>
                        </div>
                        
                        <i data-lucide="chevron-down" class="expand-icon"></i>
                    </div>
                    
                    <div class="module-content">
                        <div class="submodules-section">
                            <h4><i data-lucide="layers"></i> Sous-modules (<?php echo count($submodules); ?>)</h4>
                            
                            <?php if (empty($submodules)): ?>
                            <div class="empty-state">
                                <p>Aucun sous-module.</p>
                            </div>
                            <?php else: ?>
                            <div class="submodules-list">
                                <?php foreach ($submodules as $sub): ?>
                                <div class="submodule-item">
                                    <div class="submodule-info">
                                        <div class="submodule-icon" style="background: <?php echo $themeColor; ?>20; color: <?php echo $themeColor; ?>;">
                                            <?php echo $icons[$sub['icon'] ?? 'file-text'] ?? '📄'; ?>
                                        </div>
                                        <div>
                                            <div class="submodule-title"><?php echo htmlspecialchars($sub['title']); ?></div>
                                            <div class="submodule-meta"><?php echo $sub['estimated_time'] ?? 10; ?> min • <?php echo $sub['xp_reward'] ?? 10; ?> XP</div>
                                        </div>
                                    </div>
                                    <div class="module-actions">
                                        <a href="edit_submodule.php?id=<?php echo $sub['id']; ?>" class="btn-icon" title="Modifier"><i data-lucide="edit"></i></a>
                                        <a href="cours.php?delete_submodule=<?php echo $sub['id']; ?>" class="btn-icon danger" title="Supprimer" onclick="return confirm('Supprimer ce sous-module ?');"><i data-lucide="trash-2"></i></a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <a href="add_submodule.php?module_id=<?php echo $module['id']; ?>" class="btn-add-submodule">
                                <i data-lucide="plus"></i> Ajouter un sous-module
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        function toggleModule(moduleId) {
            document.querySelector(`.module-card[data-module-id="${moduleId}"]`).classList.toggle('expanded');
        }
        lucide.createIcons();
    </script>
</body>
</html>

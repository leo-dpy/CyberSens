<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

// Filtre par module
$module_filter = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;

// Suppression d'une question
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->execute([$id]);
    
    $redirect = $module_filter ? "questions.php?module_id=$module_filter&msg=deleted" : "questions.php?msg=deleted";
    header("Location: $redirect");
    exit;
}

// Récupérer tous les modules pour le filtre
$all_modules = $pdo->query("SELECT id, title FROM modules ORDER BY title")->fetchAll();

// Récupérer les questions
if ($module_filter) {
    $stmt = $pdo->prepare("SELECT q.*, m.title as module_title FROM questions q JOIN modules m ON q.module_id = m.id WHERE q.module_id = ? ORDER BY q.id");
    $stmt->execute([$module_filter]);
    $questions = $stmt->fetchAll();
    
    // Récupérer le nom du module filtré
    $module_stmt = $pdo->prepare("SELECT title FROM modules WHERE id = ?");
    $module_stmt->execute([$module_filter]);
    $module_name = $module_stmt->fetchColumn();
} else {
    $questions = $pdo->query("SELECT q.*, m.title as module_title FROM questions q JOIN modules m ON q.module_id = m.id ORDER BY m.id, q.id")->fetchAll();
    $module_name = null;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Questions - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css?v=<?php echo time(); ?>">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
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
                <a href="questions.php" class="nav-item active">
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
                    <h1>
                        <?php if($module_name): ?>
                            Questions : <?php echo htmlspecialchars($module_name); ?>
                        <?php else: ?>
                            Banque de Questions
                        <?php endif; ?>
                    </h1>
                    <p class="subtitle">Gérez les questions et les quiz associés aux modules.</p>
                </div>
                <div class="header-actions">
                    <a href="add_question.php<?php echo $module_filter ? '?module_id='.$module_filter : ''; ?>" class="btn btn-primary">
                        <i data-lucide="plus-circle"></i> Nouvelle question
                    </a>
                </div>
            </div>

            <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-success">
                <i data-lucide="check-circle"></i>
                <?php 
                if($_GET['msg'] == 'created') echo 'Question créée avec succès !';
                if($_GET['msg'] == 'updated') echo 'Question mise à jour !';
                if($_GET['msg'] == 'deleted') echo 'Question supprimée !';
                ?>
            </div>
            <?php endif; ?>

            <!-- Panneau de filtre -->
            <div class="card" style="margin-bottom: 2rem; border-color: var(--accent-primary);">
                <form method="GET" style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <label class="fw-medium" style="white-space: nowrap; color: var(--text-primary);">Filtrer par module :</label>
                    <select name="module_id" class="form-select" onchange="this.form.submit()" style="flex: 1; min-width: 200px; background: var(--bg-tertiary); color: var(--text-primary); border: 1px solid var(--border-color); padding: 0.5rem; border-radius: var(--radius-sm);">
                        <option value="">-- Tous les modules --</option>
                        <?php foreach($all_modules as $m): ?>
                            <option value="<?php echo $m['id']; ?>" <?php echo $module_filter == $m['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($m['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if($module_filter): ?>
                    <a href="questions.php" class="btn btn-outline" style="padding: 0.5rem 1rem;">
                        <i data-lucide="x"></i> Réinitialiser
                    </a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Tableau des questions -->
            <div class="admin-table-container">
                <?php if(count($questions) > 0): ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Module</th>
                            <th>Question</th>
                            <th>Difficulté</th>
                            <th>XP</th>
                            <th>Réponse</th>
                            <th class="text-end actions-col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($questions as $q): ?>
                        <?php 
                            $difficulty = $q['difficulty'] ?? 'Facile';
                            $xp = $q['xp_reward'] ?? 10;
                            
                            $diffCss = 'facile';
                            if($difficulty == 'Intermédiaire' || $difficulty == 'Moyen') $diffCss = 'moyen';
                            if($difficulty == 'Difficile') $diffCss = 'difficile';
                        ?>
                        <tr>
                            <td class="text-muted">#<?php echo $q['id']; ?></td>
                            <td>
                                <a href="questions.php?module_id=<?php echo $q['module_id']; ?>" style="color: var(--accent-primary); text-decoration: none;">
                                    <?php echo htmlspecialchars($q['module_title']); ?>
                                </a>
                            </td>
                            <td>
                                <div style="font-weight: 500; color: var(--text-primary); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($q['question']); ?>">
                                    <?php echo htmlspecialchars($q['question']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="difficulty-badge <?php echo $diffCss; ?>">
                                    <?php echo strtoupper($difficulty); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-xp">
                                    <i data-lucide="zap" style="width: 14px; height: 14px;"></i> <?php echo $xp; ?> XP
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-answer">
                                    <?php echo $q['correct_answer']; ?>
                                </span>
                            </td>
                            <td class="text-end actions-col">
                                <div class="admin-actions">
                                    <a href="edit_question.php?id=<?php echo $q['id']; ?>" class="btn-icon edit" title="Modifier">
                                        <i data-lucide="pencil"></i>
                                    </a>
                                    <a href="questions.php?delete=<?php echo $q['id']; ?><?php echo $module_filter ? '&module_id='.$module_filter : ''; ?>" 
                                       class="btn-icon delete" title="Supprimer" 
                                       onclick="return confirmAction(event, 'Êtes-vous sûr de vouloir supprimer cette question ?');">
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
                    <i data-lucide="help-circle" style="width: 64px; height: 64px; color: var(--text-muted); opacity: 0.5; margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.5rem;">Aucune question</h3>
                    <p class="text-muted" style="margin-bottom: 1.5rem;">
                        <?php if($module_filter): ?>
                            Ce module n'a pas encore de quiz.
                        <?php else: ?>
                            Commencez par créer des questions pour vos modules.
                        <?php endif; ?>
                    </p>
                    <a href="add_question.php<?php echo $module_filter ? '?module_id='.$module_filter : ''; ?>" class="btn btn-primary">
                        <i data-lucide="plus-circle"></i> Ajouter une question
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../../frontend/js/admin/shared.js"></script>
</body>
</html>

<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Récupérer les icônes disponibles
$icons = [
    'shield' => '🛡️ Sécurité',
    'lock' => '🔒 Mot de passe',
    'key' => '🔑 Authentification',
    'bug' => '🐛 Malware',
    'wifi' => '📶 Réseau',
    'mail' => '📧 Email',
    'globe' => '🌐 Web',
    'smartphone' => '📱 Mobile',
    'database' => '🗄️ Données',
    'cloud' => '☁️ Cloud',
    'code' => '💻 Code',
    'terminal' => '⌨️ Terminal',
    'alert-triangle' => '⚠️ Menaces',
    'eye' => '👁️ Surveillance',
    'users' => '👥 Social Engineering'
];

// Thèmes de couleurs pour les modules
$themes = [
    'blue' => ['name' => 'Bleu Cyber', 'primary' => '#3b82f6', 'gradient' => 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)'],
    'purple' => ['name' => 'Violet', 'primary' => '#8b5cf6', 'gradient' => 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)'],
    'green' => ['name' => 'Vert Sécurité', 'primary' => '#10b981', 'gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)'],
    'red' => ['name' => 'Rouge Alerte', 'primary' => '#ef4444', 'gradient' => 'linear-gradient(135deg, #ef4444 0%, #dc2626 100%)'],
    'orange' => ['name' => 'Orange', 'primary' => '#f59e0b', 'gradient' => 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)'],
    'cyan' => ['name' => 'Cyan Tech', 'primary' => '#06b6d4', 'gradient' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)'],
    'pink' => ['name' => 'Rose', 'primary' => '#ec4899', 'gradient' => 'linear-gradient(135deg, #ec4899 0%, #db2777 100%)']
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $difficulty = $_POST['difficulty'];
    $icon = $_POST['icon'] ?? 'shield';
    $theme = $_POST['theme'] ?? 'blue';
    $xp_reward = (int)($_POST['xp_reward'] ?? 50);
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if (empty($title)) {
        $error = "Veuillez saisir un titre pour le module.";
    }
    else {
        try {
            // Récupérer le prochain display_order
            $orderStmt = $pdo->query("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM modules");
            $nextOrder = $orderStmt->fetch()['next_order'];

            $stmt = $pdo->prepare("INSERT INTO modules (title, description, difficulty, icon, theme, xp_reward, display_order, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $difficulty, $icon, $theme, $xp_reward, $nextOrder, $is_published]);

            $module_id = $pdo->lastInsertId();

            // Rediriger vers la liste des modules
            header("Location: cours.php?msg=created");
            exit;

        }
        catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Module - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../../frontend/css/admin/add_cours.css">
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
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1>Créer un module</h1>
                    <p class="subtitle">Ajoutez un nouveau module de formation.</p>
                </div>
                <a href="cours.php" class="btn btn-outline"><i data-lucide="arrow-left"></i> Retour</a>
            </div>

            <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="alert-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST" id="moduleForm">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <!-- Colonne de gauche -->
                    <div class="card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 0;">Informations du module</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Titre du module</label>
                            <input type="text" name="title" class="form-input" required placeholder="Ex: Sécurité des mots de passe" id="titleInput">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-input" rows="4" placeholder="Décrivez ce que l'utilisateur apprendra dans ce module..." id="descInput"></textarea>
                        </div>

                        <div class="alert" style="background: rgba(59, 130, 246, 0.1); border: 1px solid #3b82f6; padding: 1rem; border-radius: var(--radius-md);">
                            <i data-lucide="info" style="color: #3b82f6;"></i>
                            <span style="color: var(--text-primary);">Les sous-modules et questions seront ajoutés après la création du module.</span>
                        </div>
                    </div>

                    <!-- Colonne de droite -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        
                        <!-- Carte paramètres -->
                        <div class="settings-card">
                            <div class="card-header">
                                <h3><i data-lucide="sliders"></i> Paramètres</h3>
                            </div>
                            
                            <!-- Difficulté -->
                            <div class="form-group">
                                <label class="form-label">Difficulté</label>
                                <input type="hidden" name="difficulty" id="difficultyInput" value="Facile">
                                <div class="difficulty-selector">
                                    <div class="difficulty-option selected" onclick="selectDifficulty('Facile', this)" data-value="Facile">
                                        <div class="diff-icon"><i data-lucide="zap"></i></div>
                                        <span>Facile</span>
                                    </div>
                                    <div class="difficulty-option" onclick="selectDifficulty('Intermédiaire', this)" data-value="Intermédiaire">
                                        <div class="diff-icon"><i data-lucide="activity"></i></div>
                                        <span>Moyen</span>
                                    </div>
                                    <div class="difficulty-option" onclick="selectDifficulty('Difficile', this)" data-value="Difficile">
                                        <div class="diff-icon"><i data-lucide="skull"></i></div>
                                        <span>Difficile</span>
                                    </div>
                                </div>
                            </div>

                            <div class="separator"></div>

                            <!-- Icône -->
                            <div class="form-group">
                                <label class="form-label">Icône</label>
                                <input type="hidden" name="icon" id="iconInput" value="shield">
                                <div class="icon-grid">
                                    <?php foreach ($icons as $value => $label):
                                        $emoji = explode(' ', $label)[0];
                                    ?>
                                    <div class="icon-option <?php echo $value === 'shield' ? 'selected' : ''; ?>" onclick="selectIcon('<?php echo $value; ?>', this)">
                                        <?php echo $emoji; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="separator"></div>

                            <!-- Thème -->
                            <div class="form-group">
                                <label class="form-label">Thème</label>
                                <input type="hidden" name="theme" id="themeInput" value="blue">
                                <div class="theme-grid">
                                    <?php foreach ($themes as $key => $theme): ?>
                                    <div class="theme-option <?php echo $key === 'blue' ? 'selected' : ''; ?>" 
                                         style="background: <?php echo $theme['gradient']; ?>"
                                         onclick="selectTheme('<?php echo $key; ?>', this)"></div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="separator"></div>

                            <!-- XP -->
                            <div class="form-group">
                                <label class="form-label">XP Récompense (quiz)</label>
                                <input type="number" name="xp_reward" value="50" class="form-input">
                            </div>

                            <div class="separator"></div>

                            <!-- Publié -->
                            <div class="form-group">
                                <label class="cyber-toggle-label">
                                    <div class="toggle-info">
                                        <span class="toggle-title">Publier immédiatement</span>
                                        <span class="toggle-desc">Rendre visible aux utilisateurs</span>
                                    </div>
                                    <div class="cyber-toggle-wrapper">
                                        <input type="checkbox" name="is_published" checked>
                                        <span class="toggle-slider"></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- CTA -->
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                            <i data-lucide="plus-circle"></i> Créer le module
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../../frontend/js/admin/shared.js"></script>
    <script>
        // Fonctions de sélection
        function selectDifficulty(value, element) {
            document.querySelectorAll('.difficulty-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('difficultyInput').value = value;
        }

        function selectIcon(value, element) {
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('iconInput').value = value;
        }

        function selectTheme(value, element) {
            document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('themeInput').value = value;
        }

        // Init Lucide
        lucide.createIcons();
    </script>
</body>
</html>

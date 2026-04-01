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

// Thèmes de couleurs pour les cours
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
    $content = $_POST['content'];
    $difficulty = $_POST['difficulty'];
    $icon = $_POST['icon'] ?? 'shield';
    $theme = $_POST['theme'] ?? 'blue';
    $xp_reward = (int)$_POST['xp_reward'] ?? 25;
    $estimated_time = (int)$_POST['estimated_time'] ?? 15;
    $is_hidden = isset($_POST['is_hidden']) ? 1 : 0;

    if (empty($title) || empty($description) || empty($content)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
    else {
        try {
            // Vérifier si les colonnes existent
            $columns = $pdo->query("DESCRIBE cours")->fetchAll(PDO::FETCH_COLUMN);

            $sql = "INSERT INTO cours (title, description, content, difficulty";
            $params = [$title, $description, $content, $difficulty];

            if (in_array('icon', $columns)) {
                $sql .= ", icon";
                $params[] = $icon;
            }
            if (in_array('theme', $columns)) {
                $sql .= ", theme";
                $params[] = $theme;
            }
            if (in_array('xp_reward', $columns)) {
                $sql .= ", xp_reward";
                $params[] = $xp_reward;
            }
            if (in_array('estimated_time', $columns)) {
                $sql .= ", estimated_time";
                $params[] = $estimated_time;
            }
            if (in_array('is_hidden', $columns)) {
                $sql .= ", is_hidden";
                $params[] = $is_hidden;
            }

            $sql .= ") VALUES (" . implode(',', array_fill(0, count($params), '?')) . ")";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $course_id = $pdo->lastInsertId();

            // Rediriger vers l'ajout de questions
            header("Location: add_question.php?course_id=" . $course_id . "&new=1");
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
    <title>Créer un Cours - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
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
                <a href="cours.php" class="nav-item active"><i data-lucide="book-open"></i><span>Gestion Cours</span></a>
                <a href="questions.php" class="nav-item"><i data-lucide="help-circle"></i><span>Banque Questions</span></a>
                
                <?php if (hasPermission('manage_content')): ?>
                <a href="news.php" class="nav-item"><i data-lucide="rss"></i><span>Actualités</span></a>
                <?php
endif; ?>


                
                <a href="users.php" class="nav-item"><i data-lucide="users"></i><span>Utilisateurs</span></a>
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
                    <h1>Créer un cours</h1>
                    <p class="subtitle">Ajoutez un nouveau module d'apprentissage.</p>
                </div>
                <a href="cours.php" class="btn btn-outline"><i data-lucide="arrow-left"></i> Retour</a>
            </div>

            <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="alert-circle"></i> <?php echo $error; ?>
            </div>
            <?php
endif; ?>

            <form method="POST" id="courseForm">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <!-- Colonne de gauche -->
                    <div class="card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 0;">Contenu du cours</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Titre du cours</label>
                            <input type="text" name="title" class="form-input" required placeholder="Ex: Introduction au Phishing" oninput="updatePreview()" id="titleInput">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description courte</label>
                            <textarea name="description" class="form-input" rows="2" required placeholder="Bref résumé du module..." oninput="updatePreview()" id="descInput"></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contenu détaillé</label>
                            <div id="editor"></div>
                            <input type="hidden" name="content" id="content">
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
                                    <?php
endforeach; ?>
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
                                    <?php
endforeach; ?>
                                </div>
                            </div>

                            <div class="separator"></div>

                            <!-- Statistiques -->
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                <div class="form-group">
                                    <label class="form-label">XP Récompense</label>
                                    <input type="number" name="xp_reward" value="25" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Durée (min)</label>
                                    <input type="number" name="estimated_time" value="15" class="form-input">
                                </div>
                            </div>

                            <div class="separator"></div>

                            <!-- Caché -->
                            <div class="form-group">
                                <label class="cyber-toggle-label">
                                    <div class="toggle-info">
                                        <span class="toggle-title">Mode Brouillon</span>
                                        <span class="toggle-desc">Cacher ce cours</span>
                                    </div>
                                    <div class="cyber-toggle-wrapper">
                                        <input type="checkbox" name="is_hidden">
                                        <span class="toggle-slider"></span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- CTA -->
                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                            <i data-lucide="plus-circle"></i> Créer le cours
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <!-- Module de redimensionnement d'image Quill -->
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    <script src="../../frontend/js/admin/shared.js"></script>
    <script src="../../frontend/js/admin/add_cours.js"></script>
</body>
</html>

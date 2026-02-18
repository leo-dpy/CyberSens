<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: cours.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$id]);
$course = $stmt->fetch();

if (!$course) {
    header("Location: cours.php");
    exit;
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $content = $_POST['content'];
    $difficulty = $_POST['difficulty'];
    $is_hidden = isset($_POST['is_hidden']) ? 1 : 0;

    if (empty($title) || empty($description) || empty($content)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
    else {
        try {
            $columns = $pdo->query("DESCRIBE courses")->fetchAll(PDO::FETCH_COLUMN);

            if (in_array('is_hidden', $columns)) {
                $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, content = ?, difficulty = ?, is_hidden = ? WHERE id = ?");
                $stmt->execute([$title, $description, $content, $difficulty, $is_hidden, $id]);
            }
            else {
                $stmt = $pdo->prepare("UPDATE courses SET title = ?, description = ?, content = ?, difficulty = ? WHERE id = ?");
                $stmt->execute([$title, $description, $content, $difficulty, $id]);
            }

            header("Location: cours.php?msg=updated");
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
    <title>Modifier Cours - Admin CyberSens</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="icon" type="image/svg+xml" href="../favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/edit_cours.css">
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
                <a href="../index.html" class="nav-item"><i data-lucide="arrow-left"></i><span>Retour au site</span></a>
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
                    <h1>Modifier le cours</h1>
                    <p class="subtitle">Édition du contenu : <?php echo htmlspecialchars($course['title']); ?></p>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                    <a href="questions.php?course_id=<?php echo $id; ?>" class="btn btn-outline" style="color: var(--warning); border-color: var(--warning);">
                        <i data-lucide="help-circle"></i> Gérer Questions
                    </a>
                    <a href="cours.php" class="btn btn-outline"><i data-lucide="arrow-left"></i> Retour</a>
                </div>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i data-lucide="alert-circle"></i> <?php echo $error; ?>
            </div>
            <?php
endif; ?>

            <form method="POST" id="courseForm">
                <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 2rem;">
                    
                    <!-- Contenu principal -->
                    <div class="card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <h3 style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 0;">Contenu</h3>
                        
                        <div class="form-group">
                            <label class="form-label">Titre</label>
                            <input type="text" name="title" class="form-input" required 
                                   value="<?php echo htmlspecialchars($course['title']); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-input" rows="3" required><?php echo htmlspecialchars($course['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contenu</label>
                            <div id="editor"><?php echo $course['content']; ?></div>
                            <input type="hidden" name="content" id="content">
                        </div>
                    </div>

                    <!-- Paramètres latéraux -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="card settings-card">
                            <div class="card-header">
                                <h3><i data-lucide="sliders"></i> Paramètres</h3>
                            </div>
                            
                            <div class="form-group">
                                <label class="form-label">Niveau de difficulté</label>
                                <input type="hidden" name="difficulty" id="difficultyInput" value="<?php echo $course['difficulty']; ?>">
                                <div class="difficulty-selector">
                                    <div class="difficulty-option <?php echo $course['difficulty'] == 'Facile' ? 'selected' : ''; ?>" 
                                         onclick="selectDifficulty('Facile', this)" data-value="Facile">
                                        <div class="diff-icon"><i data-lucide="zap"></i></div>
                                        <span>Facile</span>
                                    </div>
                                    <div class="difficulty-option <?php echo $course['difficulty'] == 'Intermédiaire' ? 'selected' : ''; ?>" 
                                         onclick="selectDifficulty('Intermédiaire', this)" data-value="Intermédiaire">
                                        <div class="diff-icon"><i data-lucide="activity"></i></div>
                                        <span>Intermédiaire</span>
                                    </div>
                                    <div class="difficulty-option <?php echo $course['difficulty'] == 'Difficile' ? 'selected' : ''; ?>" 
                                         onclick="selectDifficulty('Difficile', this)" data-value="Difficile">
                                        <div class="diff-icon"><i data-lucide="skull"></i></div>
                                        <span>Difficile</span>
                                    </div>
                                </div>
                            </div>

                            <div class="separator"></div>

                            <div class="form-group">
                                <label class="cyber-toggle-label">
                                    <div class="toggle-info">
                                        <span class="toggle-title">Mode Brouillon</span>
                                        <span class="toggle-desc">Visibilité du cours</span>
                                    </div>
                                    <div class="cyber-toggle-wrapper">
                                        <input type="checkbox" name="is_hidden" id="isHiddenCheckbox" <?php echo(!empty($course['is_hidden']) && $course['is_hidden'] == 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </div>
                                </label>
                            </div>

                            <div class="separator"></div>

                            <div class="meta-info">
                                <div class="meta-item">
                                    <i data-lucide="calendar"></i>
                                    <span>Créé le <?php echo date('d/m/Y', strtotime($course['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                            <i data-lucide="save"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <!-- Module de redimensionnement d'image Quill -->
    <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    <script src="../js/admin/shared.js"></script>
    <script src="../js/admin/edit_cours.js"></script>
</body>
</html>

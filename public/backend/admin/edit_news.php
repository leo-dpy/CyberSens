<?php
require_once 'auth.php';
checkContentAccess();

$currentUser = getCurrentUser();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: news.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$newsItem = $stmt->fetch();

if (!$newsItem) {
    header("Location: news.php?error=notfound");
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $source = trim($_POST['source']);
    $link = trim($_POST['link']);

    if (empty($title) || empty($event_date) || empty($source)) {
        $error = "Tous les champs obligatoires doivent être remplis.";
    } else {
        if (empty($link)) $link = '#';

        try {
            $stmt = $pdo->prepare("UPDATE news SET title = ?, description = ?, event_date = ?, source = ?, link = ? WHERE id = ?");
            $stmt->execute([$title, $description, $event_date, $source, $link, $id]);
            
            header("Location: news.php?msg=updated");
            exit;
        } catch (PDOException $e) {
            $error = "Erreur lors de la modification : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Actualité - Admin CyberSens</title>
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
        </nav>

        <!-- Contenu Principal -->
        <main class="main-content">
            <div class="content-wrapper">
                <div class="page-header-content">
                    <h1>Modifier l'Actualité</h1>
                    <p class="text-muted"><?php echo htmlspecialchars($newsItem['title']); ?></p>
                </div>

                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <div class="form-container">
                    <form method="POST" action="" class="admin-form">
                        <div class="form-group">
                            <label for="title">Titre / Cible <span class="required">*</span></label>
                            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($newsItem['title']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="source">Type d'incident (Source) <span class="required">*</span></label>
                            <input type="text" id="source" name="source" required value="<?php echo htmlspecialchars($newsItem['source']); ?>" list="sources-list">
                            <datalist id="sources-list">
                                <option value="Rançongiciel">
                                <option value="Fuite de Données">
                                <option value="Phishing">
                                <option value="Attaque DDoS">
                                <option value="Piratage Compte">
                                <option value="Fuite Bancaire">
                            </datalist>
                        </div>

                        <div class="form-group">
                            <label for="event_date">Date de l'incident <span class="required">*</span></label>
                            <input type="date" id="event_date" name="event_date" required value="<?php echo htmlspecialchars($newsItem['event_date']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?php echo htmlspecialchars($newsItem['description']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="link">Lien (Optionnel)</label>
                            <input type="url" id="link" name="link" value="<?php echo htmlspecialchars($newsItem['link'] === '#' ? '' : $newsItem['link']); ?>" placeholder="https://...">
                        </div>

                        <div class="form-actions">
                            <a href="news.php" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> Mettre à jour
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>

<?php
require_once 'auth.php';
checkContentAccess();

$currentUser = getCurrentUser();

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
            $stmt = $pdo->prepare("INSERT INTO news (title, description, event_date, source, link) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $event_date, $source, $link]);
            
            header("Location: news.php?msg=created");
            exit;
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Actualité - Admin CyberSens</title>
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
                    <h1>Nouvelle Actualité</h1>
                    <p class="text-muted">Ajouter un nouveau hack ou incident à la liste.</p>
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
                            <input type="text" id="title" name="title" required placeholder="Ex: Entreprise X, Ministère Y...">
                        </div>

                        <div class="form-group">
                            <label for="source">Type d'incident (Source) <span class="required">*</span></label>
                            <input type="text" id="source" name="source" required placeholder="Ex: Rançongiciel, Fuite de données, Phishing..." list="sources-list">
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
                            <input type="date" id="event_date" name="event_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" placeholder="Détails de l'attaque, impact, conséquences..."></textarea>
                        </div>

                        <div class="form-group">
                            <label for="link">Lien (Optionnel)</label>
                            <input type="url" id="link" name="link" placeholder="https://...">
                            <small style="color: var(--text-muted); display: block; margin-top: 5px;">Laissez vide si aucun lien public.</small>
                        </div>

                        <div class="form-actions">
                            <a href="news.php" class="btn btn-outline">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-lucide="save"></i> Enregistrer
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

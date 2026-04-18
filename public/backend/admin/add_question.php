<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

$all_modules = $pdo->query("SELECT id, title FROM modules ORDER BY display_order, title")->fetchAll();
$selected_module = isset($_GET['module_id']) ? (int)$_GET['module_id'] : null;
$is_new_module = isset($_GET['new']) && $_GET['new'] == 1;

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $module_id = (int)$_POST['module_id'];
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_answer = strtoupper($_POST['correct_answer']);
    $explanation = trim($_POST['explanation'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'Facile';
    $xp_reward = (int)($_POST['xp_reward'] ?? 10);
    $points = (int)($_POST['points'] ?? 10);
    
    if (empty($module_id) || empty($question) || empty($option_a) || empty($option_b) || empty($option_c)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO questions (module_id, question, option_a, option_b, option_c, option_d, correct_answer, explanation, difficulty, xp_reward, points) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$module_id, $question, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $difficulty, $xp_reward, $points]);
            
            if (isset($_POST['add_another'])) {
                $success = "Question ajoutée ! Vous pouvez en ajouter une autre.";
                $selected_module = $module_id;
            } else {
                header("Location: questions.php?module_id=" . $module_id . "&msg=created");
                exit;
            }
        } catch (PDOException $e) {
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
    <title>Ajouter Question - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../../frontend/css/admin/add_question.css">
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
                <a href="cours.php" class="nav-item"><i data-lucide="book-open"></i><span>Gestion Modules</span></a>
                <a href="questions.php" class="nav-item active"><i data-lucide="help-circle"></i><span>Banque Questions</span></a>
                
                <?php if(hasPermission('manage_content')): ?>
                <a href="news.php" class="nav-item"><i data-lucide="rss"></i><span>Actualités</span></a>
                <?php endif; ?>

                <?php if(hasPermission('manage_users')): ?>
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
                    <h1>Nouvelle Question</h1>
                    <p class="subtitle">Enrichissez la banque de quiz.</p>
                </div>
                <a href="questions.php" class="btn btn-outline"><i data-lucide="arrow-left"></i> Retour</a>
            </div>

            <?php if($success): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid var(--success); color: var(--success); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="check-circle"></i> <?php echo $success; ?>
            </div>
            <?php endif; ?>

            <?php if($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid var(--danger); color: var(--danger); padding: 1rem; border-radius: var(--radius-md); margin-bottom: 2rem; display: flex; align-items: center; gap: 0.75rem;">
                <i data-lucide="alert-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <div class="card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Module associé</label>
                            <select name="module_id" class="form-input" required>
                                <option value="">Choisir un module...</option>
                                <?php foreach($all_modules as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php echo $selected_module == $m['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Énoncé de la question</label>
                            <textarea name="question" class="form-input" rows="3" required placeholder="La question posée à l'utilisateur..."></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Réponses (Cochez la bonne réponse)</label>
                            
                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="A" checked>
                                </label>
                                <input type="text" name="option_a" class="form-input option-input" required placeholder="Option A">
                            </div>

                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="B">
                                </label>
                                <input type="text" name="option_b" class="form-input option-input" required placeholder="Option B">
                            </div>

                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="C">
                                </label>
                                <input type="text" name="option_c" class="form-input option-input" required placeholder="Option C">
                            </div>

                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="D">
                                </label>
                                <input type="text" name="option_d" class="form-input option-input" placeholder="Option D (Optionnel)">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <h3>Détails</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Difficulté</label>
                                <select name="difficulty" class="form-input">
                                    <option value="Facile">Facile (10 XP)</option>
                                    <option value="Intermédiaire">Intermédiaire (15 XP)</option>
                                    <option value="Difficile">Difficile (30 XP)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Explication (Feedback)</label>
                                <textarea name="explanation" class="form-input" rows="4" placeholder="Expliquez pourquoi c'est la bonne réponse..."></textarea>
                            </div>
                        </div>

                        <button type="submit" name="add_another" class="btn btn-outline" style="justify-content: center; width: 100%;">
                            <i data-lucide="plus"></i> Enregistrer & Ajouter
                        </button>
                        <button type="submit" class="btn btn-primary" style="justify-content: center; width: 100%;">
                            <i data-lucide="check"></i> Terminer
                        </button>
                    </div>

                </div>
            </form>
        </main>
    </div>
    <script src="../../frontend/js/admin/shared.js"></script>
</body>
</html>

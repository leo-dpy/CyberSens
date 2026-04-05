<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: questions.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM questions WHERE id = ?");
$stmt->execute([$id]);
$question = $stmt->fetch();

if (!$question) {
    header("Location: questions.php");
    exit;
}

$all_modules = $pdo->query("SELECT id, title FROM modules ORDER BY display_order, title")->fetchAll();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $module_id = (int)$_POST['module_id'];
    $question_text = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_answer = strtoupper($_POST['correct_answer']);
    $explanation = trim($_POST['explanation'] ?? '');
    $difficulty = $_POST['difficulty'] ?? 'Facile';
    $xp_reward = (int)($_POST['xp_reward'] ?? 10);

    if (empty($module_id) || empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c)) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
    else {
        try {
            $stmt = $pdo->prepare("UPDATE questions SET module_id = ?, question = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, correct_answer = ?, explanation = ?, difficulty = ?, xp_reward = ? WHERE id = ?");
            $stmt->execute([$module_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $explanation, $difficulty, $xp_reward, $id]);

            header("Location: questions.php?module_id=" . $module_id . "&msg=updated");
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
    <title>Modifier Question - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../../frontend/css/admin/edit_question.css">
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
                    <h1>Modifier Question #<?php echo $id; ?></h1>
                    <p class="subtitle">Mise à jour du contenu.</p>
                </div>
                <a href="questions.php" class="btn btn-outline"><i data-lucide="arrow-left"></i> Retour</a>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i data-lucide="alert-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    
                    <div class="card static-card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label">Module associé</label>
                            <select name="module_id" class="form-input" required>
                                <?php foreach ($all_modules as $m): ?>
                                <option value="<?php echo $m['id']; ?>" <?php echo $question['module_id'] == $m['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($m['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Énoncé de la question</label>
                            <textarea name="question" class="form-input" rows="3" required><?php echo htmlspecialchars($question['question']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Réponses (Cochez la bonne réponse)</label>
                            
                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="A" <?php echo $question['correct_answer'] == 'A' ? 'checked' : ''; ?>>
                                </label>
                                <input type="text" name="option_a" class="form-input option-input" required value="<?php echo htmlspecialchars($question['option_a']); ?>">
                            </div>

                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="B" <?php echo $question['correct_answer'] == 'B' ? 'checked' : ''; ?>>
                                </label>
                                <input type="text" name="option_b" class="form-input option-input" required value="<?php echo htmlspecialchars($question['option_b']); ?>">
                            </div>

                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="C" <?php echo $question['correct_answer'] == 'C' ? 'checked' : ''; ?>>
                                </label>
                                <input type="text" name="option_c" class="form-input option-input" required value="<?php echo htmlspecialchars($question['option_c']); ?>">
                            </div>

                            <div class="option-group">
                                <label class="option-radio-label">
                                    <input type="radio" name="correct_answer" value="D" <?php echo $question['correct_answer'] == 'D' ? 'checked' : ''; ?>>
                                </label>
                                <input type="text" name="option_d" class="form-input option-input" value="<?php echo htmlspecialchars($question['option_d'] ?? ''); ?>" placeholder="Optionnelle">
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="card static-card" style="display: flex; flex-direction: column; gap: 1.5rem;">
                            <h3>Détails</h3>
                            
                            <div class="form-group">
                                <label class="form-label">Difficulté</label>
                                <select name="difficulty" class="form-input">
                                    <option value="Facile" <?php echo($question['difficulty'] ?? 'Facile') == 'Facile' ? 'selected' : ''; ?>>Facile (10 XP)</option>
                                    <option value="Intermédiaire" <?php echo($question['difficulty'] ?? '') == 'Intermédiaire' ? 'selected' : ''; ?>>Intermédiaire (15 XP)</option>
                                    <option value="Difficile" <?php echo($question['difficulty'] ?? '') == 'Difficile' ? 'selected' : ''; ?>>Difficile (30 XP)</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Explication (Feedback)</label>
                                <textarea name="explanation" class="form-input" rows="4"><?php echo htmlspecialchars($question['explanation'] ?? ''); ?></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="justify-content: center; width: 100%;">
                            <i data-lucide="save"></i> Enregistrer
                        </button>
                    </div>

                </div>
            </form>
        </main>
    </div>
    <script src="../../frontend/js/admin/shared.js"></script>
</body>
</html>

<?php
require_once 'auth.php';
checkCoursesAccess();

$currentUser = getCurrentUser();
$error = '';
$success = '';

// Module ID requis
if (!isset($_GET['module_id']) || !is_numeric($_GET['module_id'])) {
    header("Location: cours.php");
    exit;
}

$module_id = (int)$_GET['module_id'];

// Vérifier que le module existe
$moduleStmt = $pdo->prepare("SELECT * FROM modules WHERE id = ?");
$moduleStmt->execute([$module_id]);
$module = $moduleStmt->fetch();

if (!$module) {
    header("Location: cours.php");
    exit;
}

// Icônes disponibles
$icons = [
    'shield' => '🛡️', 'lock' => '🔒', 'key' => '🔑', 'bug' => '🐛', 'wifi' => '📶',
    'mail' => '📧', 'globe' => '🌐', 'smartphone' => '📱', 'database' => '🗄️',
    'cloud' => '☁️', 'code' => '💻', 'terminal' => '⌨️', 'alert-triangle' => '⚠️',
    'eye' => '👁️', 'users' => '👥', 'file-text' => '📄', 'book' => '📖', 'bookmark' => '🔖'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $content = $_POST['content'] ?? '';
    $icon = $_POST['icon'] ?? 'file-text';
    $xp_reward = (int)($_POST['xp_reward'] ?? 15);
    $estimated_time = (int)($_POST['estimated_time'] ?? 10);

    if (empty($title)) {
        $error = "Veuillez saisir un titre.";
    } else {
        try {
            // Récupérer le prochain display_order
            $orderStmt = $pdo->prepare("SELECT COALESCE(MAX(display_order), 0) + 1 as next_order FROM submodules WHERE module_id = ?");
            $orderStmt->execute([$module_id]);
            $nextOrder = $orderStmt->fetch()['next_order'];

            $stmt = $pdo->prepare("INSERT INTO submodules (module_id, title, description, content, icon, xp_reward, estimated_time, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$module_id, $title, $description, $content, $icon, $xp_reward, $estimated_time, $nextOrder]);

            header("Location: cours.php?msg=sub_created");
            exit;
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
    <title>Nouveau Sous-module - Admin CyberSens</title>
    <link rel="stylesheet" href="../../frontend/styles.css">
    <link rel="icon" type="image/svg+xml" href="../../frontend/favicon.svg">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .icon-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .icon-option {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 2px solid transparent;
            background: rgba(255,255,255,0.05);
            font-size: 1.25rem;
            transition: all 0.2s;
        }
        .icon-option:hover {
            border-color: var(--primary);
        }
        .icon-option.selected {
            border-color: var(--primary);
            background: rgba(59, 130, 246, 0.2);
        }
        /* Quill Editor Styles */
        .editor-container {
            background: rgba(0,0,0,0.2);
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            overflow: hidden;
        }
        .ql-toolbar.ql-snow {
            background: rgba(255,255,255,0.05);
            border: none;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .ql-container.ql-snow {
            border: none;
            min-height: 300px;
            font-size: 1rem;
        }
        .ql-editor {
            color: #fff;
            min-height: 300px;
        }
        .ql-editor.ql-blank::before {
            color: #666;
            font-style: normal;
        }
        .ql-snow .ql-stroke { stroke: #aaa; }
        .ql-snow .ql-fill { fill: #aaa; }
        .ql-snow .ql-picker { color: #aaa; }
        .ql-snow .ql-picker-options { background: #1a1a2e; border-color: rgba(255,255,255,0.1); }
        .ql-snow .ql-picker-item:hover { color: #3b82f6; }
        .ql-snow .ql-picker-item.ql-selected { color: #3b82f6; }
        .ql-snow button:hover .ql-stroke { stroke: #3b82f6; }
        .ql-snow button:hover .ql-fill { fill: #3b82f6; }
        .ql-snow button.ql-active .ql-stroke { stroke: #3b82f6; }
        .ql-snow button.ql-active .ql-fill { fill: #3b82f6; }
        .ql-toolbar.ql-snow .ql-formats { margin-right: 10px; }
        .ql-editor h1, .ql-editor h2, .ql-editor h3 { color: #fff; }
        .ql-editor a { color: #3b82f6; }
        .ql-editor blockquote { border-left: 4px solid #3b82f6; padding-left: 1rem; color: #aaa; }
        .ql-editor pre.ql-syntax { background: rgba(0,0,0,0.4); border-radius: 6px; padding: 1rem; }
        .ql-editor code { background: rgba(59, 130, 246, 0.2); padding: 2px 6px; border-radius: 4px; color: #3b82f6; }
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
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h1>Nouveau sous-module</h1>
                    <p class="subtitle">Module : <?php echo htmlspecialchars($module['title']); ?></p>
                </div>
                <a href="cours.php" class="btn btn-outline"><i data-lucide="arrow-left"></i> Retour</a>
            </div>

            <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #ef4444; color: #ef4444; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <i data-lucide="alert-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <form method="POST">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                    <div class="card" style="padding: 1.5rem;">
                        <h3 style="margin-bottom: 1.5rem; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem;">Contenu</h3>
                        
                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label class="form-label">Titre *</label>
                            <input type="text" name="title" class="form-input" required placeholder="Ex: Créer un mot de passe fort">
                        </div>

                        <div class="form-group" style="margin-bottom: 1.5rem;">
                            <label class="form-label">Description courte</label>
                            <input type="text" name="description" class="form-input" placeholder="Résumé du contenu...">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Contenu du cours</label>
                            <input type="hidden" name="content" id="contentInput">
                            <div class="editor-container">
                                <div id="editor"></div>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="card" style="padding: 1.5rem;">
                            <h3 style="margin-bottom: 1.5rem;">Paramètres</h3>
                            
                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label class="form-label">Icône</label>
                                <input type="hidden" name="icon" id="iconInput" value="file-text">
                                <div class="icon-grid">
                                    <?php foreach ($icons as $key => $emoji): ?>
                                    <div class="icon-option <?php echo $key === 'file-text' ? 'selected' : ''; ?>" data-value="<?php echo $key; ?>" onclick="selectIcon('<?php echo $key; ?>', this)">
                                        <?php echo $emoji; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 1.5rem;">
                                <label class="form-label">Durée estimée (minutes)</label>
                                <input type="number" name="estimated_time" class="form-input" value="10" min="1">
                            </div>

                            <div class="form-group">
                                <label class="form-label">XP Récompense</label>
                                <input type="number" name="xp_reward" class="form-input" value="15" min="0">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 1rem;">
                            <i data-lucide="plus-circle"></i> Créer le sous-module
                        </button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
    <script src="https://unpkg.com/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
    <script>
        // Sélection d'icône
        function selectIcon(value, element) {
            document.querySelectorAll('.icon-option').forEach(el => el.classList.remove('selected'));
            element.classList.add('selected');
            document.getElementById('iconInput').value = value;
        }

        // Initialiser Quill avec imageResize
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Rédigez le contenu du cours ici...',
            modules: {
                imageResize: {
                    displaySize: true,
                    modules: [ 'Resize', 'DisplaySize', 'Toolbar' ]
                },
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    [{ 'align': [] }],
                    ['clean']
                ]
            }
        });

        // Logique d'extraction des URLs avec modification avant soumission
        document.querySelector('form').addEventListener('submit', function() {
            let content = quill.root.innerHTML;

            // Extraire et remplacer les liens YouTube et Vimeo en texte brut
            const ytRegex = /(?:<p>)?(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]{11})(?:[^\s<]*)(?:<\/p>)?/gi;
            const vmRegex = /(?:<p>)?(?:https?:\/\/)?(?:www\.)?(?:vimeo\.com\/)(\d+)(?:[^\s<]*)(?:<\/p>)?/gi;

            let iframes = [];

            content = content.replace(ytRegex, function(match, videoId) {
                iframes.push(`<div class="video-embed-container"><iframe src="https://www.youtube.com/embed/${videoId}" allowfullscreen></iframe></div>`);
                return '';
            });

            content = content.replace(vmRegex, function(match, videoId) {
                iframes.push(`<div class="video-embed-container"><iframe src="https://player.vimeo.com/video/${videoId}" allowfullscreen></iframe></div>`);
                return '';
            });

            if (iframes.length > 0) {
                content += "\n" + iframes.join("\n");
            }
            
            document.getElementById('contentInput').value = content;
        });

        lucide.createIcons();
    </script>
</body>
</html>

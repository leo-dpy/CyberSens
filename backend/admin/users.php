<?php
require_once 'auth.php';
checkUsersAccess();

$currentUser = getCurrentUser();
$isSuperAdmin = $currentUser['role'] === ROLE_SUPERADMIN;

$message = '';
$messageType = '';

// Gestion des messages asynchrones (PRG / GET)
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
} elseif (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'deleted') {
        $message = "Utilisateur supprimé avec succès.";
        $messageType = "success";
    } elseif ($_GET['msg'] === 'forbidden') {
        $message = "Vous n'avez pas la permission d'effectuer cette action.";
        $messageType = "danger";
    }
}

// Groupes disponibles (depuis la BDD, avec auto-création si nécessaire)
try {
    // Vérifier si la table existe, sinon la créer
    try {
        $pdo->query("SELECT 1 FROM `groups` LIMIT 1");
    } catch (PDOException $e) {
        // Table n'existe pas, on la crée
        $pdo->exec("CREATE TABLE IF NOT EXISTS `groups` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(50) NOT NULL,
            `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    // Si la table est vide, insérer les groupes par défaut
    $countStmt = $pdo->query("SELECT COUNT(*) FROM `groups`");
    if ((int)$countStmt->fetchColumn() === 0) {
        $defaultGroups = ['Red Team', 'Blue Team', 'Purple Team', 'Staff', 'VIP'];
        $insertStmt = $pdo->prepare("INSERT IGNORE INTO `groups` (`name`) VALUES (?)");
        foreach ($defaultGroups as $g) {
            $insertStmt->execute([$g]);
        }
    }

    $groupsStmt = $pdo->query("SELECT id, name FROM `groups` ORDER BY name ASC");
    $dbGroups = $groupsStmt->fetchAll();
    $availableGroups = ['Aucun'];
    foreach ($dbGroups as $g) {
        $availableGroups[] = $g['name'];
    }
} catch (PDOException $e) {
    // Fallback ultime
    $availableGroups = ['Aucun', 'Red Team', 'Blue Team', 'Purple Team', 'Staff', 'VIP'];
    $dbGroups = [];
}

// Récupérer tous les utilisateurs
$sql = "SELECT u.*, 
    (SELECT COUNT(*) FROM progression WHERE user_id = u.id AND is_completed = 1) as completed_courses,
    (SELECT COUNT(*) FROM user_badges WHERE user_id = u.id) as badges_count
    FROM users u ORDER BY 
        CASE u.role 
            WHEN 'superadmin' THEN 1 
            WHEN 'admin' THEN 2 
            WHEN 'creator' THEN 3 
            ELSE 4 
        END, u.created_at DESC";
$users = $pdo->query($sql)->fetchAll();

// Modifier un utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'edit_user') {
        $uid = (int)$_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $xp = (int)$_POST['xp'];
        $level = (int)$_POST['level'];
        $newRole = $_POST['role'] ?? null;
        $group_name = trim($_POST['group_name'] ?? 'Aucun');

        // Vérifier les permissions
        $canEdit = canModifyUser($uid) || $isSuperAdmin;
        $canChangeRole = $newRole ? canChangeRole($uid, $newRole) : false;

        if (!$canEdit) {
            $message = "Vous n'avez pas la permission de modifier cet utilisateur.";
            $messageType = "danger";
        }
        else {
            try {
                // Si changement de rôle et permission
                if ($canChangeRole && $newRole) {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, xp = ?, level = ?, role = ?, group_name = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $xp, $level, $newRole, $group_name, $uid]);
                }
                else {
                    // Mise à jour sans changer le rôle
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, xp = ?, level = ?, group_name = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $xp, $level, $group_name, $uid]);
                }

                // Mettre à jour le mot de passe si fourni
                if (!empty($_POST['new_password'])) {
                    $hashedPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $uid]);
                }

                $message = "Utilisateur mis à jour avec succès !";
                $messageType = "success";

                // Rafraîchir la liste
                $users = $pdo->query($sql)->fetchAll();

            }
            catch (PDOException $e) {
                $message = "Erreur : " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }

    if ($_POST['action'] === 'add_xp') {
        $uid = (int)$_POST['user_id'];
        $addXp = (int)$_POST['add_xp'];

        $stmt = $pdo->prepare("UPDATE users SET xp = xp + ? WHERE id = ?");
        $stmt->execute([$addXp, $uid]);

        // Recalculer le niveau
        $stmt = $pdo->prepare("SELECT xp FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        $currentXp = $stmt->fetchColumn();
        $newLevel = floor($currentXp / 100) + 1;

        $stmt = $pdo->prepare("UPDATE users SET level = ? WHERE id = ?");
        $stmt->execute([$newLevel, $uid]);

        $message = "XP ajoutée ! Nouveau total : " . $currentXp . " XP (Niveau " . $newLevel . ")";
        $messageType = "success";

        $users = $pdo->query($sql)->fetchAll();
    }

    if ($_POST['action'] === 'change_role' && $isSuperAdmin) {
        $uid = (int)$_POST['user_id'];
        $newRole = $_POST['new_role'];

        if (canChangeRole($uid, $newRole)) {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->execute([$newRole, $uid]);
            $message = "Rôle mis à jour avec succès !";
            $messageType = "success";
            $users = $pdo->query($sql)->fetchAll();
        }
        else {
            $message = "Vous ne pouvez pas changer ce rôle.";
            $messageType = "danger";
        }
    }

    // Créer un nouvel utilisateur (admin et superadmin)
    if ($_POST['action'] === 'create_user') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $role = $_POST['role'] ?? 'user';
        $group_name = trim($_POST['group_name'] ?? 'Aucun');

        // Seul le superadmin peut créer des admins/créateurs
        if (!$isSuperAdmin && in_array($role, ['admin', 'creator', 'superadmin'])) {
            $role = 'user';
        }
        // Personne ne peut créer de superadmin
        if ($role === 'superadmin') {
            $role = 'admin';
        }

        if (empty($username) || empty($email) || empty($password)) {
            $message = "Tous les champs obligatoires doivent être remplis.";
            $messageType = "danger";
        }
        elseif (strlen($password) < 8) {
            $message = "Le mot de passe doit contenir au moins 8 caractères.";
            $messageType = "danger";
        }
        else {
            try {
                // Vérifier si l'email ou username existe déjà
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
                $checkStmt->execute([$email, $username]);
                if ($checkStmt->fetch()) {
                    $message = "Cet email ou nom d'utilisateur existe déjà.";
                    $messageType = "danger";
                }
                else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, group_name, xp, level) VALUES (?, ?, ?, ?, ?, 0, 1)");
                    $stmt->execute([$username, $email, $hashedPassword, $role, $group_name]);

                    $message = "Utilisateur créé avec succès !";
                    $messageType = "success";
                    $users = $pdo->query($sql)->fetchAll();
                }
            }
            catch (PDOException $e) {
                $message = "Erreur : " . $e->getMessage();
                $messageType = "danger";
            }
        }
    }

    // Gestion des groupes
    if ($_POST['action'] === 'create_group') {
        $groupName = trim($_POST['group_name'] ?? '');
        if (empty($groupName)) {
            $message = "Le nom du groupe est requis.";
            $messageType = "danger";
        } elseif ($groupName === 'Aucun') {
            $message = "\"Aucun\" est un nom réservé.";
            $messageType = "danger";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO `groups` (`name`) VALUES (?)");
                $stmt->execute([$groupName]);
                $message = "Groupe \"$groupName\" créé avec succès !";
                $messageType = "success";
                // Rafraîchir la liste des groupes
                $groupsStmt = $pdo->query("SELECT id, name FROM `groups` ORDER BY name ASC");
                $dbGroups = $groupsStmt->fetchAll();
                $availableGroups = ['Aucun'];
                foreach ($dbGroups as $g) { $availableGroups[] = $g['name']; }
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate') !== false) {
                    $message = "Ce groupe existe déjà.";
                } else {
                    $message = "Erreur : " . $e->getMessage();
                }
                $messageType = "danger";
            }
        }
    }

    if ($_POST['action'] === 'delete_group') {
        $groupId = (int)$_POST['group_id'];
        if ($groupId) {
            // Récupérer le nom du groupe
            $stmt = $pdo->prepare("SELECT name FROM `groups` WHERE id = ?");
            $stmt->execute([$groupId]);
            $grp = $stmt->fetch();
            if ($grp) {
                // Remettre les utilisateurs à "Aucun"
                $pdo->prepare("UPDATE users SET group_name = 'Aucun' WHERE group_name = ?")->execute([$grp['name']]);
                // Supprimer le groupe
                $pdo->prepare("DELETE FROM `groups` WHERE id = ?")->execute([$groupId]);
                $message = "Groupe \"" . htmlspecialchars($grp['name']) . "\" supprimé. Les utilisateurs ont été déplacés vers \"Aucun\".";
                $messageType = "success";
                // Rafraîchir
                $groupsStmt = $pdo->query("SELECT id, name FROM `groups` ORDER BY name ASC");
                $dbGroups = $groupsStmt->fetchAll();
                $availableGroups = ['Aucun'];
                foreach ($dbGroups as $g) { $availableGroups[] = $g['name']; }
                $users = $pdo->query($sql)->fetchAll();
            } else {
                $message = "Groupe non trouvé.";
                $messageType = "danger";
            }
        }
    }

    // Pattern PRG : redirection après traitement d'un POST pour éviter le renvoi du formulaire au F5
    if ($message !== '') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $messageType;
    }
    header("Location: users.php");
    exit;
}

// Supprimer un utilisateur
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $uid = (int)$_GET['delete'];
    if (canDeleteUser($uid)) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$uid]);
        header("Location: users.php?msg=deleted");
        exit;
    }
    else {
        header("Location: users.php?msg=forbidden");
        exit;
    }
}

// Compter les utilisateurs par rôle
$roleCounts = [
    'superadmin' => 0,
    'admin' => 0,
    'creator' => 0,
    'user' => 0
];
foreach ($users as $u) {
    $role = $u['role'] ?? 'user';
    if (isset($roleCounts[$role])) {
        $roleCounts[$role]++;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Admin CyberSens</title>
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
                
                <?php if (hasPermission('manage_courses')): ?>
                <a href="cours.php" class="nav-item">
                    <i data-lucide="book-open"></i>
                    <span>Gestion Cours</span>
                </a>
                <a href="questions.php" class="nav-item">
                    <i data-lucide="help-circle"></i>
                    <span>Banque Questions</span>
                </a>
                <?php
endif; ?>

                <?php if (hasPermission('manage_content')): ?>
                <a href="news.php" class="nav-item">
                    <i data-lucide="rss"></i>
                    <span>Actualités</span>
                </a>
                <?php
endif; ?>


                
                <?php if (hasPermission('manage_users')): ?>
                <a href="users.php" class="nav-item active">
                    <i data-lucide="users"></i>
                    <span>Utilisateurs</span>
                </a>
                <?php
endif; ?>

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
                    <h1>Utilisateurs</h1>
                    <p class="subtitle">Gestion des comptes et des permissions.</p>
                </div>
                <button class="btn btn-primary" onclick="openCreateModal()">
                    <i data-lucide="user-plus"></i> Créer un utilisateur
                </button>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i data-lucide="<?php echo $messageType === 'success' ? 'check-circle' : 'alert-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
            <?php
endif; ?>

            <!-- Grille de statistiques -->
            <div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="stat-card-admin">
                    <div class="icon-box bg-primary-subtle"><i data-lucide="users"></i></div>
                    <h3><?php echo count($users); ?></h3>
                    <p>TOTAL UTILISATEURS</p>
                </div>
                <?php if ($isSuperAdmin): ?>
                <div class="stat-card-admin">
                    <div class="icon-box bg-danger-subtle" style="color: #ef4444;"><i data-lucide="shield-alert"></i></div>
                    <h3><?php echo $roleCounts['superadmin']; ?></h3>
                    <p>SUPER ADMINS</p>
                </div>
                <div class="stat-card-admin">
                    <div class="icon-box bg-warning-subtle"><i data-lucide="shield"></i></div>
                    <h3><?php echo $roleCounts['admin']; ?></h3>
                    <p>ADMINISTRATEURS</p>
                </div>
                <?php
endif; ?>
                <div class="stat-card-admin">
                    <div class="icon-box bg-success-subtle"><i data-lucide="user"></i></div>
                    <h3><?php echo $roleCounts['user']; ?></h3>
                    <p>UTILISATEURS STD</p>
                </div>
            </div>

            <!-- Tableau des utilisateurs -->
            <div class="admin-table-container">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Utilisateur</th>
                            <th>Rôle</th>
                            <th>Groupe</th>
                            <th>Niveau</th>
                            <th>Progression</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u):
    $userRole = $u['role'] ?? 'user';
    $isProtected = $u['is_protected'] ?? false;
    $canEdit = canModifyUser($u['id']) || $isSuperAdmin;
    $canDelete = canDeleteUser($u['id']);
?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <div style="width: 36px; height: 36px; background: var(--accent-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                                        <?php echo strtoupper(substr($u['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight: 500; color: var(--text-primary);"><?php echo htmlspecialchars($u['username']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);"><?php echo htmlspecialchars($u['email']); ?></div>
                                    </div>
                                    <?php if ($isProtected): ?>
                                    <span class="badge badge-danger" style="margin-left: auto;">PROTÉGÉ</span>
                                    <?php
    endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge-role <?php echo $userRole; ?>">
                                    <?php echo getRoleName($userRole); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge" style="background: var(--bg-tertiary); color: var(--text-secondary);">
                                    <?php echo htmlspecialchars($u['group_name'] ?? 'Aucun'); ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 0.8rem;">
                                    <div style="display: inline-flex; align-items: center; gap: 0.3rem; color: var(--accent-orange); font-weight: 600;">
                                        <i data-lucide="zap" style="width: 14px; height: 14px;"></i> <?php echo $u['xp']; ?>
                                    </div>
                                    <span class="badge badge-purple" style="font-size: 0.7rem;">LVL <?php echo $u['level']; ?></span>
                                </div>
                            </td>
                            <td>
                                <?php echo $u['completed_courses']; ?> cours<br>
                                <small class="text-muted"><?php echo $u['badges_count']; ?> badges</small>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <button class="btn-icon edit" <?php echo $canEdit ? '' : 'disabled'; ?> onclick="openEditModal(<?php echo htmlspecialchars(json_encode($u)); ?>, <?php echo $isSuperAdmin ? 'true' : 'false'; ?>)">
                                        <i data-lucide="pencil"></i>
                                    </button>
                                    <button class="btn-icon xp" onclick="openXpModal(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['username']); ?>')">
                                        <i data-lucide="zap"></i>
                                    </button>

                                    <a href="users.php?delete=<?php echo $u['id']; ?>" class="btn-icon delete" <?php echo $canDelete ? '' : 'style="pointer-events:none; opacity:0.3;"'; ?> onclick="return confirmAction(event, 'Supprimer cet utilisateur ?')">
                                        <i data-lucide="trash-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php
endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Section Gestion des Groupes -->
            <div style="margin-top: 3rem;">
                <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2>Gestion des Groupes</h2>
                        <p class="subtitle">Créer et supprimer des groupes d'utilisateurs.</p>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; margin-top: 1.5rem;">
                    <!-- Formulaire création -->
                    <div style="background: var(--bg-secondary); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--glass-border);">
                        <h3 style="margin-bottom: 1rem; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="plus-circle" style="width: 20px; height: 20px;"></i> Nouveau Groupe</h3>
                        <form method="POST">
                            <input type="hidden" name="action" value="create_group">
                            <div class="form-group">
                                <input type="text" name="group_name" class="form-input" placeholder="Nom du groupe" required>
                            </div>
                            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">Créer le groupe</button>
                        </form>
                    </div>

                    <!-- Liste des groupes -->
                    <div style="background: var(--bg-secondary); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--glass-border);">
                        <h3 style="margin-bottom: 1rem; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="layers" style="width: 20px; height: 20px;"></i> Groupes existants</h3>
                        <?php if (!empty($dbGroups)): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem;">
                            <?php foreach ($dbGroups as $grp): ?>
                            <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-tertiary); padding: 0.5rem 1rem; border-radius: 8px; border: 1px solid var(--glass-border);">
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($grp['name']); ?></span>
                                <form method="POST" style="display: inline; margin: 0;" onsubmit="return confirmAction(event, 'Supprimer le groupe &quot;<?php echo htmlspecialchars($grp['name']); ?>&quot; ? Les utilisateurs seront remis à Aucun.')">
                                    <input type="hidden" name="action" value="delete_group">
                                    <input type="hidden" name="group_id" value="<?php echo $grp['id']; ?>">
                                    <button type="submit" class="btn-icon delete" style="padding: 0.2rem;">
                                        <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p style="color: var(--text-muted);">Aucun groupe créé. Utilisez le formulaire pour en créer.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals -->
    <!-- Edit User Modal -->
    <!-- Modales -->
    <!-- Modale Modifier Utilisateur -->
    <div id="editModal" class="admin-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2><i data-lucide="pencil"></i> Modifier Utilisateur</h2>
                <button class="close-btn" onclick="closeModal('editModal')"><i data-lucide="x"></i></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" id="edit_username" class="form-input" required>
                </div>
                <!-- ... rest of form ... -->
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label class="form-label">XP</label>
                        <input type="number" name="xp" id="edit_xp" class="form-input" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Niveau</label>
                        <input type="number" name="level" id="edit_level" class="form-input" min="1">
                    </div>
                </div>
                
                <div id="edit_role_container" class="form-group" style="display: none;">
                    <label class="form-label">Rôle</label>
                    <select name="role" id="edit_role" class="form-input" style="background: var(--bg-tertiary);">
                        <option value="user">Utilisateur</option>
                        <option value="creator">Créateur</option>
                        <option value="admin">Administrateur</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Groupe</label>
                    <select name="group_name" id="edit_group" class="form-input" style="background: var(--bg-tertiary);">
                        <?php foreach ($availableGroups as $g): ?>
                        <option value="<?php echo $g; ?>"><?php echo $g; ?></option>
                        <?php
endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe (optionnel)</label>
                    <input type="password" name="new_password" class="form-input" placeholder="••••••••">
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Sauvegarder</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modale XP -->
    <div id="xpModal" class="admin-modal">
        <div class="admin-modal-content" style="max-width: 400px;">
            <div class="admin-modal-header">
                <h2><i data-lucide="zap"></i> Ajouter XP</h2>
                <button class="close-btn" onclick="closeModal('xpModal')"><i data-lucide="x"></i></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_xp">
                <input type="hidden" name="user_id" id="xp_user_id">
                
                <p style="margin-bottom: 1.5rem; text-align: center;">Ajouter de l'XP à <strong id="xp_username" class="text-warning">User</strong></p>
                
                <div style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 1.5rem;">
                    <button type="button" class="btn btn-outline btn-sm" onclick="setXp(10)">+10</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="setXp(25)">+25</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="setXp(50)">+50</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="setXp(100)">+100</button>
                </div>

                <div class="form-group">
                    <input type="number" name="add_xp" id="add_xp" class="form-input text-center" style="font-size: 1.5rem;" value="10" required>
                </div>

                <div class="admin-modal-footer">
                    <button type="submit" class="btn btn-primary w-100">Ajouter</button>
                </div>
            </form>
        </div>
    </div>



    <!-- Modale Création -->
    <div id="createModal" class="admin-modal">
        <div class="admin-modal-content">
            <div class="admin-modal-header">
                <h2><i data-lucide="user-plus"></i> Créer Utilisateur</h2>
                <button class="close-btn" onclick="closeModal('createModal')"><i data-lucide="x"></i></button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="create_user">
                
                <div class="form-group">
                    <label class="form-label">Nom d'utilisateur</label>
                    <input type="text" name="username" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mot de passe</label>
                    <input type="password" name="password" class="form-input" required minlength="6">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Rôle</label>
                    <select name="role" class="form-input" style="background: var(--bg-tertiary);">
                        <option value="user">Utilisateur</option>
                        <?php if ($isSuperAdmin): ?>
                        <option value="creator">Créateur</option>
                        <option value="admin">Administrateur</option>
                        <?php
endif; ?>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 2rem;">
                    <label class="form-label">Groupe</label>
                    <select name="group_name" class="form-input" style="background: var(--bg-tertiary);">
                        <?php foreach ($availableGroups as $g): ?>
                        <option value="<?php echo $g; ?>"><?php echo $g; ?></option>
                        <?php
endforeach; ?>
                    </select>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('createModal')">Annuler</button>
                    <button type="submit" class="btn btn-primary">Créer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../../frontend/js/admin/shared.js"></script>
    <script src="../../frontend/js/admin/users.js"></script>
</body>
</html>

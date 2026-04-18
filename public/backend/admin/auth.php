<?php
// Fichier d'authentification admin avec système de rôles
session_start();
require_once '../api/db.php';

// Définition des rôles et permissions
define('ROLE_USER', 'user');
define('ROLE_CREATOR', 'creator');
define('ROLE_ADMIN', 'admin');
define('ROLE_SUPERADMIN', 'superadmin');

// Hiérarchie des rôles (plus le nombre est élevé, plus le rôle a de pouvoir)
$roleHierarchy = [
    ROLE_USER => 1,
    ROLE_CREATOR => 2,
    ROLE_ADMIN => 3,
    ROLE_SUPERADMIN => 4
];

// Permissions par rôle
$rolePermissions = [
    ROLE_USER => [],
    ROLE_CREATOR => ['manage_courses', 'manage_questions', 'view_dashboard', 'manage_content'],
    ROLE_ADMIN => ['manage_users', 'view_dashboard', 'manage_content'],
    ROLE_SUPERADMIN => ['manage_courses', 'manage_questions', 'manage_users', 'manage_roles', 'view_dashboard', 'delete_admins', 'manage_content']
];

// Récupérer le rôle de l'utilisateur connecté
function getCurrentUserRole() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        return $user ? $user['role'] : null;
    } catch (PDOException $e) {
        return null;
    }
}

// Récupérer les infos complètes de l'utilisateur connecté
function getCurrentUser() {
    global $pdo;
    
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, role, is_protected FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return null;
    }
}

// Vérifier si l'utilisateur a une permission spécifique
function hasPermission($permission) {
    global $rolePermissions;
    $role = getCurrentUserRole();
    
    if (!$role) return false;
    
    return in_array($permission, $rolePermissions[$role] ?? []);
}

// Vérifier si l'utilisateur a au moins le rôle requis
function hasMinimumRole($requiredRole) {
    global $roleHierarchy;
    $currentRole = getCurrentUserRole();
    
    if (!$currentRole) return false;
    
    $currentLevel = $roleHierarchy[$currentRole] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;
    
    return $currentLevel >= $requiredLevel;
}

// Vérifier si l'utilisateur peut modifier un autre utilisateur
function canModifyUser($targetUserId) {
    global $pdo;
    $currentUser = getCurrentUser();
    
    if (!$currentUser) return false;
    
    // Super admin peut tout faire
    if ($currentUser['role'] === ROLE_SUPERADMIN) return true;
    
    // On ne peut pas se modifier soi-même via ces fonctions
    if ($currentUser['id'] == $targetUserId) return false;
    
    // Récupérer le rôle de l'utilisateur cible
    $stmt = $pdo->prepare("SELECT role, is_protected FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch();
    
    if (!$targetUser) return false;
    
    // Personne ne peut modifier un utilisateur protégé sauf superadmin
    if ($targetUser['is_protected']) return false;
    
    // Admin peut modifier uniquement les users normaux
    if ($currentUser['role'] === ROLE_ADMIN) {
        return $targetUser['role'] === ROLE_USER;
    }
    
    return false;
}

// Vérifier si l'utilisateur peut supprimer un autre utilisateur
function canDeleteUser($targetUserId) {
    global $pdo;
    $currentUser = getCurrentUser();
    
    if (!$currentUser) return false;
    
    // On ne peut pas se supprimer soi-même
    if ($currentUser['id'] == $targetUserId) return false;
    
    // Récupérer le rôle de l'utilisateur cible
    $stmt = $pdo->prepare("SELECT role, is_protected FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch();
    
    if (!$targetUser) return false;
    
    // On ne peut jamais supprimer un utilisateur protégé
    if ($targetUser['is_protected']) return false;
    
    // Super admin peut supprimer tout le monde sauf les protégés
    if ($currentUser['role'] === ROLE_SUPERADMIN) return true;
    
    // Admin peut supprimer uniquement les users normaux
    if ($currentUser['role'] === ROLE_ADMIN) {
        return $targetUser['role'] === ROLE_USER;
    }
    
    return false;
}

// Vérifier si l'utilisateur peut changer le rôle d'un autre
function canChangeRole($targetUserId, $newRole) {
    global $pdo, $roleHierarchy;
    $currentUser = getCurrentUser();
    
    if (!$currentUser) return false;
    
    // Seul le superadmin peut changer les rôles
    if ($currentUser['role'] !== ROLE_SUPERADMIN) return false;
    
    // On ne peut pas changer son propre rôle
    if ($currentUser['id'] == $targetUserId) return false;
    
    // On ne peut pas promouvoir quelqu'un en superadmin
    if ($newRole === ROLE_SUPERADMIN) return false;
    
    // Récupérer l'utilisateur cible
    $stmt = $pdo->prepare("SELECT is_protected FROM users WHERE id = ?");
    $stmt->execute([$targetUserId]);
    $targetUser = $stmt->fetch();
    
    // On ne peut pas changer le rôle d'un utilisateur protégé
    if ($targetUser && $targetUser['is_protected']) return false;
    
    return true;
}

// Vérifier l'accès admin (créateur, admin ou superadmin)
function checkAdmin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../../index.html?evt=session_expired");
        exit;
    }
    
    if (!hasMinimumRole(ROLE_CREATOR)) {
        header("Location: ../../index.html?evt=forbidden");
        exit;
    }
}

// Vérifier l'accès aux cours (créateur ou superadmin)
function checkCoursesAccess() {
    checkAdmin();
    if (!hasPermission('manage_courses')) {
        header("Location: index.php?error=no_permission");
        exit;
    }
}

// Vérifier l'accès aux utilisateurs (admin ou superadmin)
function checkUsersAccess() {
    checkAdmin();
    if (!hasPermission('manage_users')) {
        header("Location: index.php?error=no_permission");
        exit;
    }
}

// Vérifier l'accès à la gestion des rôles (superadmin uniquement)
function checkRolesAccess() {
    checkAdmin();
    if (!hasPermission('manage_roles')) {
        header("Location: index.php?error=no_permission");
        exit;
    }
}

// Vérifier l'accès au contenu (news) - créateur, admin ou superadmin
function checkContentAccess() {
    checkAdmin();
    if (!hasPermission('manage_content')) {
        header("Location: index.php?error=no_permission");
        exit;
    }
}



// Obtenir le nom lisible d'un rôle
function getRoleName($role) {
    $names = [
        ROLE_USER => 'Utilisateur',
        ROLE_CREATOR => 'Créateur',
        ROLE_ADMIN => 'Administrateur',
        ROLE_SUPERADMIN => 'Super Admin'
    ];
    return $names[$role] ?? 'Inconnu';
}

// Obtenir la couleur Bootstrap d'un rôle
function getRoleColor($role) {
    $colors = [
        ROLE_USER => 'secondary',
        ROLE_CREATOR => 'info',
        ROLE_ADMIN => 'warning',
        ROLE_SUPERADMIN => 'danger'
    ];
    return $colors[$role] ?? 'secondary';
}

// Obtenir l'icône d'un rôle
function getRoleIcon($role) {
    $icons = [
        ROLE_USER => 'person',
        ROLE_CREATOR => 'pencil-square',
        ROLE_ADMIN => 'shield',
        ROLE_SUPERADMIN => 'shield-lock-fill'
    ];
    return $icons[$role] ?? 'person';
}
?>

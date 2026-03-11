// Gestion Utilisateurs
function openEditModal(user, isSuperAdmin) {
    document.getElementById('edit_user_id').value = user.id;
    document.getElementById('edit_username').value = user.username;
    document.getElementById('edit_email').value = user.email;
    document.getElementById('edit_xp').value = user.xp;
    document.getElementById('edit_level').value = user.level;
    document.getElementById('edit_group').value = user.group_name || 'Aucun';

    // Sélection du rôle
    if (document.getElementById('edit_role_container')) {
        const roleSelect = document.getElementById('edit_role');
        if (roleSelect) {
            roleSelect.value = user.role;
            document.getElementById('edit_role_container').style.display = 'block';
        }
    }

    openModal('editModal');
}

function openXpModal(userId, username) {
    document.getElementById('xp_user_id').value = userId;
    document.getElementById('xp_username').textContent = username;
    openModal('xpModal');
}

function setXp(amount) {
    document.getElementById('add_xp').value = amount;
}

function openRoleModal(userId, username, currentRole) {
    document.getElementById('role_user_id').value = userId;
    document.getElementById('role_username').textContent = username;

    // Sélectionner le rôle actuel
    const radios = document.getElementsByName('new_role');
    for (let r of radios) {
        if (r.value === currentRole) r.click();
    }

    openModal('roleModal');
}

function selectRole(role) {
    // Logique visuelle si nécessaire
    document.querySelectorAll('.role-option').forEach(el => el.classList.remove('selected'));
    const input = document.querySelector(`input[name="new_role"][value="${role}"]`);
    if (input) {
        input.checked = true;
        input.closest('.role-option').classList.add('selected');
    }
}

function openCreateModal() {
    openModal('createModal');
}

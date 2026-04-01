// CLIENT API (Remplace la DB LocalStorage)
const API_URL = 'backend/api';

// Fonction utilitaire pour convertir le niveau numérique en texte
function getLevelName(level) {
    const levels = {
        1: 'Novice',
        2: 'Initié',
        3: 'Confirmé',
        4: 'Expert',
        5: 'Maître',
        6: 'Élite',
        7: 'Légende'
    };
    return levels[level] || `Niveau ${level}`;
}

// XP requis pour le prochain niveau
function getXpForNextLevel(level) {
    const requirements = {
        1: 100,
        2: 300,
        3: 600,
        4: 1000,
        5: 1500,
        6: 2500,
        7: Infinity
    };
    return requirements[level] || Infinity;
}

class ApiClient {

    async login(email, password) {
        try {
            const response = await fetch(`${API_URL}/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            return await response.json();
        } catch (e) {
            console.error("Erreur Login:", e);
            return { success: false, message: "Erreur de connexion au serveur" };
        }
    }

    async register(username, email, password) {
        try {
            const response = await fetch(`${API_URL}/register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email, password })
            });
            return await response.json();
        } catch (e) {
            console.error("Erreur Register:", e);
            return { success: false, message: "Erreur de connexion au serveur" };
        }
    }

    async getUsers() {
        try {
            const response = await fetch(`${API_URL}/users.php`);
            return await response.json();
        } catch (e) {
            console.error("Erreur GetUsers:", e);
            return [];
        }
    }

    async getUser(id) {
        try {
            const response = await fetch(`${API_URL}/users.php?id=${id}`);
            const data = await response.json();
            return data.success ? data.user : null;
        } catch (e) {
            console.error("Erreur GetUser:", e);
            return null;
        }
    }

    async deleteUser(id) {
        try {
            await fetch(`${API_URL}/users.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            return true;
        } catch (e) {
            return false;
        }
    }

    async updateUserGroup(id, groupName) {
        try {
            await fetch(`${API_URL}/users.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, group: groupName })
            });
            return true;
        } catch (e) {
            return false;
        }
    }

    async addXp(userId, xpAmount) {
        try {
            const response = await fetch(`${API_URL}/users.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add_xp', user_id: userId, xp: xpAmount })
            });
            return await response.json();
        } catch (e) {
            console.error("Erreur AddXP:", e);
            return { success: false };
        }
    }

    async updateAvatar(userId, avatar) {
        try {
            const response = await fetch(`${API_URL}/users.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_avatar', user_id: userId, avatar: avatar })
            });
            return await response.json();
        } catch (e) {
            console.error("Erreur UpdateAvatar:", e);
            return { success: false };
        }
    }

    async getLeaderboard(groupName = null) {
        try {
            let url = `${API_URL}/leaderboard.php`;
            if (groupName && groupName !== 'Aucun') url += `?group_name=${encodeURIComponent(groupName)}`;
            const response = await fetch(url);
            return await response.json();
        } catch (e) {
            return [];
        }
    }

    async getGroups() {
        try {
            const response = await fetch(`${API_URL}/groups.php`);
            return await response.json();
        } catch (e) {
            return { success: false, groups: [] };
        }
    }


    async updateProfile(userId, username, email) {
        try {
            const response = await fetch(`${API_URL}/users.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId, username, email })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    async updatePassword(userId, newPassword) {
        try {
            const response = await fetch(`${API_URL}/users.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: userId, action: 'update_password', password: newPassword })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    // API COURS
    async getCourses(userId = null, userRole = null) {
        try {
            let url = `${API_URL}/courses.php`;
            const params = [];
            if (userId) params.push(`user_id=${userId}`);
            if (userRole) params.push(`role=${userRole}`);
            if (params.length > 0) url += '?' + params.join('&');

            const response = await fetch(url);
            const data = await response.json();
            return data.success ? data.courses : [];
        } catch (e) {
            console.error("Erreur GetCourses:", e);
            return [];
        }
    }

    async getCourse(id) {
        try {
            const response = await fetch(`${API_URL}/courses.php?id=${id}`);
            const data = await response.json();
            return data.success ? data.course : null;
        } catch (e) {
            console.error("Erreur GetCourse:", e);
            return null;
        }
    }

    async createCourse(courseData) {
        try {
            const response = await fetch(`${API_URL}/courses.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(courseData)
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    async updateCourse(courseData) {
        try {
            const response = await fetch(`${API_URL}/courses.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(courseData)
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    async deleteCourse(id) {
        try {
            const response = await fetch(`${API_URL}/courses.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    // API QUESTIONS
    async getQuestions(courseId = null) {
        try {
            let url = `${API_URL}/questions.php`;
            if (courseId) url += `?course_id=${courseId}`;
            const response = await fetch(url);
            const data = await response.json();
            return data.success ? data.questions : [];
        } catch (e) {
            console.error("Erreur GetQuestions:", e);
            return [];
        }
    }

    async createQuestion(questionData) {
        try {
            const response = await fetch(`${API_URL}/questions.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(questionData)
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    async updateQuestion(questionData) {
        try {
            const response = await fetch(`${API_URL}/questions.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(questionData)
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    async deleteQuestion(id) {
        try {
            const response = await fetch(`${API_URL}/questions.php`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id })
            });
            return await response.json();
        } catch (e) {
            return { success: false, message: "Erreur serveur" };
        }
    }

    // API STATS
    async getStats() {
        try {
            const response = await fetch(`${API_URL}/stats.php`);
            const data = await response.json();
            return data.success ? data.stats : null;
        } catch (e) {
            console.error("Erreur GetStats:", e);
            return null;
        }
    }

    // API ADMINISTRATION UTILISATEURS
    async toggleUserAdmin(id) {
        try {
            const response = await fetch(`${API_URL}/users.php`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, toggle_admin: true })
            });
            return await response.json();
        } catch (e) {
            return { success: false };
        }
    }

    // API PROGRESSION
    async saveProgression(userId, courseId, completed, score = null) {
        try {
            const data = { user_id: userId, course_id: courseId, completed };
            if (score !== null) {
                data.score = score;
            }
            const response = await fetch(`${API_URL}/progression.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            return await response.json();
        } catch (e) {
            return { success: false };
        }
    }

    async getProgression(userId) {
        try {
            const response = await fetch(`${API_URL}/progression.php?user_id=${userId}`);
            const data = await response.json();
            return data.success ? data.progression : [];
        } catch (e) {
            return [];
        }
    }

    // API BADGES
    async getBadges(userId) {
        try {
            const response = await fetch(`${API_URL}/badges.php?user_id=${userId}`);
            const data = await response.json();
            return data.success ? data : { badges: [], unlocked: [] };
        } catch (e) {
            return { badges: [], unlocked: [] };
        }
    }

    async checkBadges(userId) {
        try {
            const response = await fetch(`${API_URL}/badges.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            return await response.json();
        } catch (e) {
            return { success: false, new_badges: [] };
        }
    }

    // API CERTIFICATS
    async getCertificates(userId) {
        try {
            const response = await fetch(`${API_URL}/certificates.php?user_id=${userId}`);
            const data = await response.json();
            return data.success ? data.certificates : [];
        } catch (e) {
            return [];
        }
    }

    async generateCertificate(userId, courseId, score) {
        try {
            const response = await fetch(`${API_URL}/certificates.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, course_id: courseId, score })
            });
            return await response.json();
        } catch (e) {
            return { success: false };
        }
    }

    async verifyCertificate(code) {
        try {
            const response = await fetch(`${API_URL}/certificates.php?code=${code}`);
            return await response.json();
        } catch (e) {
            return { success: false, valid: false };
        }
    }

    // API PHISHING
    async getPhishingScenarios(userId = null) {
        try {
            let url = `${API_URL}/phishing.php`;
            if (userId) url += `?user_id=${userId}`;
            const response = await fetch(url);
            const data = await response.json();
            return data.success ? data.scenarios : [];
        } catch (e) {
            return [];
        }
    }

    async getPhishingScenario(scenarioId) {
        try {
            const response = await fetch(`${API_URL}/phishing.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'get_scenario', scenario_id: scenarioId })
            });
            const data = await response.json();
            return data.success ? data.scenario : null;
        } catch (e) {
            return null;
        }
    }

    async submitPhishingAnswer(userId, scenarioId, isPhishing, timeTaken) {
        try {
            const response = await fetch(`${API_URL}/phishing.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'answer',
                    user_id: userId,
                    scenario_id: scenarioId,
                    is_phishing: isPhishing,
                    time_taken: timeTaken
                })
            });
            return await response.json();
        } catch (e) {
            return { success: false };
        }
    }

    async getPhishingStats(userId) {
        try {
            const response = await fetch(`${API_URL}/phishing.php?action=stats&user_id=${userId}`);
            const data = await response.json();
            return data.success ? data : { stats: {}, completed_scenarios: [] };
        } catch (e) {
            return { stats: {}, completed_scenarios: [] };
        }
    }



    // API NOTIFICATIONS
    async getNotifications(userId, unreadOnly = false) {
        try {
            let url = `${API_URL}/notifications.php?user_id=${userId}`;
            if (unreadOnly) url += '&unread=true';
            const response = await fetch(url);
            const data = await response.json();
            return data.success ? data : { notifications: [], unread_count: 0 };
        } catch (e) {
            return { notifications: [], unread_count: 0 };
        }
    }

    async markNotificationsRead(userId, notificationId = null) {
        try {
            const response = await fetch(`${API_URL}/notifications.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'mark_read',
                    user_id: userId,
                    notification_id: notificationId
                })
            });
            return await response.json();
        } catch (e) {
            return { success: false };
        }
    }

    async createNotification(userId, title, message, type = 'success', link = null) {
        try {
            const response = await fetch(`${API_URL}/notifications.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userId,
                    title: title,
                    message: message,
                    type: type,
                    link: link
                })
            });
            return await response.json();
        } catch (e) {
            return { success: false };
        }
    }
}

const api = new ApiClient();

// SYSTÈME DE NOTIFICATIONS TOAST
function showToast(title, message, type = 'success', duration = 5000) {
    // Créer le container s'il n'existe pas
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }

    // Icône selon le type
    const icons = {
        success: 'check-circle',
        unlock: 'unlock',
        achievement: 'trophy',
        levelup: 'trending-up',
        info: 'info',
        warning: 'alert-triangle',
        error: 'x-circle'
    };

    // Couleurs selon le type
    const colors = {
        success: '#10b981',
        unlock: '#00f3ff',
        achievement: '#ffe600',
        levelup: '#bc13fe',
        info: '#3b82f6',
        warning: '#f59e0b',
        error: '#ef4444'
    };

    const icon = icons[type] || icons.info;
    const color = colors[type] || colors.info;

    // Créer le toast
    const toast = document.createElement('div');
    const bgStyle = type === 'error' ? `rgba(220, 38, 38, 0.9)` : `var(--bg-panel)`;

    toast.className = 'toast-notification';
    toast.style.cssText = `
        background: ${bgStyle};
        backdrop-filter: blur(20px);
        border: 1px solid ${color}40;
        border-left: 4px solid ${color};
        border-radius: 12px;
        padding: 1rem 1.25rem;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.5), 0 0 20px ${color}20;
        animation: slideInRight 0.4s ease-out;
        cursor: pointer;
        transition: transform 0.2s, opacity 0.3s;
    `;

    toast.innerHTML = `
        <div style="color: ${color}; flex-shrink: 0;">
            <i data-lucide="${icon}" style="width: 24px; height: 24px;"></i>
        </div>
        <div style="flex: 1;">
            <div style="font-weight: 600; color: #fff; margin-bottom: 4px;">${title}</div>
            <div style="font-size: 0.9rem; color: #aaa; line-height: 1.4;">${message}</div>
        </div>
        <button style="background: none; border: none; color: #666; cursor: pointer; padding: 0; flex-shrink: 0;" onclick="this.parentElement.remove()">
            <i data-lucide="x" style="width: 18px; height: 18px;"></i>
        </button>
    `;

    // Ajouter au container
    container.appendChild(toast);
    lucide.createIcons();

    // Hover effect
    toast.addEventListener('mouseenter', () => {
        toast.style.transform = 'translateX(-5px)';
    });
    toast.addEventListener('mouseleave', () => {
        toast.style.transform = 'translateX(0)';
    });

    // Auto-dismiss
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, duration);

    // Click to dismiss
    toast.addEventListener('click', (e) => {
        if (e.target.tagName !== 'BUTTON') {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }
    });
}

// Ajouter les styles d'animation au document
const toastStyles = document.createElement('style');
toastStyles.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
`;
document.head.appendChild(toastStyles);

// SYSTÈME DE PANNEAU DE NOTIFICATIONS
let notificationsOpen = false;

async function loadNotifications() {
    const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
    const notifBtn = document.getElementById('notifications-btn');
    const badge = document.getElementById('notification-badge');

    if (!currentUser) {
        // Ne pas masquer le bouton, juste arrêter la fonction
        // if (notifBtn) notifBtn.style.display = 'none'; 
        return;
    }

    // Afficher le bouton
    if (notifBtn) notifBtn.style.display = 'flex';

    // Charger les notifications
    const data = await api.getNotifications(currentUser.id);

    // Mettre à jour le badge
    if (badge) {
        if (data.unread_count > 0) {
            badge.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }

    // Mettre à jour la liste
    const list = document.getElementById('notifications-list');
    if (list) {
        if (data.notifications && data.notifications.length > 0) {
            list.innerHTML = data.notifications.map(n => {
                const iconType = n.type || 'info';
                const icons = {
                    success: 'check-circle',
                    unlock: 'unlock',
                    achievement: 'trophy',
                    levelup: 'trending-up',
                    info: 'info',
                    warning: 'alert-triangle'
                };
                const icon = icons[iconType] || 'bell';
                const timeAgo = getTimeAgo(new Date(n.created_at));

                return `
                    <div class="notification-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                        <div class="notification-icon ${iconType}">
                            <i data-lucide="${icon}" style="width: 18px; height: 18px;"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${n.title}</div>
                            <div class="notification-message">${n.message}</div>
                            <div class="notification-time">${timeAgo}</div>
                        </div>
                    </div>
                `;
            }).join('');
            lucide.createIcons();
        } else {
            list.innerHTML = `
                <div class="notifications-empty">
                    <p>Aucune notification</p>
                </div>
            `;
            lucide.createIcons();
        }
    }
}

function getTimeAgo(date) {
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);

    if (diff < 60) return 'À l\'instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
    if (diff < 604800) return `Il y a ${Math.floor(diff / 86400)} j`;
    return date.toLocaleDateString('fr-FR');
}

function toggleNotificationsPanel() {
    const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
    if (!currentUser) {
        return;
    }

    const panel = document.getElementById('notifications-panel');
    notificationsOpen = !notificationsOpen;

    if (panel) {
        panel.style.display = notificationsOpen ? 'block' : 'none';
        if (notificationsOpen) {
            loadNotifications();
        }
    }
}

async function markAllNotificationsRead() {
    const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
    if (!currentUser) return;

    await api.markNotificationsRead(currentUser.id);
    loadNotifications();
    showToast('Notifications', 'Toutes les notifications ont été marquées comme lues', 'info', 3000);
}

// Nouvelle fonction pour tout supprimer
async function deleteAllNotifications() {
    const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
    if (!currentUser) return;

    if (!await showConfirmModal('Supprimer Notifications', 'Supprimer définitivement l\'historique ?', 'Cette action est irréversible.')) {
        return;
    }

    try {
        const response = await fetch(`${API_URL}/notifications.php`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                user_id: currentUser.id,
                delete_all: true
            })
        });

        const result = await response.json();

        if (result.success) {
            loadNotifications();
            showToast('Notifications', 'Historique des notifications effacé', 'success', 3000);
        } else {
            showToast('Erreur', 'Impossible de supprimer les notifications', 'error');
        }
    } catch (e) {
        console.error(e);
        showToast('Erreur', 'Erreur de connexion', 'error');
    }
}

// Rendre la fonction accessible globalement
window.deleteAllNotifications = deleteAllNotifications;
window.markAllNotificationsRead = markAllNotificationsRead;

// Fermer le panneau en cliquant ailleurs
document.addEventListener('click', (e) => {
    const panel = document.getElementById('notifications-panel');
    const btn = document.getElementById('notifications-btn');

    if (notificationsOpen && panel && btn) {
        if (!panel.contains(e.target) && !btn.contains(e.target)) {
            notificationsOpen = false;
            panel.style.display = 'none';
        }
    }
});

// LOGIQUE VUE QUIZ
async function initQuizView() {
    const startBtn = document.getElementById('start-sudden-death-btn');
    if (!startBtn) return;

    startBtn.addEventListener('click', () => {
        if (typeof window.startSuddenDeathQuiz === 'function') {
            window.startSuddenDeathQuiz();
        } else {
            console.error("La fonction startSuddenDeathQuiz n'est pas définie");
        }
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    // Gestion des retours d'erreur session de l'admin (PHP -> JS)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('evt') === 'session_expired') {
        sessionStorage.removeItem('currentUser');
        alert("Votre session administrateur a expiré. Veuillez vous reconnecter.");
        // Nettoyer l'URL
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    // Vérifier si on exécute via protocole file://
    if (window.location.protocol === 'file:') {
        alert("⚠️ ATTENTION : Vous avez ouvert le fichier directement.\n\nPour que la base de données fonctionne, vous devez passer par votre serveur WAMP (http://localhost/Cybersens).");
    }

    // Initialiser les icônes Lucide
    lucide.createIcons();

    // Configurer le bouton de notifications
    const notifBtn = document.getElementById('notifications-btn');
    if (notifBtn) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleNotificationsPanel();
        });
    }

    // Charger les notifications pour l'utilisateur connecté
    loadNotifications();

    const contentArea = document.getElementById('content-area');
    const navItems = document.querySelectorAll('.nav-item');

    // NAVIGATION ET CHARGEMENT DES TEMPLATES

    let statsInterval = null; // Variable pour stocker l'intervalle de rafraichissement

    async function loadHomeStats(isUpdate = false) {
        try {
            const response = await fetch(`${API_URL}/stats.php`);
            const data = await response.json();

            if (data.success) {
                const s = data.stats;

                // Helper pour l'animation de valeur
                const animateValue = (id, target, duration, suffix = '') => {
                    const obj = document.getElementById(id);
                    if (!obj) return;

                    // Si mode mise à jour (rafraichissement silencieux), récupérer valeur actuelle pour départ
                    // Sinon (chargement page), partir de 0
                    let start = 0;
                    if (isUpdate) {
                        const currentText = obj.innerText.replace(/[^0-9]/g, ''); // Enlever le texte non numérique
                        start = parseInt(currentText) || 0;
                        if (start === target) return; // Pas de changement, pas d'animation
                    }

                    let startTimestamp = null;
                    const step = (timestamp) => {
                        if (!startTimestamp) startTimestamp = timestamp;
                        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                        obj.innerHTML = Math.floor(progress * (target - start) + start) + suffix;
                        if (progress < 1) {
                            window.requestAnimationFrame(step);
                        }
                    };
                    window.requestAnimationFrame(step);
                };

                // Mettre à jour les stats avec animation
                if (s.questions) animateValue("stat-questions", s.questions, 1000);
                if (s.courses) animateValue("stat-courses", s.courses, 1000);
                if (s.successRate) animateValue("stat-success-rate", s.successRate, 1000, "%");
                if (s.certificates) animateValue("stat-certificates", s.certificates, 1000);

            }
        } catch (e) {
            console.error("Erreur chargement stats home:", e);
        }
    }

    async function loadRealNews() {
        const newsFeed = document.getElementById('news-feed');
        const tickerContainer = document.getElementById('attacks-ticker');
        const logContainer = document.getElementById('attacks-log');

        // On continue même si newsFeed n'existe pas, car on veut peut-être juste le ticker
        if (!newsFeed && !tickerContainer && !logContainer) return;

        try {
            const response = await fetch(`${API_URL}/news.php`);
            const data = await response.json();

            // Optim: Si les données n'ont pas changé ET que le ticker est déjà affiché, on ne touche pas au DOM
            const currentDataStr = JSON.stringify(data.news);
            // Vérifier si le ticker est déjà rendu (contient la classe ticker-track)
            const isTickerRendered = tickerContainer && tickerContainer.querySelector('.ticker-track');

            if (window.lastNewsData === currentDataStr && isTickerRendered) return;
            window.lastNewsData = currentDataStr;

            if (data.success && data.news.length > 0) {
                let newsHTML = '';
                let logHTML = '';

                // Fonction helper pour la date
                const formatDate = (timestamp) => {
                    return new Date(timestamp * 1000).toLocaleDateString('fr-FR', {
                        day: 'numeric', month: 'long', hour: '2-digit', minute: '2-digit'
                    });
                };

                const formatLogDate = (timestamp) => {
                    const d = new Date(timestamp * 1000);
                    return `[${d.getDate().toString().padStart(2, '0')}/${(d.getMonth() + 1).toString().padStart(2, '0')}]`;
                };

                // 1. Ticker (Comme avant, liste complète pour l'effet)
                if (tickerContainer) {
                    const fullList = [...data.news, ...data.news];
                    const itemsHTML = fullList.map(item => `
                        <a href="${item.link || '#'}" target="_blank" class="ticker-item" style="text-decoration: none; color: inherit; display: inline-block;">
                            <span style="font-weight:600; margin-right:5px;">${new Date(item.date * 1000).toLocaleDateString('fr-FR')} : </span>
                            <span style="color:inherit; border-bottom:1px dotted var(--text-muted);">${item.title}</span>
                        </a>
                    `).join('');
                    tickerContainer.innerHTML = `<div class="ticker-track">${itemsHTML}</div>`;
                }

                // 2. Feed Principal (UNIQUEMENT LE TOP 3)
                if (newsFeed) {
                    // On prend les 3 premiers items seulement pour ne pas alourdir
                    const topNews = data.news.slice(0, 3);

                    topNews.forEach(item => {
                        let icon = 'alert-triangle';
                        let colorStyle = "color: var(--danger);";
                        if (item.source.includes('Fuite')) { icon = 'database'; colorStyle = "color: #ff6b6b;"; }
                        if (item.source.includes('Rançongiciel')) { icon = 'lock'; colorStyle = "color: #fca5a5;"; }
                        if (item.source.includes('Piratage')) { icon = 'skull'; colorStyle = "color: #ef4444;"; }

                        newsHTML += `
                            <a href="${item.link || '#'}" target="_blank" class="news-card type-external" style="border-color: rgba(220,38,38,0.3); text-decoration: none; color: inherit; display: block; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(220,38,38,0.2)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
                                <div class="news-header">
                                    <div class="news-icon" style="background: rgba(220, 38, 38, 0.1); ${colorStyle}">
                                        <i data-lucide="${icon}"></i>
                                    </div>
                                    <span style="font-weight:600; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; ${colorStyle}">${item.source}</span>
                                </div>
                                <div class="news-content">
                                    <h3 style="font-size:1rem; line-height:1.4;">${item.title}</h3>
                                    <p style="font-size:0.9rem; opacity:0.8; margin-top: 0.5rem;">${item.description}</p>
                                </div>
                                <div class="news-time" style="margin-top: 1rem;">
                                    <i data-lucide="calendar" style="width:12px; height:12px; display:inline-block; vertical-align:middle;"></i>
                                    ${formatDate(item.date)}
                                </div>
                            </a>
                        `;
                    });
                    newsFeed.innerHTML = newsHTML;
                }

                // 3. Journal des Attaques (TOUT LE RESTE + LE TOP 3) -> Liste complète
                if (logContainer) {
                    // Pour montrer "tout ce qu'il y a eu"
                    data.news.forEach(item => {
                        let sourceClass = "";
                        if (item.source.includes('Piratage') || item.source.includes('Rançongiciel')) sourceClass = "danger";

                        logHTML += `
                            <div class="log-entry">
                                <span class="log-date">${formatLogDate(item.date)}</span>
                                <span class="log-source ${sourceClass}">${item.source}</span><br>
                                <a href="${item.link || '#'}" target="_blank" class="log-link" style="color: var(--primary-color); text-decoration: none;">> ${item.title}</a>
                            </div>
                        `;
                    });
                    logContainer.innerHTML = logHTML;
                }


                lucide.createIcons();
            } else {
                if (newsFeed) newsFeed.innerHTML = '<p style="text-align: center; color: var(--text-muted);">Aucune actualité récente trouvée.</p>';
            }
        } catch (e) {
            console.error("Erreur news:", e);
        }
    }

    async function loadTemplate(viewId) {
        // Arrêter l'intervalle de statistiques s'il existe (quand on quitte l'accueil)
        if (statsInterval) {
            clearInterval(statsInterval);
            statsInterval = null;
        }

        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        
        // Restriction d'accès : Si non connecté et essaie d'accéder à autre chose que profil ou home
        if (!sessionUser && viewId !== 'profil' && viewId !== 'home') {
            viewId = 'profil';
        }

        try {
            const response = await fetch(`frontend/templates/${viewId}.html?t=${Date.now()}`);
            if (!response.ok) throw new Error('Template not found');
            const html = await response.text();
            contentArea.innerHTML = html;

            // Réinitialiser les icônes pour le nouveau contenu
            lucide.createIcons();

            // Initialiser la logique de vue spécifique
            if (viewId === 'profil') initAuth();
            if (viewId === 'leaderboard') loadLeaderboards();

            if (viewId === 'home') {
                loadHomeStats(); // Premier chargement
                loadRealNews(); // Charger les actus externes
                // Rafraichir les stats et news toutes les 10 secondes si l'utilisateur reste sur la page
                statsInterval = setInterval(() => {
                    loadHomeStats(true);
                    loadRealNews();
                }, 10000);
            }

            // On garde l'effet tilt pour le quiz
            if (viewId === 'home' || viewId === 'cours' || viewId === 'quiz') initTiltEffect();

            if (viewId === 'admin') initAdminPanel();
            if (viewId === 'cours') loadCoursesView();

            // APPEL DE LA FONCTION POUR CHARGER LE QUIZ
            if (viewId === 'quiz') initQuizView();

            if (viewId === 'phishing') initPhishingView();

            if (viewId === 'parametres') initParametresView();

            // Mettre à jour l'état actif dans la barre latérale
            navItems.forEach(item => {
                if (item.dataset.view === viewId) item.classList.add('active');
                else item.classList.remove('active');
            });

        } catch (error) {
            console.error('Erreur de chargement du template:', error);
            contentArea.innerHTML = '<h1>Erreur 404</h1><p>Impossible de charger le contenu.</p>';
        }
    }

    // Ajouter les écouteurs de clic
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            // Pour le lien Admin (balise A avec href)
            if (item.tagName === 'A' && item.getAttribute('href')) {
                const href = item.getAttribute('href');
                // On force la navigation pour être sûr
                if (href && href !== '#') {
                    window.location.href = href;
                    e.preventDefault();
                    e.stopPropagation();
                }
                return;
            }

            const viewId = item.dataset.view;
            if (viewId) {
                loadTemplate(viewId);
            }
        });
    });

    // ÉCOUTEUR GLOBAL POUR LE BOUTON DÉCONNEXION (DELEGATION)
    window.customLogout = async function(e) {
        if (e) e.preventDefault();
        
        // 1. DÉTRUIRE LA SESSION PHP CÔTÉ SERVEUR (corrige l'accès admin après déco)
        try {
            await fetch(`${API_URL}/logout.php?ajax=1`, {
                method: 'GET',
                credentials: 'same-origin',  // Envoyer le cookie PHPSESSID
                headers: { 'Accept': 'application/json' }
            });
        } catch (err) {
            console.warn('Erreur lors de la déconnexion serveur:', err);
        }
        
        // 2. Nettoyer le stockage côté client
        sessionStorage.removeItem('currentUser');
        
        // 3. Cacher le lien admin
        const adminNavItem = document.querySelector('.nav-item.admin-only');
        if (adminNavItem) adminNavItem.style.display = 'none';
        
        // 4. Mettre à jour l'UI
        if (typeof window.updateSidebarUser === 'function') {
            window.updateSidebarUser(null);
        }
        document.body.classList.add('auth-restricted');
        if (typeof window.loadTemplate === 'function') {
            window.loadTemplate('home');
        } else if (typeof loadTemplate === 'function') {
            loadTemplate('home');
        }
    };

    document.addEventListener('click', (e) => {
        // Gérer le clic sur le bouton lui-même ou sur l'icône enfant
        const logoutBtn = e.target.closest('#logout-btn');
        if (logoutBtn) {
            window.customLogout(e);
        }
    });

    // Vérifier si l'utilisateur est déjà connecté et afficher la nav admin si applicable
    const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
    if (sessionUser) {
        document.body.classList.remove('auth-restricted');
        // Afficher la nav admin pour les rôles admin
        if (['admin', 'superadmin', 'creator'].includes(sessionUser.role)) {
            const adminNavItem = document.querySelector('.nav-item.admin-only');
            if (adminNavItem) {
                adminNavItem.style.display = 'flex';
            }
        }

        // Mettre à jour les infos utilisateur de la barre latérale
        updateSidebarUser(sessionUser);
        
        // Charger l'accueil par défaut pour les connectés
        loadTemplate('home');
    } else {
        // Non connecté : restreindre et afficher l'accueil par défaut
        document.body.classList.add('auth-restricted');
        loadTemplate('home');
    }


    // MISE À JOUR UTILISATEUR BARRE LATÉRALE

    function updateSidebarUser(user) {
        const sidebarUser = document.getElementById('sidebar-user');
        const sidebarAvatar = document.getElementById('sidebar-avatar');
        const sidebarUsername = document.getElementById('sidebar-username');
        const sidebarRole = document.getElementById('sidebar-role');

        if (sidebarUser && user) {
            sidebarUser.style.display = 'flex';
            if (sidebarAvatar) {
                sidebarAvatar.textContent = user.username ? user.username.charAt(0).toUpperCase() : 'U';
            }
            if (sidebarUsername) {
                sidebarUsername.textContent = user.username || 'Utilisateur';
            }
            if (sidebarRole) {
                const roleLabels = {
                    'user': 'Membre',
                    'creator': 'Créateur',
                    'admin': 'Administrateur',
                    'superadmin': 'Super Admin'
                };
                sidebarRole.textContent = roleLabels[user.role] || 'Membre';
            }
        } else if (sidebarUser) {
            sidebarUser.style.display = 'none';
        }
    }

    // Rendre la fonction accessible globalement
    window.updateSidebarUser = updateSidebarUser;


    // EFFETS UI

    function initTiltEffect() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;

                const centerX = rect.width / 2;
                const centerY = rect.height / 2;

                const rotateX = ((y - centerY) / centerY) * -5;
                const rotateY = ((x - centerX) / centerX) * 5;

                card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.02)`;
            });

            card.addEventListener('mouseleave', () => {
                card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) scale(1)';
            });
        });
    }


    // LOGIQUE D'AUTHENTIFICATION
    function initAuth() {
        const authContainer = document.getElementById('auth-container');
        const userDashboard = document.getElementById('user-dashboard');

        const loginView = document.getElementById('login-view');
        const signupView = document.getElementById('signup-view');
        const loginToggleContent = document.getElementById('login-toggle-content');
        const signupToggleContent = document.getElementById('signup-toggle-content');

        // Vérifier la session
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        if (sessionUser) {
            updateUI(sessionUser);
        }

        // Basculer les vues
        const showSignupBtn = document.getElementById('show-signup-btn');
        const showLoginBtn = document.getElementById('show-login-btn');

        if (showSignupBtn) {
            showSignupBtn.addEventListener('click', (e) => {
                e.preventDefault();
                loginView.style.display = 'none';
                signupView.style.display = 'block';
            });
        }

        if (showLoginBtn) {
            showLoginBtn.addEventListener('click', (e) => {
                e.preventDefault();
                signupView.style.display = 'none';
                loginView.style.display = 'block';
            });
        }

        // Gérer la connexion
        document.getElementById('login-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const errorMsg = document.getElementById('login-error');

            const result = await api.login(email, password);

            if (result.success) {
                loginUser(result.user);
                errorMsg.style.display = 'none';
            } else {
                errorMsg.textContent = result.message;
                errorMsg.style.display = 'block';
            }
        });

        // Gérer l'inscription
        document.getElementById('signup-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = document.getElementById('signup-username').value;
            const email = document.getElementById('signup-email').value;
            const password = document.getElementById('signup-password').value;
            const errorMsg = document.getElementById('signup-error');

            const result = await api.register(username, email, password);

            if (result.success) {
                loginUser(result.user);
                errorMsg.style.display = 'none';
            } else {
                errorMsg.textContent = result.message;
                errorMsg.style.display = 'block';
            }
        });

        function loginUser(user) {
            sessionStorage.setItem('currentUser', JSON.stringify(user));
            document.body.classList.remove('auth-restricted');
            updateUI(user);
            // Charger les notifications
            loadNotifications();
            // Rediriger vers l'accueil après connexion
            loadTemplate('home');
        }

        async function updateUI(user) {
            if (!authContainer) return;

            // Masquer Auth, Afficher Dashboard
            authContainer.style.display = 'none';
            userDashboard.style.display = 'block';

            // Mise à jour des globales MÊME SI la page profil a disparu
            if (user.role === 'admin' || user.role === 'creator' || user.role === 'superadmin') {
                const adminNavItem = document.querySelector('.nav-item.admin-only');
                if (adminNavItem) {
                    adminNavItem.style.display = 'flex';
                }
            }

            if (typeof window.updateSidebarUser === 'function') {
                window.updateSidebarUser(user);
            }

            // Rafraîchir les données depuis le serveur pour avoir l'XP exact à jour
            try {
                const freshUser = await api.getUser(user.id);
                if (freshUser) {
                    user = freshUser;
                    sessionStorage.setItem('currentUser', JSON.stringify(user));
                }
            } catch (e) {
                console.error("Erreur rafraîchissement profil:", e);
            }

            // Mettre à jour les infos utilisateur (avec vérification si on est sur la vue profil)
            const userNameDisplay = document.getElementById('user-name-display');
            if (userNameDisplay) {
                userNameDisplay.textContent = user.username;
                const levelNum = parseInt(user.level) || 1;
                const xp = parseInt(user.xp) || 0;
                
                const userLevelDisplay = document.getElementById('user-level-display');
                if (userLevelDisplay) userLevelDisplay.textContent = getLevelName(levelNum);
                
                const userXpDisplay = document.getElementById('user-xp-display');
                if (userXpDisplay) userXpDisplay.textContent = xp;

                // Mettre à jour la barre de progression XP
                updateXpProgressBar(xp, levelNum);

                // Charger les badges et certificats
                loadUserBadges(user.id);
                loadUserCertificates(user.id);
            }

            // Charger les notifications globals
            loadNotifications();
        }

        function updateXpProgressBar(xp, level) {
            const xpCurrentEl = document.getElementById('xp-current-value');
            const xpNextEl = document.getElementById('xp-next-value');
            const progressFill = document.getElementById('xp-progress-fill');
            const currentBadge = document.getElementById('current-level-badge');
            const nextBadge = document.getElementById('next-level-badge');

            if (!xpCurrentEl || !progressFill) return;

            const xpForNext = getXpForNextLevel(level);
            const xpForCurrent = level > 1 ? getXpForNextLevel(level - 1) : 0;
            const xpInLevel = xp - xpForCurrent;
            const xpNeeded = xpForNext - xpForCurrent;
            const progress = Math.min((xpInLevel / xpNeeded) * 100, 100);

            // Animer le compteur XP
            let currentXp = 0;
            const targetXp = xp;
            const duration = 1000;
            const startTime = performance.now();

            function animateXp(currentTime) {
                const elapsed = currentTime - startTime;
                const percent = Math.min(elapsed / duration, 1);
                currentXp = Math.floor(percent * targetXp);
                xpCurrentEl.textContent = currentXp;

                if (percent < 1) {
                    requestAnimationFrame(animateXp);
                } else {
                    xpCurrentEl.textContent = targetXp;
                }
            }
            requestAnimationFrame(animateXp);

            xpNextEl.textContent = xpForNext === Infinity ? '∞' : xpForNext;

            // Animer la barre de progression
            setTimeout(() => {
                progressFill.style.width = `${progress}%`;
            }, 100);

            // Mettre à jour les badges de niveau
            currentBadge.textContent = `Niv. ${level}`;
            nextBadge.textContent = level >= 7 ? 'MAX' : `Niv. ${level + 1}`;

            if (level >= 7) {
                nextBadge.style.background = 'linear-gradient(135deg, gold, orange)';
                nextBadge.style.color = '#000';
                nextBadge.style.border = 'none';
            }
        }

        async function loadUserBadges(userId) {
            const badgesData = await api.getBadges(userId);
            const badgesGrid = document.getElementById('user-badges-grid');
            const badgeCountEl = document.getElementById('user-badges-display');
            const coursesCountEl = document.getElementById('user-courses-display');

            if (badgeCountEl) {
                badgeCountEl.textContent = badgesData.unlocked?.length || 0;
            }

            // Récupérer la progression pour le compte des cours
            const progression = await api.getProgression(userId);
            if (coursesCountEl) {
                coursesCountEl.textContent = progression.length || 0;
            }

            if (!badgesGrid) return;

            if (!badgesData.badges || badgesData.badges.length === 0) {
                badgesGrid.innerHTML = '<div class="no-data"><i data-lucide="award"></i><p>Aucun badge disponible</p></div>';
                lucide.createIcons();
                return;
            }

            const unlockedIds = badgesData.unlocked?.map(b => b.badge_id) || [];

            badgesGrid.innerHTML = badgesData.badges.map(b => {
                const isUnlocked = unlockedIds.includes(b.id);
                return `
                    <div class="badge-item ${isUnlocked ? '' : 'locked'}" title="${b.description}" style="--badge-color: ${b.color || '#00f3ff'}">
                        <div class="badge-icon" style="color: ${isUnlocked ? (b.color || '#00f3ff') : '#666'}">
                            <i data-lucide="${b.icon || 'award'}"></i>
                        </div>
                        <div class="badge-name">${b.name}</div>
                    </div>
                `;
            }).join('');

            lucide.createIcons();
        }

        async function loadUserCertificates(userId) {
            const certificates = await api.getCertificates(userId);
            const certsGrid = document.getElementById('user-certificates-grid');
            const certCountEl = document.getElementById('user-certs-display');

            if (certCountEl) {
                certCountEl.textContent = certificates.length || 0;
            }

            if (!certsGrid) return;

            if (!certificates || certificates.length === 0) {
                certsGrid.innerHTML = '<div class="no-data"><i data-lucide="scroll"></i><p>Complétez des cours avec 70%+ pour obtenir des certificats</p></div>';
                lucide.createIcons();
                return;
            }

            certsGrid.innerHTML = certificates.map(c => `
                <div class="certificate-item">
                    <span class="cert-score">${c.score}%</span>
                    <h4>${c.course_title}</h4>
                    <div class="cert-code">${c.certificate_code}</div>
                    <div class="cert-date">Obtenu le ${new Date(c.issued_at).toLocaleDateString('fr-FR')}</div>
                </div>
            `).join('');

            lucide.createIcons();
        }
    }

    // LOGIQUE DU CLASSEMENT

    async function loadLeaderboards(forceGroup = null) {
        const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
        let userGroup = null;
        if (typeof forceGroup === 'string') {
            userGroup = forceGroup;
        } else {
            userGroup = currentUser?.group_name || 'Aucun';
        }
        const data = await api.getLeaderboard(userGroup);

        const groupFilter = document.getElementById('lb-group-filter');
        if (groupFilter && groupFilter.dataset.loaded !== 'true') {
            groupFilter.dataset.loaded = 'true';
            const groupsData = await api.getGroups();
            if (groupsData.success) {
                groupFilter.innerHTML = '<option value="Aucun">Global</option>';
                groupsData.groups.forEach(g => {
                    const opt = document.createElement('option');
                    opt.value = g.name;
                    opt.textContent = g.name;
                    groupFilter.appendChild(opt);
                });
                
                groupFilter.value = userGroup === 'Aucun' ? 'Aucun' : userGroup;
                
                groupFilter.addEventListener('change', (e) => {
                    loadLeaderboards(e.target.value);
                });
            }
        } else if (groupFilter) {
            groupFilter.value = userGroup === 'Aucun' ? 'Aucun' : userGroup;
        }

        // Afficher le nom du groupe dans le titre
        const groupNameEl = document.getElementById('lb-group-name');
        if (groupNameEl) {
            if (userGroup && userGroup !== 'Aucun') {
                groupNameEl.textContent = userGroup;
            } else {
                groupNameEl.textContent = '';
            }
        }

        const groupTbody = document.getElementById('group-leaderboard-list');
        const podiumContainer = document.getElementById('podium-container');
        if (!groupTbody) return;

        // Mettre à jour les stats globales
        const totalUsersEl = document.getElementById('lb-total-users');
        const totalXpEl = document.getElementById('lb-total-xp');

        if (totalUsersEl && data.stats) {
            totalUsersEl.textContent = data.stats.total_users || 0;
        }
        if (totalXpEl && data.stats) {
            totalXpEl.textContent = (data.stats.total_xp || 0).toLocaleString();
        }

        groupTbody.innerHTML = '';
        if (podiumContainer) podiumContainer.innerHTML = '';

        const leaderboard = data.leaderboard || data || [];

        if (leaderboard.length === 0) {
            groupTbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#666;">Aucun participant</td></tr>';
        } else {
            const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));

            // Séparer Podium et Liste
            // On veut afficher dans l'ordre du podium : 2ème, 1er, 3ème
            const podiumUsers = [];
            // Assigner les places (l'API renvoie trié par rang)
            const first = leaderboard[0];
            const second = leaderboard[1];
            const third = leaderboard[2];

            // Construire le tableau podium dans l'ordre visuel (2, 1, 3)
            if (second) podiumUsers.push({ ...second, place: 2 });
            if (first) podiumUsers.push({ ...first, place: 1 });
            if (third) podiumUsers.push({ ...third, place: 3 });

            // Afficher le podium
            if (podiumContainer) {
                podiumUsers.forEach(u => {
                    const avatarContent = u.avatar || u.username.charAt(0).toUpperCase();
                    // Si avatar est une image ou emoji, sinon lettre

                    const isCrown = u.place === 1 ? '<div class="podium-crown"><i data-lucide="crown"></i></div>' : '';

                    podiumContainer.innerHTML += `
                        <div class="podium-item rank-${u.place}">
                            <div class="podium-avatar-wrapper">
                                ${isCrown}
                                <div class="podium-avatar">${avatarContent}</div>
                            </div>
                            <div class="podium-info">
                                <div class="podium-username">${u.username}</div>
                                <div class="podium-xp">${parseInt(u.xp).toLocaleString()} XP</div>
                            </div>
                            <div class="podium-stand">
                                <span class="stand-rank">${u.place}</span>
                            </div>
                        </div>
                    `;
                });
            }

            // Afficher le reste (à partir du 4ème)
            const listUsers = leaderboard.slice(3);

            // Si on veut aussi afficher ceux du podium dans la liste ? 
            // Généralement non, mais parfois oui pour voir les stats détaillées.
            // Ici, affichons le reste de la liste, ou toute la liste ? 
            // Design pattern courant : Podium + suite.
            // Si utilisateur est 1er, il est sur le podium.

            listUsers.forEach((user, index) => {
                const globalRank = index + 4; // 1,2,3 sont sur podium

                const tr = document.createElement('tr');
                if (currentUser && currentUser.id === user.id) {
                    tr.classList.add('current-user-row');
                }

                // Avatar
                const initial = user.username.charAt(0).toUpperCase();
                const avatarDisplay = user.avatar
                    ? `<span style="font-size:1.2rem;">${user.avatar}</span>`
                    : `<div class="user-avatar-sm">${initial}</div>`;

                tr.innerHTML = `
                    <td><span class="rank-text">#${globalRank}</span></td>
                    <td>
                        <div class="user-cell">
                            ${avatarDisplay}
                            <span class="user-name-cell">${user.username}</span>
                        </div>
                    </td>
                    <td>${getLevelName(user.level)}</td>
                    <td>${parseInt(user.xp).toLocaleString()}</td>
                    <td>${user.courses_completed || 0}</td>
                    <td>${user.badges_count || 0}</td>
                `;
                groupTbody.appendChild(tr);
            });

            // Petit hack : si liste vide et seulement < 3 personnes, afficher message ?
            if (listUsers.length === 0 && leaderboard.length > 0) {
                groupTbody.innerHTML = '<tr><td colspan="6" style="text-align:center; color:#666; padding: 2rem;">Le top 3 domine le classement !</td></tr>';
            }
        }
        lucide.createIcons();
    }

    // LOGIQUE PANNEAU ADMIN

    async function initAdminPanel() {
        // Vérifier si l'utilisateur est admin
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        if (!sessionUser || sessionUser.role !== 'admin') {
            loadTemplate('home');
            return;
        }

        // Charger les statistiques
        const stats = await api.getStats();
        if (stats) {
            document.getElementById('stat-users').textContent = stats.users || 0;
            document.getElementById('stat-courses').textContent = stats.courses || 0;
            document.getElementById('stat-questions').textContent = stats.questions || 0;
            document.getElementById('stat-completions').textContent = stats.completions || 0;

            // Cours récents
            const recentCoursesList = document.getElementById('recent-courses-list');
            if (recentCoursesList && stats.recentCourses) {
                recentCoursesList.innerHTML = stats.recentCourses.map(c => `
                    <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--glass-border);">
                        <span style="color: var(--primary-color);">${c.title}</span>
                        <small style="display: block; color: #666;">${c.difficulty}</small>
                    </div>
                `).join('') || '<p style="color:#666;">Aucun cours</p>';
            }

            // Utilisateurs récents
            const recentUsersList = document.getElementById('recent-users-list');
            if (recentUsersList && stats.recentUsers) {
                recentUsersList.innerHTML = stats.recentUsers.map(u => `
                    <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--glass-border);">
                        <span>${u.username}</span>
                        <small style="display: block; color: #666;">${u.email}</small>
                    </div>
                `).join('') || '<p style="color:#666;">Aucun utilisateur</p>';
            }
        }

        // Navigation par onglets
        document.querySelectorAll('.admin-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.admin-tab').forEach(t => {
                    t.classList.remove('active');
                    t.classList.remove('btn-primary');
                    t.classList.add('btn-outline');
                });
                tab.classList.add('active');
                tab.classList.remove('btn-outline');
                tab.classList.add('btn-primary');

                document.querySelectorAll('.admin-panel').forEach(p => p.style.display = 'none');
                document.getElementById(`admin-panel-${tab.dataset.tab}`).style.display = 'block';

                // Charger les données pour les onglets spécifiques
                if (tab.dataset.tab === 'courses') loadAdminCourses();
                if (tab.dataset.tab === 'questions') loadAdminQuestions();
                if (tab.dataset.tab === 'users') loadAdminUsersTable();
            });
        });

        // Initialiser les icônes
        lucide.createIcons();
    }

    async function loadAdminCourses() {
        const courses = await api.getCourses();
        const tbody = document.getElementById('courses-list');
        if (!tbody) return;

        tbody.innerHTML = courses.map(c => `
            <tr>
                <td>#${c.id}</td>
                <td>
                    <strong>${c.title}</strong>
                    <small style="display: block; color: #666;">${c.description?.substring(0, 50)}...</small>
                </td>
                <td>
                    <span class="difficulty-badge difficulty-${c.difficulty?.toLowerCase()}">${c.difficulty}</span>
                </td>
                <td>${c.nb_questions || 0}</td>
                <td>
                    <button class="btn btn-outline btn-sm" onclick="editCourse(${c.id})" style="margin-right: 0.5rem;">
                        <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="deleteCourse(${c.id})" style="color: var(--accent-color); border-color: var(--accent-color);">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="5" style="text-align: center;">Aucun cours</td></tr>';

        lucide.createIcons();
    }

    async function loadAdminQuestions() {
        // Charger les cours pour le filtre
        const courses = await api.getCourses();
        const filterSelect = document.getElementById('filter-course');
        const questionCourseSelect = document.getElementById('question-course');

        if (filterSelect) {
            filterSelect.innerHTML = '<option value="">-- Tous les cours --</option>' +
                courses.map(c => `<option value="${c.id}">${c.title}</option>`).join('');

            filterSelect.addEventListener('change', async () => {
                const courseId = filterSelect.value;
                const questions = await api.getQuestions(courseId || null);
                renderQuestions(questions);
            });
        }

        if (questionCourseSelect) {
            questionCourseSelect.innerHTML = '<option value="">-- Sélectionner un cours --</option>' +
                courses.map(c => `<option value="${c.id}">${c.title}</option>`).join('');
        }

        // Charger toutes les questions
        const questions = await api.getQuestions();
        renderQuestions(questions);
    }

    function renderQuestions(questions) {
        const tbody = document.getElementById('questions-list');
        if (!tbody) return;

        tbody.innerHTML = questions.map(q => `
            <tr>
                <td>#${q.id}</td>
                <td><span style="color: var(--secondary-color);">${q.course_title}</span></td>
                <td>${q.question_text?.substring(0, 60)}...</td>
                <td><span style="color: var(--primary-color); font-weight: bold;">${q.correct_option.toUpperCase()}</span></td>
                <td>
                    <button class="btn btn-outline btn-sm" onclick="editQuestion(${q.id})" style="margin-right: 0.5rem;">
                        <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                    </button>
                    <button class="btn btn-outline btn-sm" onclick="deleteQuestion(${q.id})" style="color: var(--accent-color); border-color: var(--accent-color);">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="5" style="text-align: center;">Aucune question</td></tr>';

        lucide.createIcons();
    }

    async function loadAdminUsersTable() {
        const users = await api.getUsers();
        const tbody = document.getElementById('admin-users-list-full');
        if (!tbody) return;

        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));

        tbody.innerHTML = users.map(u => `
            <tr>
                <td>#${u.id}</td>
                <td>
                    ${u.username}
                    ${u.id == sessionUser?.id ? '<span style="color: var(--primary-color);"> (Vous)</span>' : ''}
                </td>
                <td>${u.email}</td>
                <td>
                    <span style="color: ${u.is_admin ? 'var(--accent-color)' : 'inherit'};">
                        ${u.is_admin ? '🛡️ Admin' : 'Utilisateur'}
                    </span>
                </td>
                <td>
                    <span style="color: var(--primary-color);">${u.completed_courses || 0}</span> cours
                </td>
                <td>
                    ${u.id != sessionUser?.id ? `
                        <button class="btn btn-outline btn-sm" onclick="toggleAdmin(${u.id})" style="margin-right: 0.5rem;" title="${u.is_admin ? 'Retirer admin' : 'Promouvoir admin'}">
                            <i data-lucide="shield" style="width: 14px; height: 14px;"></i>
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="deleteUserAdmin(${u.id})" style="color: var(--accent-color); border-color: var(--accent-color);" title="Supprimer">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    ` : '-'}
                </td>
            </tr>
        `).join('') || '<tr><td colspan="6" style="text-align: center;">Aucun utilisateur</td></tr>';

        lucide.createIcons();
    }

    // VUE COURS (Public)
    async function loadCoursesView() {
        // Récupérer l'utilisateur connecté pour le verrouillage
        const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
        const userId = currentUser ? currentUser.id : null;
        const userRole = currentUser ? currentUser.role : null;

        // Thèmes de couleurs (identiques à admin/add_cours.php)
        const themes = {
            'blue': { primary: '#3b82f6', glow: 'rgba(59, 130, 246, 0.4)' },
            'purple': { primary: '#8b5cf6', glow: 'rgba(139, 92, 246, 0.4)' },
            'green': { primary: '#10b981', glow: 'rgba(16, 185, 129, 0.4)' },
            'red': { primary: '#ef4444', glow: 'rgba(239, 68, 68, 0.4)' },
            'orange': { primary: '#f59e0b', glow: 'rgba(245, 158, 11, 0.4)' },
            'cyan': { primary: '#06b6d4', glow: 'rgba(6, 182, 212, 0.4)' },
            'pink': { primary: '#ec4899', glow: 'rgba(236, 72, 153, 0.4)' }
        };

        const courses = await api.getCourses(userId, userRole);
        const contentArea = document.getElementById('content-area');

        if (courses.length === 0) return;

        // Remplacer le contenu statique par les cours dynamiques
        const bentoGrid = contentArea.querySelector('.bento-grid');
        if (bentoGrid) {
            bentoGrid.innerHTML = courses.map(c => {
                const isLocked = c.is_locked === true || c.is_locked === 1;
                const isCompleted = c.is_completed === true || c.is_completed === 1;
                const isRead = c.is_read === true || c.is_read === 1;

                // Application du thème personnalisé
                const theme = themes[c.theme] || themes['blue'];
                const styleAttr = `style="--primary: ${theme.primary}; --primary-glow: ${theme.glow}; --primary-dim: ${theme.primary}80;"`;

                // Icône personnalisée ou par défaut
                const iconName = c.icon && c.icon !== '' ? c.icon : getDifficultyIcon(c.difficulty);

                // Logique du badge de statut
                let statusBadge = '';
                if (isCompleted) {
                    statusBadge = '<span class="status-badge status-completed" style="margin-right:0.5rem; display:flex; align-items:center; font-size:0.85rem;"><i data-lucide="check-circle" style="width:14px;height:14px;margin-right:4px;"></i>Terminé</span>';
                } else if (isRead) {
                    statusBadge = '<span class="status-badge status-progress" style="margin-right:0.5rem; color:var(--warning); display:flex; align-items:center; font-size:0.85rem;"><i data-lucide="loader-2" style="width:14px;height:14px;margin-right:4px;"></i>En cours</span>';
                }

                return `
                <div class="card course-card ${isLocked ? 'card-locked' : ''} ${isCompleted ? 'card-completed' : ''} ${isRead ? 'card-read' : ''}" 
                     data-course-id="${c.id}" 
                     ${styleAttr}
                     ${isLocked ? '' : `onclick="viewCourse(${c.id})"`}>
                    ${isLocked ? '<div class="lock-overlay"><i data-lucide="lock" class="lock-icon"></i><span>Terminez le module précédent</span></div>' : ''}
                    
                    <div class="course-header">
                        <div class="card-icon"><i data-lucide="${iconName}"></i></div>
                        <span class="difficulty-badge difficulty-${c.difficulty?.toLowerCase()}">${c.difficulty}</span>
                    </div>
                    
                    <div class="course-body">
                        <h3>${c.title}</h3>
                        <p>${c.description}</p>
                    </div>
                    
                    ${(isCompleted || isRead) ? `
                    <div class="course-status-footer" style="padding-top: 1rem; margin-top: auto; display: flex; justify-content: flex-end;">
                        ${statusBadge}
                    </div>` : ''}
                </div>
            `;
            }).join('');

            lucide.createIcons();
            initTiltEffect();
        }
    }

    function getDifficultyIcon(difficulty) {
        switch (difficulty) {
            case 'Facile': return 'shield';
            case 'Intermédiaire': return 'shield-alert';
            case 'Difficile': return 'skull';
            default: return 'book-open';
        }
    }

    // Rendre les fonctions accessibles globalement
    window.showAddCourseForm = function () {
        document.getElementById('course-modal-title').textContent = 'Nouveau Cours';
        document.getElementById('course-form').reset();
        document.getElementById('course-id').value = '';
        document.getElementById('course-modal').style.display = 'flex';
    };

    window.closeCourseModal = function () {
        document.getElementById('course-modal').style.display = 'none';
    };

    window.showAddQuestionForm = function () {
        document.getElementById('question-modal-title').textContent = 'Nouvelle Question';
        document.getElementById('question-form').reset();
        document.getElementById('question-id').value = '';
        document.getElementById('question-modal').style.display = 'flex';
        loadAdminQuestions(); // Pour remplir le menu déroulant des cours
    };

    window.closeQuestionModal = function () {
        document.getElementById('question-modal').style.display = 'none';
    };

    window.editCourse = async function (id) {
        const course = await api.getCourse(id);
        if (!course) return;

        document.getElementById('course-modal-title').textContent = 'Modifier le Cours';
        document.getElementById('course-id').value = course.id;
        document.getElementById('course-title').value = course.title;
        document.getElementById('course-description').value = course.description;
        document.getElementById('course-difficulty').value = course.difficulty;
        document.getElementById('course-content').value = course.content;
        document.getElementById('course-modal').style.display = 'flex';
    };

    window.deleteCourse = async function (id) {
        if (!await showConfirmModal('Supprimer un Cours', 'Supprimer ce cours et toutes ses questions ?', 'Cette action est définitive.')) return;
        const result = await api.deleteCourse(id);
        if (result.success) {
            loadAdminCourses();
            showToast('Succès', 'Cours supprimé', 'success');
        } else {
            showToast('Erreur', result.message || 'Impossible de supprimer', 'error');
        }
    };

    window.editQuestion = async function (id) {
        const questions = await api.getQuestions();
        const question = questions.find(q => q.id == id);
        if (!question) return;

        document.getElementById('question-modal-title').textContent = 'Modifier la Question';
        document.getElementById('question-id').value = question.id;
        document.getElementById('question-course').value = question.course_id;
        document.getElementById('question-text').value = question.question || question.question_text;
        document.getElementById('option-a').value = question.option_a;
        document.getElementById('option-b').value = question.option_b;
        document.getElementById('option-c').value = question.option_c;
        document.getElementById('option-d').value = question.option_d || '';
        document.getElementById('correct-option').value = question.correct_answer || question.correct_option;
        document.getElementById('question-explanation').value = question.explanation || '';
        document.getElementById('question-modal').style.display = 'flex';
    };

    window.deleteQuestion = async function (id) {
        if (!await showConfirmModal('Supprimer Question', 'Voulez-vous vraiment supprimer cette question ?')) return;
        const result = await api.deleteQuestion(id);
        if (result.success) {
            loadAdminQuestions();
            showToast('Succès', 'Question supprimée', 'success');
        } else {
            showToast('Erreur', result.message, 'error');
        }
    };

    window.toggleAdmin = async function (id) {
        if (!await showConfirmModal('Droits Admin', 'Modifier les privilèges administrateur de cet utilisateur ?')) return;
        await api.toggleUserAdmin(id);
        loadAdminUsersTable();
        showToast('Succès', 'Droits mis à jour', 'success');
    };

    window.deleteUserAdmin = async function (id) {
        if (!await showConfirmModal('Supprimer Utilisateur', 'Supprimer définitivement cet utilisateur ?', 'Toutes ses données seront perdues.')) return;
        await api.deleteUser(id);
        loadAdminUsersTable();
        showToast('Succès', 'Utilisateur supprimé', 'success');
    };

    window.viewCourse = async function (id) {
        // Vérifier si le cours est verrouillé
        const currentUser = JSON.parse(sessionStorage.getItem('currentUser'));
        const userId = currentUser ? currentUser.id : null;
        const userRole = currentUser ? currentUser.role : null;

        if (userId) {
            const courses = await api.getCourses(userId, userRole);
            const targetCourse = courses.find(c => c.id == id);

            if (targetCourse && (targetCourse.is_locked === true || targetCourse.is_locked === 1)) {
                alert('Ce module est verrouillé. Veuillez terminer le module précédent avant de continuer.');
                return;
            }
        }

        const course = await api.getCourse(id);
        if (!course) return;

        // Marquer le cours comme lu (progression avec completed = 0)
        if (currentUser) {
            await api.saveProgression(currentUser.id, id, 0);
        }

        const contentArea = document.getElementById('content-area');
        contentArea.innerHTML = `
            <div style="margin-bottom: 2rem;">
                <button class="btn btn-outline" onclick="loadTemplate('cours')">
                    <i data-lucide="arrow-left"></i> Retour aux cours
                </button>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin-bottom: 0.5rem; line-height: 1.2;">${course.title}</h1>
                    <span class="difficulty-badge difficulty-${course.difficulty?.toLowerCase()}">${course.difficulty}</span>
                </div>
                <button class="btn btn-primary" onclick="startQuiz(${course.id})" style="flex-shrink: 0;">
                    <i data-lucide="brain-circuit"></i> Commencer le Quiz
                </button>
            </div>

            <div class="settings-card">
                <div class="course-content">${course.content}</div>
            </div>
        `;
        lucide.createIcons();
    };

    window.startSuddenDeathQuiz = async function() {
        // Fetch all questions
        const questions = await api.getQuestions();
        if (!questions || questions.length === 0) {
            alert('Aucune question disponible en base de données.');
            return;
        }

        // Shuffle questions
        const shuffledQuestions = [...questions].sort(() => Math.random() - 0.5);
        
        let currentQuestion = 0;
        let score = 0;
        let gameOver = false;

        function renderQuestion() {
            const q = shuffledQuestions[currentQuestion];
            const questionText = q.question || q.question_text;
            const correctAnswer = (q.correct_answer || q.correct_option || '').toUpperCase();
            const explanation = q.explanation || '';
            const difficulty = q.difficulty || 'Facile';
            const xpReward = q.xp_reward || 10;
            const contentArea = document.getElementById('content-area');

            // Badge de difficulté avec couleur
            const difficultyBadge = difficulty === 'Facile'
                ? '<span style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">🟢 Facile • ' + xpReward + ' XP</span>'
                : difficulty === 'Intermédiaire'
                    ? '<span style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">🟠 Intermédiaire • ' + xpReward + ' XP</span>'
                    : '<span style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">🔴 Difficile • ' + xpReward + ' XP</span>';

            const buildOption = (letter, text) => {
                return `<button class="btn btn-outline sd-quiz-option" data-option="${letter}" style="text-align: left; padding: 1rem; transition: all 0.2s;" ${gameOver ? 'disabled' : ''}>
                    <strong>${letter}.</strong> ${text}
                </button>`;
            };

            let optionsHtml = buildOption('A', q.option_a) + buildOption('B', q.option_b) + buildOption('C', q.option_c);
            if (q.option_d && q.option_d.trim()) {
                optionsHtml += buildOption('D', q.option_d);
            }

            contentArea.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h1>Mort <span style="color: var(--danger, #ff003c);">Subite</span></h1>
                    <div style="font-size: 1.5rem; font-weight: bold; background: rgba(255, 255, 255, 0.1); padding: 0.5rem 1.5rem; border-radius: 50px;">
                        Score: <span id="sd-score-display" style="color: var(--primary-color);">${score}</span>
                    </div>
                </div>
                
                <div class="settings-card" style="max-width: 800px; margin: 1rem auto; padding: 2rem; ${gameOver ? 'border-color: var(--danger, #ff003c); box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);' : ''}">
                    <div style="margin-bottom: 1.5rem;">
                        <h3 style="margin: 0; font-size: 1.25rem; line-height: 1.5;">Question ${currentQuestion + 1}</h3>
                        <p style="font-size: 1.1rem; margin-top: 1rem;">${questionText}</p>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">${difficultyBadge}</div>
                    
                    <div class="sd-quiz-options" style="display: flex; flex-direction: column; gap: 1rem;">
                        ${optionsHtml}
                    </div>

                    <div id="sd-explanation-box" style="display: none; margin-top: 1.5rem; padding: 1.5rem; border-radius: 8px; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                    </div>
                    
                    <div id="sd-action-container" style="display: none; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); text-align: right;">
                        <button class="btn btn-primary" id="sd-next-btn" style="padding: 0.75rem 2rem; font-size: 1.1rem;">
                            Question suivante <i data-lucide="arrow-right"></i>
                        </button>
                    </div>

                    <div id="sd-gameover-container" style="display: none; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); text-align: center;">
                        <h2 style="color: var(--danger, #ef4444); margin-bottom: 1rem; font-size: 2rem;">Game Over !</h2>
                        <p style="font-size: 1.2rem; margin-bottom: 1.5rem;">Vous avez survécu à <strong>${score}</strong> question(s).</p>
                        <button class="btn" id="sd-quit-btn" style="background: var(--bg-lighter); color: var(--text-main);">Retour à l'accueil Quiz</button>
                        <button class="btn btn-primary" id="sd-retry-btn" style="margin-left: 1rem; background: linear-gradient(45deg, var(--danger, #ff003c), #ff4b2b); border: none;">Réessayer</button>
                    </div>
                </div>
            `;
            
            lucide.createIcons();

            // Event listeners
            if (!gameOver) {
                document.querySelectorAll('.sd-quiz-option').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        if (gameOver) return;
                        
                        const selected = e.currentTarget.dataset.option.toUpperCase();
                        const isCorrect = selected === correctAnswer;

                        // Disable all buttons
                        document.querySelectorAll('.sd-quiz-option').forEach(b => {
                            b.disabled = true;
                            b.style.opacity = '0.7';
                            
                            // Highlight correct answer
                            if (b.dataset.option.toUpperCase() === correctAnswer) {
                                b.style.borderColor = 'var(--success, #10b981)';
                                b.style.background = 'rgba(16, 185, 129, 0.2)';
                                b.style.opacity = '1';
                            }
                            
                            // Highlight wrong selected answer
                            if (b.dataset.option.toUpperCase() === selected && !isCorrect) {
                                b.style.borderColor = 'var(--danger, #ef4444)';
                                b.style.background = 'rgba(239, 68, 68, 0.2)';
                                b.style.opacity = '1';
                            }
                        });

                        if (isCorrect) {
                            score++;
                            document.getElementById('sd-score-display').textContent = score;
                            
                            // Show explanation and next button
                            const explBox = document.getElementById('sd-explanation-box');
                            explBox.style.display = 'block';
                            explBox.style.background = 'rgba(16, 185, 129, 0.1)';
                            explBox.style.borderColor = 'rgba(16, 185, 129, 0.3)';
                            explBox.innerHTML = `<h4 style="color: var(--success, #10b981); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="check-circle" style="width: 20px; height: 20px;"></i> Bonne réponse !</h4>` + (explanation ? `<p style="margin-bottom: 0;">${explanation}</p>` : '');
                            
                            lucide.createIcons();
                            
                            if (currentQuestion < shuffledQuestions.length - 1) {
                                document.getElementById('sd-action-container').style.display = 'block';
                            } else {
                                // Gagner le jeu (toutes les questions)
                                gameOver = true;
                                document.getElementById('sd-gameover-container').style.display = 'block';
                                document.getElementById('sd-gameover-container').querySelector('h2').textContent = 'Félicitations !';
                                document.getElementById('sd-gameover-container').querySelector('h2').style.color = 'var(--success, #10b981)';
                                document.getElementById('sd-gameover-container').querySelector('p').innerHTML = `Vous avez répondu correctement à <strong>toutes les questions</strong> !`;
                                sdSaveScoreAndXp(score);
                            }
                        } else {
                            // Mauvaise réponse -> Game Over
                            gameOver = true;
                            
                            const explBox = document.getElementById('sd-explanation-box');
                            explBox.style.display = 'block';
                            explBox.innerHTML = `<h4 style="color: var(--danger, #ef4444); margin-top: 0; display: flex; align-items: center; gap: 0.5rem;"><i data-lucide="x-circle" style="width: 20px; height: 20px;"></i> Mauvaise réponse</h4><p style="margin-bottom: 0.5rem;">La bonne réponse était: <strong>${correctAnswer}</strong></p>` + (explanation ? `<p style="margin-bottom: 0;">${explanation}</p>` : '');
                            
                            lucide.createIcons();
                            
                            document.getElementById('sd-gameover-container').style.display = 'block';
                            sdSaveScoreAndXp(score);
                        }
                    });
                });
            }

            document.getElementById('sd-next-btn')?.addEventListener('click', () => {
                currentQuestion++;
                renderQuestion();
            });

            document.getElementById('sd-retry-btn')?.addEventListener('click', () => {
                window.startSuddenDeathQuiz();
            });
            
            document.getElementById('sd-quit-btn')?.addEventListener('click', () => {
                // Return to quiz home (just triggers navigation)
                document.querySelector('.nav-item[data-view="quiz"]')?.click();
            });
        }
        
        async function sdSaveScoreAndXp(finalScore) {
            const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
            if (!sessionUser || finalScore === 0) return;
            
            // Calculer l'XP : 15 XP par bonne réponse consécutive
            const xpGained = finalScore * 15;
            
            const xpResult = await api.addXp(sessionUser.id, xpGained);
            if (xpResult.success) {
                // Update session
                sessionUser.xp = xpResult.new_xp;
                if (xpResult.leveled_up) {
                    sessionUser.level = xpResult.new_level;
                    showToast('Niveau Supérieur !', `Vous avez atteint le niveau ${xpResult.new_level} !`, 'levelup', 6000);
                }
                sessionStorage.setItem('currentUser', JSON.stringify(sessionUser));
                
                // Show notification for XP gained
                showToast('XP Gagnée', `+${xpGained} XP pour votre performance en Mort Subite !`, 'achievement', 5000);
                
                // Check badges
                const badgesResult = await api.checkBadges(sessionUser.id);
                if (badgesResult.success && badgesResult.new_badges.length > 0) {
                    badgesResult.new_badges.forEach(badge => {
                        showToast('Badge Débloqué !', `Vous avez obtenu le badge : ${badge.name}`, 'unlock', 6000);
                    });
                }
                
                // Refresh sidebar
                if (typeof window.updateSidebarUser === 'function') {
                    window.updateSidebarUser(sessionUser);
                }
            }
        }

        renderQuestion();
    };

    window.startQuiz = async function (courseId) {
        const questions = await api.getQuestions(courseId);
        if (questions.length === 0) {
            alert('Aucune question disponible pour ce cours.');
            return;
        }

        let currentQuestion = 0;
        let userAnswers = new Array(questions.length).fill(null); // Stocker les réponses de l'utilisateur

        function renderQuestion() {
            const q = questions[currentQuestion];
            const questionText = q.question || q.question_text;
            const correctAnswer = (q.correct_answer || q.correct_option || '').toUpperCase();
            const explanation = q.explanation || '';
            const difficulty = q.difficulty || 'Facile';
            const xpReward = q.xp_reward || 10;
            const contentArea = document.getElementById('content-area');
            const previousAnswer = userAnswers[currentQuestion];
            const hasAnswered = previousAnswer !== null;

            // Badge de difficulté avec couleur
            const difficultyBadge = difficulty === 'Facile'
                ? '<span style="background: rgba(16, 185, 129, 0.2); color: #10b981; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">🟢 Facile • ' + xpReward + ' XP</span>'
                : difficulty === 'Intermédiaire'
                    ? '<span style="background: rgba(245, 158, 11, 0.2); color: #f59e0b; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">🟠 Intermédiaire • ' + xpReward + ' XP</span>'
                    : '<span style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.85rem;">🔴 Difficile • ' + xpReward + ' XP</span>';

            // Construire les options (A, B, C, et D si disponible)
            const buildOption = (letter, text) => {
                let style = 'text-align: left; padding: 1rem;';
                if (hasAnswered) {
                    if (letter === correctAnswer) {
                        style += ' border-color: var(--success, #10b981); background: rgba(16, 185, 129, 0.2);';
                    } else if (letter === previousAnswer && previousAnswer !== correctAnswer) {
                        style += ' border-color: var(--danger, #ef4444); background: rgba(239, 68, 68, 0.2);';
                    }
                }
                return `<button class="btn btn-outline quiz-option" data-option="${letter}" style="${style}" ${hasAnswered ? 'disabled' : ''}>
                    <strong>${letter}.</strong> ${text}
                </button>`;
            };

            let optionsHtml = buildOption('A', q.option_a) + buildOption('B', q.option_b) + buildOption('C', q.option_c);
            if (q.option_d && q.option_d.trim()) {
                optionsHtml += buildOption('D', q.option_d);
            }

            // Boutons de navigation
            const isLastQuestion = currentQuestion === questions.length - 1;
            const isFirstQuestion = currentQuestion === 0;

            contentArea.innerHTML = `
                <h1>Quiz <span class="text-gradient">${currentQuestion + 1}/${questions.length}</span></h1>
                
                <div style="max-width: 800px; margin: 0 auto 1rem;">
                    <div style="display: flex; gap: 4px;">
                        ${questions.map((_, i) => `
                            <div style="flex: 1; height: 6px; border-radius: 3px; background: ${userAnswers[i] !== null
                    ? (userAnswers[i] === (questions[i].correct_answer || '').toUpperCase() ? 'var(--success, #10b981)' : 'var(--danger, #ef4444)')
                    : i === currentQuestion ? 'var(--primary-color)' : 'rgba(255,255,255,0.1)'
                };"></div>
                        `).join('')}
                    </div>
                </div>
                
                <div class="settings-card" style="max-width: 800px; margin: 1rem auto; padding: 2rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h3 style="margin: 0;">${questionText}</h3>
                    </div>
                    <div style="margin-bottom: 1.5rem;">${difficultyBadge}</div>
                    <div class="quiz-options" style="display: flex; flex-direction: column; gap: 1rem;">
                        ${optionsHtml}
                    </div>
                    <div id="explanation-box" style="${hasAnswered ? 'display: block;' : 'display: none;'} margin-top: 1.5rem; padding: 1rem; border-radius: 8px; ${hasAnswered
                    ? (previousAnswer === correctAnswer
                        ? 'background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);'
                        : 'background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);')
                    : ''
                }">
                        ${hasAnswered && explanation ? `
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <span style="font-size: 1.2rem;">${previousAnswer === correctAnswer ? '✅' : '❌'}</span>
                                <strong style="color: ${previousAnswer === correctAnswer ? '#10b981' : '#ef4444'};">
                                    ${previousAnswer === correctAnswer ? 'Bonne réponse !' : 'Mauvaise réponse'}
                                </strong>
                            </div>
                            <p style="margin: 0; color: #ccc;"><strong>💡 Explication :</strong> ${explanation}</p>
                        ` : ''}
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                        <button class="btn btn-outline" id="prevBtn" ${isFirstQuestion ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ''}>
                            ← Précédent
                        </button>
                        <div style="display: flex; gap: 1rem;">
                            ${hasAnswered ? (isLastQuestion
                    ? `<button class="btn btn-primary" id="finishBtn">Terminer le quiz 🏁</button>`
                    : `<button class="btn btn-primary" id="nextBtn">Suivant →</button>`
                ) : '<span style="color: #666; font-size: 0.9rem; align-self: center;">Sélectionnez une réponse</span>'}
                        </div>
                    </div>
                </div>
            `;

            // Event listeners pour la navigation
            document.getElementById('prevBtn')?.addEventListener('click', () => {
                if (currentQuestion > 0) {
                    currentQuestion--;
                    renderQuestion();
                }
            });

            document.getElementById('nextBtn')?.addEventListener('click', () => {
                if (currentQuestion < questions.length - 1) {
                    currentQuestion++;
                    renderQuestion();
                }
            });

            document.getElementById('finishBtn')?.addEventListener('click', () => {
                // Calculer le score final
                let finalScore = 0;
                questions.forEach((q, i) => {
                    const correct = (q.correct_answer || q.correct_option || '').toUpperCase();
                    if (userAnswers[i] === correct) finalScore++;
                });
                showQuizResults(courseId, finalScore, questions.length);
            });

            // Event listeners pour les options (seulement si pas encore répondu)
            if (!hasAnswered) {
                document.querySelectorAll('.quiz-option').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        const selected = e.currentTarget.dataset.option.toUpperCase();
                        const correct = correctAnswer;
                        const isCorrect = selected === correct;

                        // Enregistrer la réponse
                        userAnswers[currentQuestion] = selected;

                        document.querySelectorAll('.quiz-option').forEach(b => {
                            b.disabled = true;
                            if (b.dataset.option.toUpperCase() === correct) {
                                b.style.borderColor = 'var(--success, #10b981)';
                                b.style.background = 'rgba(16, 185, 129, 0.2)';
                            }
                            if (b.dataset.option.toUpperCase() === selected && selected !== correct) {
                                b.style.borderColor = 'var(--danger, #ef4444)';
                                b.style.background = 'rgba(239, 68, 68, 0.2)';
                            }
                        });

                        // Afficher l'explication
                        const explanationBox = document.getElementById('explanation-box');
                        if (explanation) {
                            explanationBox.style.display = 'block';
                            explanationBox.style.background = isCorrect ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)';
                            explanationBox.style.border = isCorrect ? '1px solid rgba(16, 185, 129, 0.3)' : '1px solid rgba(239, 68, 68, 0.3)';
                            explanationBox.innerHTML = `
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                                    <span style="font-size: 1.2rem;">${isCorrect ? '✅' : '❌'}</span>
                                    <strong style="color: ${isCorrect ? '#10b981' : '#ef4444'};">
                                        ${isCorrect ? 'Bonne réponse !' : 'Mauvaise réponse'}
                                    </strong>
                                </div>
                                <p style="margin: 0; color: #ccc;"><strong>💡 Explication :</strong> ${explanation}</p>
                            `;
                        }

                        // Re-render pour afficher les boutons de navigation
                        renderQuestion();
                    });
                });
            }
        }

        renderQuestion();
    };

    async function showQuizResults(courseId, score, total) {
        const percentage = Math.round((score / total) * 100);
        const contentArea = document.getElementById('content-area');

        // Sauvegarder la progression si l'utilisateur est connecté
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        let certificateCode = null;
        let xpGained = 0;
        let leveledUp = false;

        if (sessionUser) {
            // Calculer l'XP gagné (basé sur le score)
            xpGained = Math.round(score * 10 * (percentage / 100)); // 10 XP par bonne réponse × multiplicateur
            if (percentage >= 70) xpGained += 20; // Bonus de réussite
            if (percentage === 100) xpGained += 30; // Bonus parfait

            // Ajouter l'XP
            const xpResult = await api.addXp(sessionUser.id, xpGained);
            if (xpResult.success) {
                leveledUp = xpResult.leveled_up;
                // Mettre à jour la session
                sessionUser.xp = xpResult.new_xp;
                sessionUser.level = xpResult.new_level;
                sessionStorage.setItem('currentUser', JSON.stringify(sessionUser));

                // Toast pour l'XP gagné
                showToast('XP Gagné !', `+${xpGained} XP ajoutés à votre profil`, 'success', 4000);

                // Toast si level up
                if (leveledUp) {
                    setTimeout(() => {
                        showToast('Niveau Supérieur !', `Félicitations ! Vous êtes maintenant ${getLevelName(xpResult.new_level)}`, 'levelup', 6000);
                    }, 500);
                }
            }

            if (percentage >= 70) {
                // Sauvegarder la progression avec le score
                await api.saveProgression(sessionUser.id, courseId, 1, percentage);

                // Vérifier si un nouveau module est débloqué
                const courses = await api.getCourses(sessionUser.id, sessionUser.role);
                const currentCourseIndex = courses.findIndex(c => c.id == courseId);
                if (currentCourseIndex !== -1 && currentCourseIndex < courses.length - 1) {
                    const nextCourse = courses[currentCourseIndex + 1];
                    if (!nextCourse.is_locked) {
                        // Le prochain module est maintenant débloqué !
                        setTimeout(() => {
                            showToast('🔓 Module Débloqué !', `"${nextCourse.title}" est maintenant accessible !`, 'unlock', 7000);
                        }, 1000);

                        // Sauvegarder la notification en base
                        await api.createNotification(
                            sessionUser.id,
                            'Nouveau module débloqué !',
                            `Vous avez débloqué le module "${nextCourse.title}". Continuez votre progression !`,
                            'unlock',
                            '#cours'
                        );
                    }
                }

                // Générer un certificat
                const certResult = await api.generateCertificate(sessionUser.id, courseId, percentage);
                if (certResult.success) {
                    certificateCode = certResult.certificate_code;
                    setTimeout(() => {
                        showToast('Certificat Obtenu !', 'Un certificat a été généré pour ce cours', 'achievement', 5000);
                    }, 1500);
                }

                // Vérifier les nouveaux badges
                const badgeResult = await api.checkBadges(sessionUser.id);
                if (badgeResult.new_badges && badgeResult.new_badges.length > 0) {
                    let delay = 2000;
                    badgeResult.new_badges.forEach(badge => {
                        setTimeout(() => {
                            showToast(`🏅 Badge Débloqué !`, `"${badge.name}" - ${badge.description}`, 'achievement', 6000);
                        }, delay);
                        delay += 800;
                    });
                }
            }
        }

        contentArea.innerHTML = `
            <div style="text-align: center; max-width: 600px; margin: 0 auto;">
                <h1>Quiz <span class="text-gradient">Terminé!</span></h1>
                <div class="card" style="margin-top: 2rem;">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">
                        ${percentage >= 70 ? '🎉' : percentage >= 50 ? '👍' : '📚'}
                    </div>
                    <h2>${score} / ${total}</h2>
                    <p style="color: var(--primary-color); font-size: 1.5rem; margin: 1rem 0;">
                        ${percentage}%
                    </p>
                    <p style="color: #666;">
                        ${percentage >= 70 ? 'Excellent travail !' : percentage >= 50 ? 'Pas mal, continuez !' : 'Révisez et réessayez !'}
                    </p>
                    ${xpGained > 0 ? `
                        <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(0, 255, 136, 0.1); border-radius: 8px;">
                            <p style="color: var(--accent-color); font-weight: 600;">
                                +${xpGained} XP ${leveledUp ? '🎊 Niveau supérieur!' : ''}
                            </p>
                        </div>
                    ` : ''}
                    ${certificateCode ? `
                        <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(255, 255, 255, 0.1); border-radius: 8px; border: 1px solid var(--primary-color);">
                            <p style="color: var(--primary-color); font-weight: 600;">🏆 Certificat obtenu !</p>
                            <p style="font-family: 'JetBrains Mono', monospace; color: #999;">${certificateCode}</p>
                        </div>
                    ` : ''}
                </div>
                <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                    <button class="btn btn-primary" onclick="startQuiz(${courseId})">
                        <i data-lucide="refresh-cw"></i> Recommencer
                    </button>
                    <button class="btn btn-outline" onclick="loadTemplate('cours')">
                        <i data-lucide="book-open"></i> Autres cours
                    </button>
                </div>
            </div>
        `;
        lucide.createIcons();
    }

    // Gérer la soumission du formulaire de cours
    document.addEventListener('submit', async (e) => {
        if (e.target.id === 'course-form') {
            e.preventDefault();
            const id = document.getElementById('course-id').value;
            const courseData = {
                title: document.getElementById('course-title').value,
                description: document.getElementById('course-description').value,
                difficulty: document.getElementById('course-difficulty').value,
                content: document.getElementById('course-content').value
            };

            let result;
            if (id) {
                courseData.id = parseInt(id);
                result = await api.updateCourse(courseData);
            } else {
                result = await api.createCourse(courseData);
            }

            if (result.success) {
                closeCourseModal();
                loadAdminCourses();
            } else {
                alert('Erreur: ' + result.message);
            }
        }

        if (e.target.id === 'question-form') {
            e.preventDefault();
            const id = document.getElementById('question-id').value;
            const questionData = {
                course_id: document.getElementById('question-course').value,
                question: document.getElementById('question-text').value,
                option_a: document.getElementById('option-a').value,
                option_b: document.getElementById('option-b').value,
                option_c: document.getElementById('option-c').value,
                option_d: document.getElementById('option-d')?.value || '',
                correct_answer: document.getElementById('correct-option').value.toUpperCase(),
                explanation: document.getElementById('question-explanation').value
            };

            let result;
            if (id) {
                questionData.id = parseInt(id);
                result = await api.updateQuestion(questionData);
            } else {
                result = await api.createQuestion(questionData);
            }

            if (result.success) {
                closeQuestionModal();
                loadAdminQuestions();
            } else {
                alert('Erreur: ' + result.message);
            }
        }
    });

    // Rendre loadTemplate accessible globalement pour les handlers onclick
    window.loadTemplate = loadTemplate;

    // VUE PARAMETRES
    function initParametresView() {
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        if (!sessionUser) {
            loadTemplate('profil');
            return;
        }

        // Pre-remplir les données actuelles
        const usernameInput = document.getElementById('param-username');
        const emailInput = document.getElementById('param-email');
        if (usernameInput) usernameInput.value = sessionUser.username;
        if (emailInput) emailInput.value = sessionUser.email;

        // Formulaire profil
        document.getElementById('update-profile-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const username = usernameInput.value;
            const email = emailInput.value;
            const msgEl = document.getElementById('profile-update-msg');
            
            msgEl.textContent = 'Enregistrement...';
            msgEl.style.color = '#fff';
            msgEl.style.display = 'inline-block';

            const result = await api.updateProfile(sessionUser.id, username, email);
            if (result.success) {
                msgEl.textContent = 'Profil mis à jour !';
                msgEl.style.color = 'var(--accent-color)';
                // Mettre à jour la session
                sessionUser.username = username;
                sessionUser.email = email;
                sessionStorage.setItem('currentUser', JSON.stringify(sessionUser));
                updateSidebarUser(sessionUser);
            } else {
                msgEl.textContent = 'Erreur : ' + result.message;
                msgEl.style.color = 'var(--danger)';
            }
            setTimeout(() => { msgEl.style.display = 'none'; }, 3000);
        });

        // Formulaire sécurité
        document.getElementById('update-password-form')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = document.getElementById('param-password').value;
            const confirm = document.getElementById('param-password-confirm').value;
            const msgEl = document.getElementById('password-update-msg');
            
            if (password !== confirm) {
                msgEl.textContent = 'Les mots de passe ne correspondent pas';
                msgEl.style.color = 'var(--danger)';
                msgEl.style.display = 'inline-block';
                return;
            }

            if (password.length < 17) {
                msgEl.textContent = 'Le mot de passe doit faire 17 caractères minimum';
                msgEl.style.color = 'var(--danger)';
                msgEl.style.display = 'inline-block';
                return;
            }

            msgEl.textContent = 'Mise à jour...';
            msgEl.style.color = '#fff';
            msgEl.style.display = 'inline-block';

            const result = await api.updatePassword(sessionUser.id, password);
            if (result.success) {
                msgEl.textContent = 'Mot de passe modifié !';
                msgEl.style.color = 'var(--accent-color)';
                document.getElementById('update-password-form').reset();
            } else {
                msgEl.textContent = 'Erreur : ' + result.message;
                msgEl.style.color = 'var(--danger)';
            }
            setTimeout(() => { msgEl.style.display = 'none'; }, 3000);
        });
    }

    // VUE SIMULATION PHISHING
    async function initPhishingView() {
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));

        // Charger les scénarios
        const scenarios = await api.getPhishingScenarios(sessionUser?.id);
        renderPhishingScenarios(scenarios);

        // Charger les stats si connecté
        if (sessionUser) {
            const statsData = await api.getPhishingStats(sessionUser.id);
            updatePhishingStats(statsData.stats);
        }
    }

    function updatePhishingStats(stats) {
        if (!stats) return;
        const total = parseInt(stats.total) || 0;
        const correct = parseInt(stats.correct) || 0;
        const rate = total > 0 ? Math.round((correct / total) * 100) : 0;
        const avgTime = Math.round(parseFloat(stats.avg_time) || 0);

        document.getElementById('phishingTotal').textContent = total;
        document.getElementById('phishingCorrect').textContent = correct;
        document.getElementById('phishingRate').textContent = rate + '%';
        document.getElementById('phishingTime').textContent = avgTime + 's';
    }

    function renderPhishingScenarios(scenarios) {
        const grid = document.getElementById('scenariosGrid');
        if (!grid) return;

        grid.innerHTML = scenarios.map(s => {
            let statusClass = '';
            let statusIcon = '';
            if (s.completed !== undefined && s.completed) {
                statusClass = s.correct ? 'completed' : 'completed wrong';
                statusIcon = s.correct
                    ? '<i data-lucide="check-circle"></i>'
                    : '<i data-lucide="x-circle"></i>';
            }

            return `
                <div class="scenario-card ${statusClass}" data-scenario-id="${s.id}">
                    <span class="type-badge ${s.type}">
                        <i data-lucide="${s.type === 'email' ? 'mail' : s.type === 'sms' ? 'smartphone' : 'globe'}"></i>
                        ${s.type.toUpperCase()}
                    </span>
                    <h3>${s.title}</h3>
                    <span class="difficulty ${s.difficulty}">${s.difficulty}</span>
                    ${statusIcon ? `<div class="status-icon">${statusIcon}</div>` : ''}
                </div>
            `;
        }).join('') || '<p style="text-align: center; color: var(--text-muted);">Aucun scénario disponible</p>';

        // Ajouter les écouteurs de clic aux cartes de scénarios
        grid.querySelectorAll('.scenario-card').forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const scenarioId = this.getAttribute('data-scenario-id');
                if (scenarioId) {
                    openScenario(parseInt(scenarioId));
                }
            });
        });

        lucide.createIcons();
    }

    let currentScenarioStartTime = null;

    window.openScenario = async function (id) {
        const scenario = await api.getPhishingScenario(id);
        if (!scenario) return;

        currentScenarioStartTime = Date.now();

        const contentDiv = document.getElementById('scenarioContent');
        const resultDiv = document.getElementById('scenarioResult');

        contentDiv.style.display = 'block';
        resultDiv.style.display = 'none';

        let previewHtml = '';
        const today = new Date().toLocaleDateString('fr-FR', { hour: '2-digit', minute: '2-digit' });

        if (scenario.type === 'email') {
            const initial = scenario.sender.charAt(0).toUpperCase();
            previewHtml = `
                <div class="scenario-container">
                    
                    <div class="email-mockup">
                        <div class="email-header-top">
                            <div class="window-dots dot-red"></div>
                            <div class="window-dots dot-yellow"></div>
                            <div class="window-dots dot-green"></div>
                        </div>
                        <div class="email-meta-area">
                            <div class="email-avatar">${initial}</div>
                            <div class="email-info">
                                <div class="email-sender-line">
                                    <span class="sender-name">Expéditeur Inconnu</span>
                                    <span class="sender-email">&lt;${scenario.sender}&gt;</span>
                                </div>
                                <div class="email-recipient-line">À : moi@entreprise.com</div>
                            </div>
                            <div class="email-date">${today}</div>
                        </div>
                        <div class="email-subject">${scenario.subject}</div>
                        <div class="email-body">
                            ${scenario.content.replace(/\n/g, '<br>')}
                        </div>
                        <div class="email-attachment">
                            <i data-lucide="paperclip"></i> pièce_jointe.pdf <span style="opacity:0.5">(1.2 MB)</span>
                        </div>
                    </div>
                </div>
            `;
        } else if (scenario.type === 'sms') {
            previewHtml = `
                <div class="scenario-container">
                    
                    <div class="phone-mockup">
                        <div class="phone-notch"></div>
                        <div class="sms-header-bar">
                            <i data-lucide="chevron-left"></i>
                            <div class="contact-info">
                                <div class="contact-name">${scenario.sender}</div>
                            </div>
                            <i data-lucide="info"></i>
                        </div>
                        <div class="sms-content-area">
                            <div class="sms-bubble received">
                                ${scenario.content}
                                <span class="sms-timestamp">Maintenant</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else {
            previewHtml = `
                <div class="scenario-container">
                    
                    <div class="browser-mockup">
                        <div class="browser-header">
                            <div class="browser-nav-btns">
                                <i data-lucide="arrow-left"></i>
                                <i data-lucide="arrow-right"></i>
                                <i data-lucide="rotate-cw"></i>
                            </div>
                            <div class="url-bar">
                                <i data-lucide="lock" class="padlock-icon"></i>
                                <span class="url-text">${scenario.sender || 'https://site-suspect.com'}</span>
                            </div>
                        </div>
                        <div class="browser-viewport">
                            <h3 class="site-title">${scenario.subject}</h3>
                            <div class="site-content">${scenario.content}</div>
                            <button class="site-btn">Connexion</button>
                        </div>
                    </div>
                </div>
            `;
        }

        contentDiv.innerHTML = `
            <div class="scenario-layout">
                <div class="scenario-visual">
                    ${previewHtml}
                </div>
                <div class="scenario-decision">
                    <h3>Verdict ?</h3>
                    <p>Analysez les indices et prenez une décision.</p>
                    <div class="answer-buttons">
                        <button class="answer-btn phishing" onclick="submitPhishingAnswer(${scenario.id}, true)">
                            <div class="btn-icon"><i data-lucide="alert-triangle"></i></div>
                            <span>C'est un Phishing</span>
                        </button>
                        <button class="answer-btn legitimate" onclick="submitPhishingAnswer(${scenario.id}, false)">
                            <div class="btn-icon"><i data-lucide="shield-check"></i></div>
                            <span>C'est Légitime</span>
                        </button>
                    </div>
                </div>
            </div>
        `;

        const modal = document.getElementById('scenarioModal');
        if (modal) {
            modal.style.display = 'flex';
        }
        lucide.createIcons();
    };

    window.submitPhishingAnswer = async function (scenarioId, isPhishing) {
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        // if (!sessionUser) {
        //    alert('Connectez-vous pour sauvegarder vos résultats');
        //    return;
        // }
        const userId = sessionUser ? sessionUser.id : 0;

        const timeTaken = Math.round((Date.now() - currentScenarioStartTime) / 1000);
        const result = await api.submitPhishingAnswer(userId, scenarioId, isPhishing, timeTaken);

        const contentDiv = document.getElementById('scenarioContent');
        const resultDiv = document.getElementById('scenarioResult');

        contentDiv.style.display = 'none';
        resultDiv.style.display = 'block';

        const isCorrect = result.is_correct;
        const indicators = result.indicators ? result.indicators.split(',').map(i => `<li>${i.trim()}</li>`).join('') : '';

        resultDiv.innerHTML = `
            <div class="result-container ${isCorrect ? 'correct' : 'incorrect'}">
                <i data-lucide="${isCorrect ? 'check-circle' : 'x-circle'}" class="result-icon"></i>
                <h2>${isCorrect ? 'Bonne réponse !' : 'Mauvaise réponse'}</h2>
                <p>Ce message était ${result.correct_answer ? 'bien du phishing' : 'légitime'}.</p>
                ${result.xp_earned > 0 ? `<p class="xp-earned"><i data-lucide="zap"></i> +${result.xp_earned} XP</p>` : ''}
                
                <div class="result-explanation">
                    <h4><i data-lucide="info"></i> Explication</h4>
                    <p>${result.explanation || 'Pas d\'explication disponible.'}</p>
                    ${indicators ? `<ul class="indicators-list">${indicators}</ul>` : ''}
                </div>
                
                <div class="result-actions">
                    <button class="btn btn-primary" onclick="closeScenarioModal(); initPhishingView();">
                        Continuer
                    </button>
                </div>
            </div>
        `;

        lucide.createIcons();

        // Vérifier les nouveaux badges
        await api.checkBadges(sessionUser.id);
    };

    window.closeScenarioModal = function () {
        document.getElementById('scenarioModal').style.display = 'none';
    };



    // LOGIQUE MODALE BONNES PRATIQUES
    const modalBp = document.getElementById('modal-bp');
    const openBpBtn = document.getElementById('open-bp-btn');
    const closeBpBtn = document.getElementById('close-modal-btn');
    const ackBtn = document.getElementById('ack-btn');

    function openBpModal() {
        // Prevent opening if not authenticated
        if (!sessionStorage.getItem('currentUser')) return;
        
        if (modalBp) {
            modalBp.style.display = 'flex';
            setTimeout(() => modalBp.classList.add('active'), 10);
        }
    }

    function closeBpModal() {
        if (modalBp) {
            modalBp.classList.remove('active');
            setTimeout(() => modalBp.style.display = 'none', 300);
        }
    }

    // LOGIQUE MODALE DE CONFIRMATION
    window.showConfirmModal = function (title, message, submessage = '') {
        return new Promise((resolve) => {
            const modal = document.getElementById('confirm-modal');
            if (!modal) {
                if (confirm(message)) resolve(true);
                else resolve(false);
                return;
            }

            const titleEl = modal.querySelector('h2');
            const msgEl = document.getElementById('confirm-message');
            const subMsgEl = document.getElementById('confirm-submessage');
            const okBtn = document.getElementById('confirm-ok-btn');
            const cancelBtn = document.getElementById('confirm-cancel-btn');

            if (titleEl && title) titleEl.innerHTML = `<i data-lucide="alert-triangle"></i> ${title}`;
            if (msgEl) msgEl.textContent = message;
            if (subMsgEl) subMsgEl.textContent = submessage;

            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
            lucide.createIcons();

            // Clone to remove listeners
            const newOk = okBtn.cloneNode(true);
            const newCancel = cancelBtn.cloneNode(true);
            okBtn.parentNode.replaceChild(newOk, okBtn);
            cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

            const closeModal = () => {
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 300);
            };

            newOk.addEventListener('click', () => {
                closeModal();
                resolve(true);
            });

            newCancel.addEventListener('click', () => {
                closeModal();
                resolve(false);
            });

            // Clic en dehors
            const clickOutside = (e) => {
                if (e.target === modal) {
                    closeModal();
                    resolve(false);
                    modal.removeEventListener('click', clickOutside);
                }
            };
            modal.addEventListener('click', clickOutside);
        });
    };

    // Affichage auto à la première visite (ou reset)
    // On enlève la condition localStorage pour forcer l'affichage si l'utilisateur le souhaite à chaque actualisation
    // Ou on peut vérifier une version dans le localStorage pour réafficher si le contenu change
    // Pour l'instant, on laisse la vérification mais on s'assure qu'elle fonctionne

    // Si l'utilisateur a demandé à ce que ça s'affiche "au lancement", 
    // peut-être qu'il pense que ça ne marche plus car il a déjà cliqué "J'ai compris".
    // On va commenter la condition pour le debug ou changer la clé pour reset

    // CODE CORRIGÉ : On vérifie si l'utilisateur n'a PAS encore acquitté ET on s'assure que le DOM est chargé
    if (!localStorage.getItem('bp_acknowledged_v2')) {
        // Petit délai pour laisser le temps au site de se charger visuellement
        setTimeout(openBpModal, 1500);
    }

    if (openBpBtn) openBpBtn.addEventListener('click', openBpModal);

    if (closeBpBtn) closeBpBtn.addEventListener('click', closeBpModal);

    if (ackBtn) {
        ackBtn.addEventListener('click', () => {
            closeBpModal();
            // On enregistre que l'utilisateur a vu le message
            localStorage.setItem('bp_acknowledged_v2', 'true');
            showToast('Top !', 'Vous connaissez maintenant les bases.', 'success');
        });
    }

    // Clic en dehors pour fermer
    window.addEventListener('click', (e) => {
        const modals = [
            document.getElementById('modal-bp'),

            document.getElementById('scenarioModal'),
            document.getElementById('course-modal'),
            document.getElementById('question-modal')
        ];

        modals.forEach(modal => {
            if (modal && e.target === modal) {
                if (modal.id === 'modal-bp') closeBpModal();
                else modal.style.display = 'none';
            }
        });
    });

    // Fermer les modales avec la touche Echap
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            const modals = [
                document.getElementById('modal-bp'),
                document.getElementById('resourceModal'),
                document.getElementById('scenarioModal'),
                document.getElementById('course-modal'),
                document.getElementById('question-modal')
            ];

            modals.forEach(modal => {
                if (modal && modal.style.display !== 'none' && modal.style.display !== '') {
                    if (modal.id === 'modal-bp') closeBpModal();
                    else modal.style.display = 'none';
                }
            });
        }
    });



});
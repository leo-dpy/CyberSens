// ==========================================
// DATABASE CLASS (Replaces database.js)
// ==========================================
class UserDB {
    constructor() {
        this.users = this.loadUsers();
    }

    loadUsers() {
        // Try to load from localStorage first
        const stored = localStorage.getItem('cybersens_users');
        if (stored) {
            return JSON.parse(stored);
        }
        
        // Default users if nothing in storage
        const defaults = [
            { id: 1, username: "tom", email: "tom@gmail.com", password: "password", xp: 1200, level: "Initié", group: "Red Team", role: "user" },
            { id: 2, username: "aaa", email: "aaa@gmail.com", password: "password", xp: 500, level: "Novice", group: "Blue Team", role: "user" },
            { id: 3, username: "Admin", email: "admin@cybersens.com", password: "admin", xp: 99999, level: "Grand Maître", group: "Staff", role: "admin" }
        ];
        this.saveUsers(defaults);
        return defaults;
    }

    saveUsers(users) {
        localStorage.setItem('cybersens_users', JSON.stringify(users));
    }

    // ==========================================
    // API METHODS
    // ==========================================

    getUsers() {
        return this.users;
    }

    findUser(email, password) {
        return this.users.find(u => u.email === email && u.password === password);
    }

    createUser(username, email, password) {
        const existing = this.users.find(u => u.email === email);
        if (existing) {
            return { success: false, message: 'Cet email est déjà utilisé.' };
        }

        const newUser = {
            id: Date.now(),
            username,
            email,
            password,
            created_at: new Date().toISOString(),
            role: 'user',
            xp: 0,
            level: "Novice",
            group: "Aucun"
        };
        
        this.users.push(newUser);
        this.saveUsers(this.users);
        
        return { success: true, user: newUser };
    }

    updateUserGroup(id, groupName) {
        const user = this.users.find(u => u.id === id);
        if (user) {
            user.group = groupName;
            this.saveUsers(this.users);
            return true;
        }
        return false;
    }

    deleteUser(id) {
        this.users = this.users.filter(u => u.id !== id);
        this.saveUsers(this.users);
        return true;
    }
}

const db = new UserDB();

document.addEventListener('DOMContentLoaded', async () => {
    // No need to wait for DB ready anymore
    // await db.ready;

    // Initialize Lucide Icons
    lucide.createIcons();

    const contentArea = document.getElementById('content-area');
    const navItems = document.querySelectorAll('.nav-item');

    // ==========================================
    // NAVIGATION & TEMPLATE LOADING
    // ==========================================

    async function loadTemplate(viewId) {
        try {
            const response = await fetch(`templates/${viewId}.html`);
            if (!response.ok) throw new Error('Template not found');
            const html = await response.text();
            contentArea.innerHTML = html;
            
            // Re-initialize icons for new content
            lucide.createIcons();
            
            // Initialize specific view logic
            if (viewId === 'profil') initAuth();
            if (viewId === 'leaderboard') loadLeaderboards();
            if (viewId === 'home' || viewId === 'cours' || viewId === 'quiz') initTiltEffect();

            // Update Active State in Sidebar
            navItems.forEach(item => {
                if (item.dataset.view === viewId) item.classList.add('active');
                else item.classList.remove('active');
            });

        } catch (error) {
            console.error('Error loading template:', error);
            contentArea.innerHTML = '<h1>Erreur 404</h1><p>Impossible de charger le contenu.</p>';
        }
    }

    // Add Click Listeners
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            const viewId = item.dataset.view;
            loadTemplate(viewId);
        });
    });

    // Load Home by default
    loadTemplate('home');


    // ==========================================
    // UI EFFECTS
    // ==========================================

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

    // Modal Logic (Global)
    const modalOverlay = document.getElementById('modal-overlay');
    const modal = document.getElementById('modal-bp');
    const closeBtn = document.getElementById('close-modal-btn');
    const ackBtn = document.getElementById('ack-btn');
    const openBtn = document.getElementById('open-bp-btn');

    function openModal() {
        modalOverlay.classList.add('active');
        setTimeout(() => modal.classList.add('active'), 10);
    }

    function closeModal() {
        modal.classList.remove('active');
        setTimeout(() => modalOverlay.classList.remove('active'), 300);
    }

    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (ackBtn) ackBtn.addEventListener('click', closeModal);
    if (modalOverlay) modalOverlay.addEventListener('click', closeModal);
    if (openBtn) openBtn.addEventListener('click', openModal);

    // Auto-open logic
    setTimeout(openModal, 500);


    // ==========================================
    // AUTHENTICATION & ADMIN LOGIC
    // ==========================================
    
    function initAuth() {
        const authContainer = document.getElementById('auth-container');
        const userDashboard = document.getElementById('user-dashboard');
        const adminDashboard = document.getElementById('admin-dashboard');
        
        const loginView = document.getElementById('login-view');
        const signupView = document.getElementById('signup-view');
        const loginToggleContent = document.getElementById('login-toggle-content');
        const signupToggleContent = document.getElementById('signup-toggle-content');

        // Check Session
        const sessionUser = JSON.parse(sessionStorage.getItem('currentUser'));
        if (sessionUser) {
            updateUI(sessionUser);
        }

        // Toggle Forms
        document.getElementById('show-signup-btn')?.addEventListener('click', () => {
            loginView.style.display = 'none';
            signupView.style.display = 'block';
            loginToggleContent.style.display = 'none';
            signupToggleContent.style.display = 'block';
        });

        document.getElementById('show-login-btn')?.addEventListener('click', () => {
            signupView.style.display = 'none';
            loginView.style.display = 'block';
            signupToggleContent.style.display = 'none';
            loginToggleContent.style.display = 'block';
        });

        // Handle Login
        document.getElementById('login-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value;
            const password = document.getElementById('login-password').value;
            const errorMsg = document.getElementById('login-error');

            const user = db.findUser(email, password);

            if (user) {
                loginUser(user);
                errorMsg.style.display = 'none';
            } else {
                errorMsg.textContent = 'Identifiants invalides.';
                errorMsg.style.display = 'block';
            }
        });

        // Handle Signup
        document.getElementById('signup-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            const username = document.getElementById('signup-username').value;
            const email = document.getElementById('signup-email').value;
            const password = document.getElementById('signup-password').value;
            const errorMsg = document.getElementById('signup-error');

            const result = db.createUser(username, email, password);

            if (result.success) {
                loginUser(result.user);
                errorMsg.style.display = 'none';
            } else {
                errorMsg.textContent = result.message;
                errorMsg.style.display = 'block';
            }
        });

        // Handle Logout
        document.getElementById('logout-btn')?.addEventListener('click', () => {
            sessionStorage.removeItem('currentUser');
            loadTemplate('profil'); // Reload profile view to reset
        });

        function loginUser(user) {
            sessionStorage.setItem('currentUser', JSON.stringify(user));
            updateUI(user);
        }

        function updateUI(user) {
            if (!authContainer) return;
            
            // Hide Auth, Show Dashboard
            authContainer.style.display = 'none';
            userDashboard.style.display = 'block';

            // Update User Info
            document.getElementById('user-name-display').textContent = user.username;
            document.getElementById('user-level-display').textContent = user.level;
            document.getElementById('user-xp-display').textContent = user.xp;

            // Admin Check
            if (user.role === 'admin') {
                adminDashboard.style.display = 'block';
                loadAdminTable();
            }
        }
    }

    function loadAdminTable() {
        const tbody = document.getElementById('admin-users-list');
        if (!tbody) return;
        
        tbody.innerHTML = '';
        const users = db.getUsers();

        users.forEach(u => {
            const tr = document.createElement('tr');
            
            // Group Select Options
            const groups = ['Aucun', 'Red Team', 'Blue Team', 'Purple Team', 'Staff'];
            let groupOptions = '';
            groups.forEach(g => {
                groupOptions += `<option value="${g}" ${u.group === g ? 'selected' : ''}>${g}</option>`;
            });

            tr.innerHTML = `
                <td>${u.id}</td>
                <td>${u.username}</td>
                <td>${u.email}</td>
                <td>
                    <select class="group-select form-input" data-id="${u.id}" style="padding: 5px; width: auto;">
                        ${groupOptions}
                    </select>
                </td>
                <td><span style="color: ${u.role === 'admin' ? 'var(--accent-color)' : 'inherit'}">${u.role}</span></td>
                <td>
                    ${u.role !== 'admin' ? `<button class="btn-delete" data-id="${u.id}" style="color: var(--accent-color); background: none; border: 1px solid var(--accent-color); padding: 5px 10px; border-radius: 5px; cursor: pointer;">Supprimer</button>` : '-'}
                </td>
            `;
            tbody.appendChild(tr);
        });

        // Add Delete Listeners
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const id = parseInt(e.target.dataset.id);
                if (confirm('Confirmer la suppression de cet agent ?')) {
                    db.deleteUser(id);
                    loadAdminTable();
                }
            });
        });

        // Add Group Change Listeners
        document.querySelectorAll('.group-select').forEach(select => {
            select.addEventListener('change', (e) => {
                const id = parseInt(e.target.dataset.id);
                const newGroup = e.target.value;
                db.updateUserGroup(id, newGroup);
            });
        });
    }

    // ==========================================
    // LEADERBOARD LOGIC
    // ==========================================
    
    function loadLeaderboards() {
        const users = db.getUsers();
        
        // Group Leaderboard Calculation
        const groups = {};
        users.forEach(u => {
            const g = u.group || 'Aucun';
            if (g === 'Aucun' || g === 'Staff') return; 
            
            if (!groups[g]) groups[g] = { name: g, totalXp: 0, members: 0 };
            groups[g].totalXp += u.xp;
            groups[g].members++;
        });

        const sortedGroups = Object.values(groups).sort((a, b) => b.totalXp - a.totalXp);
        const groupTbody = document.getElementById('group-leaderboard-list');
        if (!groupTbody) return;
        
        groupTbody.innerHTML = '';

        if (sortedGroups.length === 0) {
            groupTbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:#666;">Aucun groupe actif</td></tr>';
        } else {
            sortedGroups.forEach((g, index) => {
                const rank = index + 1;
                let rankBadge = rank;
                if (rank <= 3) rankBadge = `<div class="rank-badge rank-${rank}">${rank}</div>`;

                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${rankBadge}</td>
                    <td style="color: var(--primary-color); font-weight: bold;">${g.name}</td>
                    <td>${g.totalXp.toLocaleString()}</td>
                    <td>${g.members}</td>
                `;
                groupTbody.appendChild(tr);
            });
        }
    }
});

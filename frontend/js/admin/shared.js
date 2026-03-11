document.addEventListener('DOMContentLoaded', () => {
    lucide.createIcons();
    injectConfirmModal();
});

// Logique Modale Confirmation
function injectConfirmModal() {
    const modalHtml = `
    <div id="admin-confirm-modal" class="modal" style="z-index: 9999;">
        <div class="modal-content" style="max-width: 400px; border: 1px solid var(--danger, #ff003c); box-shadow: 0 0 30px rgba(255, 0, 60, 0.2); background: #08080c;">
            <div class="modal-header" style="border-bottom: none; padding-bottom: 0;">
                <h2 style="color: var(--danger, #ff003c); font-size: 1.25rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i data-lucide="alert-triangle"></i> Confirmation
                </h2>
            </div>
            <div class="modal-body" style="text-align: center; margin: 1.5rem 0;">
                <p id="admin-confirm-message" style="font-size: 1.1rem; color: #fff; margin-bottom: 0.5rem; font-weight: 500;">Êtes-vous sûr ?</p>
                <p style="font-size: 0.9rem; color: #888; margin: 0;">Cette action est irréversible.</p>
            </div>
            <div class="modal-footer" style="display: flex; gap: 1rem; justify-content: center; padding-top: 0; border: none;">
                <button class="btn btn-outline" onclick="closeAdminConfirm(false)" style="padding: 0.5rem 1.5rem;">Annuler</button>
                <button class="btn" onclick="closeAdminConfirm(true)" style="background: var(--danger, #ff003c); color: white; border: none; padding: 0.5rem 1.5rem;">Confirmer</button>
            </div>
        </div>
    </div>`;

    if (!document.getElementById('admin-confirm-modal')) {
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        lucide.createIcons();
    }
}

let confirmResolve = null;

window.showConfirmModal = function (message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('admin-confirm-modal');
        const msgEl = document.getElementById('admin-confirm-message');
        if (msgEl) msgEl.textContent = message;

        confirmResolve = resolve;

        if (modal) {
            modal.style.display = 'flex';
            setTimeout(() => modal.classList.add('active'), 10);
        } else {
            resolve(confirm(message));
        }
    });
};

window.closeAdminConfirm = function (result) {
    const modal = document.getElementById('admin-confirm-modal');
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
    }
    if (confirmResolve) {
        confirmResolve(result);
        confirmResolve = null;
    }
};

window.confirmAction = async function (event, message) {
    event.preventDefault();
    // Support pour les liens (<a>) et les formulaires/boutons
    const targetUrl = event.currentTarget.href;

    const confirmed = await showConfirmModal(message);
    if (confirmed) {
        if (targetUrl) {
            window.location.href = targetUrl;
        } else {
            // Si c'est un bouton dans un formulaire ou autre
            // Mais ici on gère principalement les liens <a> href="..."
        }
    }
    return false;
};

// Fermer au clic extérieur
window.addEventListener('click', function(event) {
    const modal = document.getElementById('admin-confirm-modal');
    if (event.target === modal) {
        closeAdminConfirm(false);
    }
});


// Logique Modale Standard
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        // Forcer le redessin
        modal.offsetHeight;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
    }
}

// Fermer la modale au clic en dehors
window.onclick = function (event) {
    if (event.target.classList.contains('admin-modal')) {
        closeModal(event.target.id);
    }
}

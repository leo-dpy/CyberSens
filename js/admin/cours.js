let orderMode = false;
let sortable = null;
let orderChanged = false;

document.getElementById('toggleOrderMode').addEventListener('click', function () {
    orderMode = !orderMode;
    const panel = document.getElementById('orderModePanel');
    const handleCols = document.querySelectorAll('.order-handle-col');
    const actionsCols = document.querySelectorAll('.actions-col');
    const tableBody = document.getElementById('coursesTableBody');

    if (orderMode) {
        panel.style.display = 'block';
        tableBody.classList.add('reorder-active');
        handleCols.forEach(col => col.style.display = 'table-cell');
        actionsCols.forEach(col => col.style.display = 'none');
        this.innerHTML = '<i data-lucide="x"></i> Annuler';
        this.classList.remove('btn-outline');
        this.classList.add('btn-danger');

        // Initialiser SortableJS
        sortable = new Sortable(document.getElementById('coursesTableBody'), {
            handle: '.order-handle-col',
            animation: 150,
            ghostClass: 'sortable-ghost',
            onEnd: function () {
                orderChanged = true;
                document.getElementById('saveOrder').disabled = false;
                updateOrderBadges();
            }
        });
    } else {
        panel.style.display = 'none';
        tableBody.classList.remove('reorder-active');
        handleCols.forEach(col => col.style.display = 'none');
        actionsCols.forEach(col => col.style.display = 'table-cell');
        this.innerHTML = '<i data-lucide="arrow-up-down"></i> Réorganiser';
        this.classList.remove('btn-danger');
        this.classList.add('btn-outline');

        if (sortable) {
            sortable.destroy();
            sortable = null;
        }

        if (orderChanged) {
            location.reload();
        }
    }
    lucide.createIcons();
});

function updateOrderBadges() {
    const rows = document.querySelectorAll('#coursesTableBody tr');
    // Corrigeons la logique
    const badges = document.querySelectorAll('#coursesTableBody .order-badge');
    badges.forEach((badge, index) => {
        badge.textContent = index + 1;
    });
}

document.getElementById('saveOrder').addEventListener('click', function () {
    const rows = document.querySelectorAll('#coursesTableBody tr');
    const orders = [];

    rows.forEach((row, index) => {
        orders.push({
            id: row.dataset.id,
            order: index + 1
        });
    });

    const formData = new FormData();
    formData.append('action', 'update_order');
    formData.append('orders', JSON.stringify(orders));

    fetch('cours.php', {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.innerHTML = '<i data-lucide="check"></i> Sauvegardé !';
                this.disabled = true;
                orderChanged = false;
                lucide.createIcons();

                setTimeout(() => {
                    this.innerHTML = '<i data-lucide="check"></i> Sauvegarder l\'ordre';
                    this.disabled = false;
                }, 2000);
            }
        });
});

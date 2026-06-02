const rows = Array.from(document.querySelectorAll('[data-admin-row]'));
const searchInput = document.querySelector('[data-admin-search]');
const countOutput = document.querySelector('[data-admin-count]');
const editForm = document.querySelector('[data-admin-edit-form]');
const editStatus = document.querySelector('[data-admin-edit-status]');

function updateVisibleCount() {
    if (!countOutput) {
        return;
    }

    const visibleRows = rows.filter((row) => !row.hidden).length;
    countOutput.textContent = `${visibleRows} résultat${visibleRows > 1 ? 's' : ''}`;
}

function fillEditForm(row) {
    if (!editForm) {
        return;
    }

    editForm.reservation_id.value = row.dataset.reservationId || '';
    editForm.visiteur_id.value = row.dataset.visiteurId || '';
    editForm.nom.value = row.dataset.nom || '';
    editForm.prenom.value = row.dataset.prenom || '';
    editForm.moyen_comm.value = row.dataset.contact || '';
    editForm.categorie_id.value = row.dataset.categorieId || '';
    editForm.salle_creneaux_id.value = row.dataset.slotId || '';
    editForm.participe_buffet.value = row.dataset.buffet || '0';
    editForm.nombre_personnes.value = row.dataset.people || '1';

    rows.forEach((item) => item.classList.remove('is-selected'));
    row.classList.add('is-selected');

    if (editStatus) {
        editStatus.textContent = `Réservation #${row.dataset.reservationId} chargée dans le formulaire.`;
    }
}

searchInput?.addEventListener('input', () => {
    const query = searchInput.value.trim().toLowerCase();

    rows.forEach((row) => {
        const searchableText = row.dataset.searchText || row.textContent.toLowerCase();
        row.hidden = !searchableText.includes(query);
    });

    updateVisibleCount();
});

rows.forEach((row) => {
    row.querySelector('[data-admin-edit]')?.addEventListener('click', () => fillEditForm(row));
});

document.querySelectorAll('[data-admin-delete-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm('Supprimer définitivement cette réservation ?')) {
            event.preventDefault();
        }
    });
});

editForm?.querySelector('[data-admin-delete-current]')?.addEventListener('click', (event) => {
    if (!window.confirm('Supprimer définitivement la réservation sélectionnée ?')) {
        event.preventDefault();
    }
});

if (rows[0] && editForm) {
    fillEditForm(rows[0]);
}

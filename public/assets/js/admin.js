const rows = Array.from(document.querySelectorAll('[data-admin-row]'));
const searchInput = document.querySelector('[data-admin-search]');
const countOutput = document.querySelector('[data-admin-count]');
const editForm = document.querySelector('[data-admin-edit-form]');
const editStatus = document.querySelector('[data-admin-edit-status]');
const availabilityMatrix = document.querySelector('[data-admin-availability-matrix]');
const availabilityCells = Array.from(document.querySelectorAll('[data-admin-availability-cell]'));
const refreshStatus = document.querySelector('[data-admin-refresh-status]');

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

function applyAvailabilityData(data) {
    if (!data || !Array.isArray(data.cells)) {
        return;
    }

    data.cells.forEach((cell) => {
        const targetCell = availabilityCells.find((item) => (
            item.dataset.room === String(cell.room)
            && item.dataset.time === String(cell.time)
        ));

        if (!targetCell) {
            return;
        }

        const remaining = Number(cell.remaining);
        const capacity = Number(cell.capacity);

        targetCell.textContent = `${remaining}/${capacity}`;
        targetCell.dataset.capacity = String(capacity);
        targetCell.classList.toggle('is-full', remaining === 0);
    });

    if (refreshStatus && data.updated_at) {
        refreshStatus.textContent = `Dernière mise à jour : ${data.updated_at}`;
        refreshStatus.classList.remove('is-error');
    }
}

async function refreshAvailabilityTable() {
    if (!availabilityMatrix) {
        return;
    }

    const day = availabilityMatrix.dataset.selectedDay || '';
    const url = `admin_disponibilites.php?day=${encodeURIComponent(day)}`;

    try {
        const response = await fetch(url, {
            cache: 'no-store',
            headers: {
                Accept: 'application/json',
            },
        });

        if (!response.ok) {
            throw new Error('Réponse serveur invalide.');
        }

        const data = await response.json();
        applyAvailabilityData(data);
    } catch (error) {
        if (refreshStatus) {
            refreshStatus.textContent = 'Mise à jour automatique indisponible.';
            refreshStatus.classList.add('is-error');
        }
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

if (availabilityMatrix) {
    refreshAvailabilityTable();
    setInterval(refreshAvailabilityTable, 10000);
}

const rows = Array.from(document.querySelectorAll('[data-admin-row]'));
const searchInput = document.querySelector('[data-admin-search]');
const countOutput = document.querySelector('[data-admin-count]');
const editForm = document.querySelector('[data-admin-edit-form]');
const editStatus = document.querySelector('[data-admin-edit-status]');

function updateVisibleCount() {
    const visibleRows = rows.filter((row) => !row.hidden).length;
    countOutput.textContent = `${visibleRows} résultat${visibleRows > 1 ? 's' : ''}`;
}

function fillEditForm(row) {
    editForm.nom.value = row.dataset.nom;
    editForm.prenom.value = row.dataset.prenom;
    editForm.jour.value = row.dataset.jour;
    editForm.heure.value = row.dataset.heure;
    editForm.salle.value = row.dataset.salle;
    editForm.places.value = row.dataset.places;

    rows.forEach((item) => item.classList.remove('is-selected'));
    row.classList.add('is-selected');
    editStatus.textContent = `Réservation de ${row.dataset.prenom} ${row.dataset.nom} chargée dans le panneau d'édition.`;
}

searchInput?.addEventListener('input', () => {
    const query = searchInput.value.trim().toLowerCase();

    rows.forEach((row) => {
        const searchableText = [
            row.dataset.nom,
            row.dataset.prenom,
            row.dataset.contact,
            row.dataset.jour,
            row.dataset.heure,
            row.dataset.salle,
        ].join(' ').toLowerCase();

        row.hidden = !searchableText.includes(query);
    });

    updateVisibleCount();
});

rows.forEach((row) => {
    row.querySelector('[data-admin-edit]')?.addEventListener('click', () => fillEditForm(row));

    row.querySelector('[data-admin-delete]')?.addEventListener('click', () => {
        row.hidden = true;
        editStatus.textContent = `Suppression préparée pour ${row.dataset.prenom} ${row.dataset.nom}. À relier à la BDD ensuite.`;
        updateVisibleCount();
    });
});

editForm?.addEventListener('submit', (event) => {
    event.preventDefault();
    editStatus.textContent = 'Modification enregistrée dans la maquette. La sauvegarde réelle sera ajoutée avec PHP/MySQL.';
});

document.querySelector('[data-admin-delete-current]')?.addEventListener('click', () => {
    editStatus.textContent = 'Suppression prête. Il faudra appeler une requête DELETE quand la BDD sera branchée.';
});

if (rows[0]) {
    fillEditForm(rows[0]);
}

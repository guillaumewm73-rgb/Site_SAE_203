const registrationDataElement = document.getElementById('registration-data');
const registrationData = JSON.parse(registrationDataElement.textContent);

const form = document.querySelector('[data-registration-form]');
const slotList = document.querySelector('[data-slot-list]');
const addSlotButton = document.querySelector('[data-add-slot]');
const feedback = document.querySelector('[data-form-feedback]');
const submitButton = document.querySelector('[data-submit-registration]');
const passwordInput = document.querySelector('[data-password-input]');
const passwordToggle = document.querySelector('[data-password-toggle]');

function fillTimeOptions(timeSelect, day, currentValue) {
    const times = registrationData.days[day].times;

    timeSelect.innerHTML = '';

    times.forEach((time) => {
        const option = document.createElement('option');
        option.value = time;
        option.textContent = time;
        timeSelect.appendChild(option);
    });

    timeSelect.value = times.includes(currentValue) ? currentValue : times[0];
}

function updateSlotNames(slot, index) {
    slot.querySelector('[data-slot-day]').name = `slots[${index}][day]`;
    slot.querySelector('[data-slot-time]').name = `slots[${index}][time]`;
    slot.querySelector('[data-slot-room]').name = `slots[${index}][room]`;
    slot.querySelector('[data-slot-people]').name = `slots[${index}][people]`;
}

function updateSlot(slot, index) {
    const daySelect = slot.querySelector('[data-slot-day]');
    const timeSelect = slot.querySelector('[data-slot-time]');
    const roomSelect = slot.querySelector('[data-slot-room]');
    const peopleSelect = slot.querySelector('[data-slot-people]');
    const capacity = slot.querySelector('[data-slot-capacity]');
    const title = slot.querySelector('[data-slot-title]');
    const removeButton = slot.querySelector('[data-remove-slot]');

    fillTimeOptions(timeSelect, daySelect.value, timeSelect.value);
    updateSlotNames(slot, index);

    const key = `${daySelect.value}|${timeSelect.value}|${roomSelect.value}`;
    const remainingPlaces = registrationData.availability[key] ?? 12;
    const selectedPeople = Number(peopleSelect.value);
    const placesAfterSelection = remainingPlaces - selectedPeople;
    const percentage = Math.max(0, Math.min(100, (placesAfterSelection / 12) * 100));
    const room = registrationData.rooms[roomSelect.value];

    title.textContent = `Créneau ${index + 1}`;
    removeButton.hidden = slotList.children.length === 1;

    capacity.classList.toggle('is-low', placesAfterSelection <= 3 && placesAfterSelection > 0);
    capacity.classList.toggle('is-full', placesAfterSelection < 0);

    const capacityMessage = placesAfterSelection < 0
        ? `Pas assez de places pour ${selectedPeople} personnes.`
        : `${placesAfterSelection} places restantes après votre sélection.`;

    capacity.innerHTML = `
        <strong>Salle ${roomSelect.value} - ${timeSelect.value}</strong>
        <span>${room.title} · ${remainingPlaces} places disponibles sur 12 · ${capacityMessage}</span>
        <i aria-hidden="true"><b style="width: ${percentage}%"></b></i>
    `;
}

function updateAllSlots() {
    Array.from(slotList.children).forEach((slot, index) => {
        updateSlot(slot, index);
    });
}

slotList.addEventListener('change', (event) => {
    if (event.target.matches('[data-slot-day], [data-slot-time], [data-slot-room], [data-slot-people]')) {
        updateAllSlots();
    }
});

slotList.addEventListener('click', (event) => {
    if (!event.target.matches('[data-remove-slot]')) {
        return;
    }

    event.target.closest('[data-slot-entry]').remove();
    updateAllSlots();
});

addSlotButton.addEventListener('click', () => {
    const newSlot = slotList.firstElementChild.cloneNode(true);

    newSlot.querySelector('[data-slot-day]').value = Object.keys(registrationData.days)[0];
    newSlot.querySelector('[data-slot-room]').value = '001';
    newSlot.querySelector('[data-slot-people]').value = '1';
    slotList.appendChild(newSlot);
    updateAllSlots();
});

passwordToggle?.addEventListener('click', () => {
    if (!passwordInput) {
        return;
    }

    const isVisible = passwordInput.type === 'text';

    passwordInput.type = isVisible ? 'password' : 'text';
    passwordToggle.classList.toggle('is-visible', !isVisible);
    passwordToggle.setAttribute('aria-pressed', String(!isVisible));
    passwordToggle.setAttribute(
        'aria-label',
        isVisible ? 'Afficher le mot de passe' : 'Masquer le mot de passe'
    );
});

form.addEventListener('submit', (event) => {
    if (!form.checkValidity()) {
        event.preventDefault();
        const firstInvalidField = form.querySelector(':invalid');

        feedback.textContent = 'Complétez les champs obligatoires avant de confirmer la réservation.';
        feedback.classList.remove('is-success');
        feedback.classList.add('is-error');
        firstInvalidField?.focus();
        return;
    }

    const hasOverbookedSlot = Array.from(document.querySelectorAll('[data-slot-capacity]'))
        .some((capacity) => capacity.classList.contains('is-full'));

    if (hasOverbookedSlot) {
        event.preventDefault();
        feedback.textContent = 'Réduisez le nombre de personnes : au moins un créneau dépasse les places disponibles.';
        feedback.classList.remove('is-success');
        feedback.classList.add('is-error');
        return;
    }

    const slotCount = slotList.children.length;
    const peopleCount = Array.from(document.querySelectorAll('[data-slot-people]'))
        .reduce((total, select) => total + Number(select.value), 0);

    feedback.textContent = `Envoi de votre réservation : ${slotCount} créneau${slotCount > 1 ? 'x' : ''}, ${peopleCount} personne${peopleCount > 1 ? 's' : ''} au total.`;
    feedback.classList.add('is-success');
    feedback.classList.remove('is-error');

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Réservation en cours...';
    }
});

updateAllSlots();

if (window.location.hash === '#registration-result') {
    document.getElementById('registration-result')?.scrollIntoView({ block: 'start' });
}

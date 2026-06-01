const registrationDataElement = document.getElementById('registration-data');
const registrationData = JSON.parse(registrationDataElement.textContent);

const form = document.querySelector('[data-registration-form]');
const dayToggles = Array.from(document.querySelectorAll('[data-day-toggle]'));
const slotList = document.querySelector('[data-slot-list]');
const addSlotButton = document.querySelector('[data-add-slot]');
const feedback = document.querySelector('[data-form-feedback]');

function getSelectedDays() {
    const selectedDays = dayToggles
        .filter((toggle) => toggle.checked)
        .map((toggle) => toggle.value);

    if (selectedDays.length > 0) {
        return selectedDays;
    }

    dayToggles[0].checked = true;
    return [dayToggles[0].value];
}

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
}

function updateSlot(slot, index) {
    const selectedDays = getSelectedDays();
    const daySelect = slot.querySelector('[data-slot-day]');
    const timeSelect = slot.querySelector('[data-slot-time]');
    const roomSelect = slot.querySelector('[data-slot-room]');
    const capacity = slot.querySelector('[data-slot-capacity]');
    const title = slot.querySelector('[data-slot-title]');
    const removeButton = slot.querySelector('[data-remove-slot]');

    Array.from(daySelect.options).forEach((option) => {
        option.disabled = !selectedDays.includes(option.value);
    });

    if (!selectedDays.includes(daySelect.value)) {
        daySelect.value = selectedDays[0];
    }

    fillTimeOptions(timeSelect, daySelect.value, timeSelect.value);
    updateSlotNames(slot, index);

    const key = `${daySelect.value}|${timeSelect.value}|${roomSelect.value}`;
    const remainingPlaces = registrationData.availability[key] ?? 12;
    const percentage = Math.max(0, Math.min(100, (remainingPlaces / 12) * 100));
    const room = registrationData.rooms[roomSelect.value];

    title.textContent = `Créneau ${index + 1}`;
    removeButton.hidden = slotList.children.length === 1;

    capacity.classList.toggle('is-low', remainingPlaces <= 3 && remainingPlaces > 0);
    capacity.classList.toggle('is-full', remainingPlaces === 0);

    capacity.innerHTML = `
        <strong>Salle ${roomSelect.value} - ${timeSelect.value}</strong>
        <span>${room.title} · ${remainingPlaces} places restantes sur 12</span>
        <i aria-hidden="true"><b style="width: ${percentage}%"></b></i>
    `;
}

function updateAllSlots() {
    Array.from(slotList.children).forEach((slot, index) => {
        updateSlot(slot, index);
    });
}

dayToggles.forEach((toggle) => {
    toggle.addEventListener('change', updateAllSlots);
});

slotList.addEventListener('change', (event) => {
    if (event.target.matches('[data-slot-day], [data-slot-time], [data-slot-room]')) {
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
    const selectedDays = getSelectedDays();
    const newSlot = slotList.firstElementChild.cloneNode(true);

    newSlot.querySelector('[data-slot-day]').value = selectedDays[0];
    newSlot.querySelector('[data-slot-room]').value = '001';
    slotList.appendChild(newSlot);
    updateAllSlots();
});

form.addEventListener('submit', (event) => {
    event.preventDefault();

    if (!form.reportValidity()) {
        return;
    }

    const slotCount = slotList.children.length;
    feedback.textContent = `Votre demande est prête : ${slotCount} créneau${slotCount > 1 ? 'x' : ''} pour 1 visiteur.`;
    feedback.classList.add('is-success');
});

updateAllSlots();

const slides = document.querySelectorAll('.carousel-slide');
const buttons = document.querySelectorAll('[data-slide-button]');

let currentSlide = 0;

function showSlide(index) {
    slides[currentSlide].classList.remove('is-visible');
    buttons[currentSlide].classList.remove('is-active');

    currentSlide = index;

    slides[currentSlide].classList.add('is-visible');
    buttons[currentSlide].classList.add('is-active');
}

buttons.forEach((button) => {
    button.addEventListener('click', () => {
        const index = Number(button.dataset.slideButton);
        showSlide(index);
    });
});

setInterval(() => {
    const nextSlide = (currentSlide + 1) % slides.length;
    showSlide(nextSlide);
}, 4500);


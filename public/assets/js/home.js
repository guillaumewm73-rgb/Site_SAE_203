const slides = document.querySelectorAll('.carousel-slide');
const buttons = document.querySelectorAll('[data-slide-button]');

// Index de la slide actuellement visible.
let currentSlide = 0;

function showSlide(index) {
    // On masque l'ancienne slide et on désactive son point de navigation.
    slides[currentSlide].classList.remove('is-visible');
    buttons[currentSlide].classList.remove('is-active');

    // Puis on mémorise et affiche la nouvelle slide.
    currentSlide = index;

    slides[currentSlide].classList.add('is-visible');
    buttons[currentSlide].classList.add('is-active');
}

buttons.forEach((button) => {
    // Les points permettent aussi de changer manuellement d'image.
    button.addEventListener('click', () => {
        const index = Number(button.dataset.slideButton);
        showSlide(index);
    });
});

setInterval(() => {
    // Carrousel automatique : modulo permet de revenir à la première image après la dernière.
    const nextSlide = (currentSlide + 1) % slides.length;
    showSlide(nextSlide);
}, 4500);

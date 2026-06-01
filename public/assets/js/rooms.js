const sectionLinks = new Map(
    Array.from(document.querySelectorAll('[data-room-nav-link]')).map((link) => [
        link.getAttribute('href')?.replace('#', '') ?? '',
        link,
    ]),
);

const sections = Array.from(document.querySelectorAll('[data-room-section]'));

function setActiveSection(sectionId) {
    sectionLinks.forEach((link) => link.classList.remove('is-active'));

    const activeLink = sectionLinks.get(sectionId);
    if (activeLink) {
        activeLink.classList.add('is-active');
    }
}

if (sections.length && sectionLinks.size) {
    const observer = new IntersectionObserver(
        (entries) => {
            const visibleEntry = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (visibleEntry?.target.id) {
                setActiveSection(visibleEntry.target.id);
            }
        },
        {
            rootMargin: '-30% 0px -55% 0px',
            threshold: [0.15, 0.3, 0.5, 0.75],
        },
    );

    sections.forEach((section) => observer.observe(section));

    const initialHash = window.location.hash.replace('#', '');
    if (initialHash && sectionLinks.has(initialHash)) {
        setActiveSection(initialHash);
    } else if (sections[0]?.id) {
        setActiveSection(sections[0].id);
    }
}

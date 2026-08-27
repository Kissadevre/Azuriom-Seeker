document.addEventListener('DOMContentLoaded', () => {
    const allTriggers = [...document.querySelectorAll('[data-seeker-gallery-trigger]')];

    document.querySelectorAll('[data-seeker-gallery]').forEach((gallery) => {
        const triggers = allTriggers.filter((trigger) => trigger.dataset.seekerGalleryTrigger === gallery.id);
        const preview = gallery.querySelector('[data-gallery-preview]');
        const caption = gallery.querySelector('[data-gallery-caption]');
        const counter = gallery.querySelector('[data-gallery-count]');
        const previous = gallery.querySelector('[data-gallery-previous]');
        const next = gallery.querySelector('[data-gallery-next]');
        let currentIndex = 0;

        if (!preview || triggers.length === 0) {
            return;
        }

        const render = (index) => {
            currentIndex = (index + triggers.length) % triggers.length;
            const trigger = triggers[currentIndex];

            preview.src = trigger.dataset.gallerySrc;
            preview.alt = trigger.dataset.galleryAlt || '';
            caption.textContent = trigger.dataset.galleryName || '';
            counter.textContent = gallery.dataset.galleryCounter
                .replace(':current', currentIndex + 1)
                .replace(':total', triggers.length);

            [previous, next].forEach((button) => {
                if (button) {
                    button.hidden = triggers.length < 2;
                }
            });

            if (triggers.length > 1) {
                [currentIndex - 1, currentIndex + 1].forEach((neighborIndex) => {
                    const neighbor = triggers[(neighborIndex + triggers.length) % triggers.length];
                    const preload = new Image();
                    preload.src = neighbor.dataset.gallerySrc;
                });
            }
        };

        triggers.forEach((trigger, index) => trigger.addEventListener('click', () => render(index)));
        previous?.addEventListener('click', () => render(currentIndex - 1));
        next?.addEventListener('click', () => render(currentIndex + 1));

        gallery.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') {
                event.preventDefault();
                render(currentIndex - 1);
            } else if (event.key === 'ArrowRight') {
                event.preventDefault();
                render(currentIndex + 1);
            }
        });

        gallery.addEventListener('hidden.bs.modal', () => {
            preview.removeAttribute('src');
            preview.alt = '';
        });
    });
});

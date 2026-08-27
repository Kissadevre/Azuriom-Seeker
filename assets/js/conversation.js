document.addEventListener('DOMContentLoaded', () => {
    const history = document.querySelector('[data-seeker-message-history]');

    if (history) {
        const scrollToLatest = () => {
            history.scrollTop = history.scrollHeight;
        };

        window.requestAnimationFrame(scrollToLatest);

        history.querySelectorAll('img').forEach((image) => {
            if (!image.complete) {
                image.addEventListener('load', scrollToLatest, { once: true });
            }
        });
    }

    const attachmentInput = document.querySelector('[data-seeker-attachment-input]');
    const attachmentName = document.querySelector('[data-seeker-attachment-name]');

    if (!attachmentInput || !attachmentName) {
        return;
    }

    attachmentInput.addEventListener('change', () => {
        attachmentName.textContent = attachmentInput.files?.[0]?.name
            || attachmentName.dataset.emptyLabel
            || '';
    });
});

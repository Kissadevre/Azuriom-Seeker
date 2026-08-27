document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-portfolio-choice]').forEach((choice) => {
        const form = choice.closest('form');

        if (!form) {
            return;
        }

        const radios = choice.querySelectorAll('input[name="portfolio_type"]');
        const panels = form.querySelectorAll('[data-portfolio-panel]');

        const updatePanels = () => {
            const selected = choice.querySelector('input[name="portfolio_type"]:checked')?.value;

            panels.forEach((panel) => {
                const active = panel.dataset.portfolioPanel === selected;
                panel.classList.toggle('d-none', !active);
                panel.querySelectorAll('input').forEach((input) => {
                    input.disabled = !active;
                });
            });

            panels.forEach((panel) => {
                if (panel.dataset.portfolioPanel !== selected) {
                    return;
                }

                const fileInput = panel.querySelector('input[type="file"]');

                if (!fileInput) {
                    return;
                }

                const hasExisting = panel.dataset.hasExisting === 'true';
                const existingImages = panel.querySelectorAll('input[name="remove_images[]"]');
                const keepsAnImage = [...existingImages].some((input) => !input.checked);
                fileInput.required = !hasExisting || (selected === 'images' && existingImages.length > 0 && !keepsAnImage);
            });

            const externalUrl = form.querySelector('input[name="portfolio_url"]');

            if (externalUrl) {
                externalUrl.required = selected === 'external';
            }
        };

        radios.forEach((radio) => radio.addEventListener('change', updatePanels));
        form.querySelectorAll('input[name="remove_images[]"]').forEach((input) => input.addEventListener('change', updatePanels));
        updatePanels();
    });

    document.querySelectorAll('[data-pricing-choice]').forEach((choice) => {
        const form = choice.closest('form');

        if (!form) {
            return;
        }

        const radios = choice.querySelectorAll('input[name="pricing_type"]');
        const panels = form.querySelectorAll('[data-pricing-panel]');

        const updatePanels = () => {
            const selected = choice.querySelector('input[name="pricing_type"]:checked')?.value;

            panels.forEach((panel) => {
                const active = panel.dataset.pricingPanel === selected;
                panel.classList.toggle('d-none', !active);
                panel.querySelectorAll('input').forEach((input) => {
                    input.disabled = !active;
                    input.required = active;
                });
            });
        };

        radios.forEach((radio) => radio.addEventListener('change', updatePanels));
        updatePanels();
    });
});

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

            const externalUrl = form.querySelector('input[name="portfolio_url"]');
            const imageFiles = form.querySelector('input[name="images[]"]');

            if (externalUrl) {
                externalUrl.required = selected === 'external';
            }

            if (imageFiles) {
                imageFiles.required = selected === 'images' && !form.querySelector('input[name="remove_images[]"]');
            }
        };

        radios.forEach((radio) => radio.addEventListener('change', updatePanels));
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

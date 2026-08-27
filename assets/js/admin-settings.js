document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-portfolio-settings]').forEach((group) => {
        const form = group.closest('form');
        const switches = [...group.querySelectorAll('input[type="checkbox"][data-portfolio-setting]')];
        const clientError = group.querySelector('[data-portfolio-settings-client-error]');

        if (!form || switches.length === 0) {
            return;
        }

        const validate = (showError = false) => {
            const valid = switches.some((input) => input.checked);
            switches[0].setCustomValidity(valid ? '' : group.dataset.portfolioSettingsMessage);
            clientError?.classList.toggle('d-none', valid || !showError);

            return valid;
        };

        switches.forEach((input) => input.addEventListener('change', () => validate(true)));
        form.addEventListener('submit', (event) => {
            if (!validate(true)) {
                event.preventDefault();
                switches[0].reportValidity();
            }
        });

        validate(false);
    });
});

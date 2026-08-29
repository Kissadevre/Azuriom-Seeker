document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bootstrap-icon-input]').forEach((input) => {
        const preview = input.closest('[data-bootstrap-icon-field]')?.querySelector('[data-bootstrap-icon-preview]');
        const iconPattern = /^bi-[a-z0-9]+(?:-[a-z0-9]+)*$/;

        if (!preview) {
            return;
        }

        const updatePreview = () => {
            const icon = input.value.trim();
            preview.className = `bi ${iconPattern.test(icon) ? icon : 'bi-question-circle'}`;
        };

        input.addEventListener('input', updatePreview);
        updatePreview();
    });

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

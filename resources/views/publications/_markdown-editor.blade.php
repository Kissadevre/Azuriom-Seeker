@push('styles')
    <link href="{{ asset('vendor/easymde/easymde.min.css') }}" rel="stylesheet">
    <style>
        .seeker-markdown-editor .editor-toolbar {
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-bottom: 0;
            border-radius: var(--bs-border-radius) var(--bs-border-radius) 0 0;
        }

        .seeker-markdown-editor .CodeMirror {
            min-height: 20rem;
            color: inherit;
            background: var(--bs-body-bg);
            border: var(--bs-border-width) solid var(--bs-border-color);
            border-radius: 0 0 var(--bs-border-radius) var(--bs-border-radius);
        }

        .seeker-markdown-editor .editor-toolbar button {
            color: var(--bs-body-color) !important;
        }

        .seeker-markdown-editor .editor-toolbar button.active,
        .seeker-markdown-editor .editor-toolbar button:hover {
            background: var(--bs-tertiary-bg);
            border-color: var(--bs-primary-border-subtle);
        }

        .seeker-markdown-editor .CodeMirror-cursor {
            border-color: var(--bs-body-color);
        }

        .seeker-markdown-editor.is-invalid .editor-toolbar,
        .seeker-markdown-editor.is-invalid .CodeMirror {
            border-color: var(--bs-form-invalid-border-color);
        }
    </style>
@endpush

@push('footer-scripts')
    <script src="{{ asset('vendor/easymde/easymde.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const description = document.getElementById('publicationDescription');

            if (!description || typeof EasyMDE === 'undefined') {
                return;
            }

            const iconClassMap = {
                bold: 'bi bi-type-bold',
                italic: 'bi bi-type-italic',
                strikethrough: 'bi bi-type-strikethrough',
                heading: 'bi bi-type-h1',
                code: 'bi bi-code-slash',
                quote: 'bi bi-quote',
                'unordered-list': 'bi bi-list-ul',
                'ordered-list': 'bi bi-list-ol',
                link: 'bi bi-link-45deg',
                table: 'bi bi-table',
                'horizontal-rule': 'bi bi-dash-lg',
                fullscreen: 'bi bi-fullscreen',
                guide: 'bi bi-question-circle',
                undo: 'bi bi-arrow-counterclockwise',
                redo: 'bi bi-arrow-clockwise',
            };

            const editor = new EasyMDE({
                element: description,
                autoDownloadFontAwesome: false,
                forceSync: true,
                iconClassMap: iconClassMap,
                promptURLs: true,
                spellChecker: false,
                status: false,
                toolbar: [
                    'bold', 'italic', 'strikethrough', 'heading', '|',
                    'code', 'quote', 'unordered-list', 'ordered-list', '|',
                    'link', 'table', 'horizontal-rule', '|',
                    'fullscreen', 'guide', '|', 'undo', 'redo',
                ],
            });

            editor.codemirror.getWrapperElement().closest('.EasyMDEContainer')?.classList.add('seeker-markdown-editor');

            @error('description')
            editor.codemirror.getWrapperElement().closest('.EasyMDEContainer')?.classList.add('is-invalid');
            @enderror

            // EasyMDE hides the textarea, so native browser validation cannot focus it.
            // Laravel continues to enforce the required and visible-length rules.
            description.removeAttribute('required');
            description.removeAttribute('minlength');
            description.removeAttribute('maxlength');

            for (const button of editor.toolbar) {
                const icon = iconClassMap[button.name];
                const element = editor.gui.toolbar.querySelector('.' + button.name + ' .fa');

                if (icon && element) {
                    element.setAttribute('class', icon);
                    button.className = icon;
                }
            }
        });
    </script>
@endpush

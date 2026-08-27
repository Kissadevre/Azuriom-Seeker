@once
    @push('footer-scripts')
        <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
        <script>
            tinymce.init({
                selector: '#publicationDescription',
                license_key: 'gpl',
                promotion: false,
                branding: false,
                menubar: false,
                statusbar: false,
                height: 360,
                min_height: 240,
                entity_encoding: 'raw',
                plugins: 'autolink link lists',
                toolbar: 'undo redo | blocks | bold italic underline strikethrough | bullist numlist blockquote | link | removeformat',
                block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
                formats: {
                    underline: { inline: 'u', exact: true },
                    strikethrough: { inline: 's', exact: true },
                },
                valid_elements: 'p,br,h2,h3,h4,strong/b,em/i,u,s,blockquote,ul,ol,li,a[href|title]',
                invalid_elements: 'img,picture,video,audio,source,track,iframe,object,embed,svg,math,canvas,script,style,form,input,button,textarea,select,option',
                paste_data_images: false,
                automatic_uploads: false,
                browser_spellcheck: true,
                relative_urls: false,
                link_target_list: false,
                link_title: false,
                content_css: '{{ (dark_theme() ? 'dark,' : '').asset('vendor/bootstrap-icons/bootstrap-icons.css') }}',
                @if(dark_theme())
                skin: 'oxide-dark',
                @endif
            });
        </script>
    @endpush
@endonce

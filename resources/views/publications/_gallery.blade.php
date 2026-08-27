<div
    class="modal fade seeker-gallery-modal"
    id="{{ $galleryId }}"
    tabindex="-1"
    aria-labelledby="{{ $galleryId }}Title"
    aria-hidden="true"
    data-seeker-gallery
    data-gallery-counter="@lang('seeker::messages.gallery.counter', ['current' => ':current', 'total' => ':total'])"
>
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="{{ $galleryId }}Title">{{ $galleryTitle }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
            </div>
            <div class="modal-body p-0">
                <div class="seeker-gallery-stage">
                    <img class="seeker-gallery-preview" data-gallery-preview alt="">
                    <button class="btn seeker-gallery-navigation seeker-gallery-previous" type="button" data-gallery-previous aria-label="@lang('seeker::messages.gallery.previous')">
                        <i class="bi bi-chevron-left" aria-hidden="true"></i>
                    </button>
                    <button class="btn seeker-gallery-navigation seeker-gallery-next" type="button" data-gallery-next aria-label="@lang('seeker::messages.gallery.next')">
                        <i class="bi bi-chevron-right" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="modal-footer justify-content-between gap-3">
                <span class="text-body-secondary text-break" data-gallery-caption></span>
                <span class="badge text-bg-secondary flex-shrink-0" data-gallery-count aria-live="polite"></span>
            </div>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .seeker-gallery-trigger {
                display: block;
                width: 100%;
                padding: 0;
                overflow: hidden;
                border: 0;
                color: inherit;
                background: transparent;
                text-align: start;
                cursor: zoom-in;
            }

            .seeker-gallery-trigger:focus-visible {
                outline: .2rem solid rgba(var(--bs-primary-rgb), .4);
                outline-offset: .2rem;
            }

            .seeker-gallery-stage {
                position: relative;
                display: grid;
                min-height: min(68vh, 42rem);
                place-items: center;
                padding: 1rem 4.5rem;
                background: #08090b;
            }

            .seeker-gallery-preview {
                display: block;
                max-width: 100%;
                max-height: min(68vh, 42rem);
                object-fit: contain;
            }

            .seeker-gallery-navigation {
                position: absolute;
                top: 50%;
                display: inline-flex;
                width: 2.75rem;
                height: 2.75rem;
                align-items: center;
                justify-content: center;
                border: 1px solid rgba(255, 255, 255, .2);
                border-radius: 999px;
                color: #fff;
                background: rgba(0, 0, 0, .58);
                transform: translateY(-50%);
            }

            .seeker-gallery-navigation:hover,
            .seeker-gallery-navigation:focus-visible {
                color: #fff;
                background: rgba(var(--bs-primary-rgb), .9);
            }

            .seeker-gallery-previous {
                left: 1rem;
            }

            .seeker-gallery-next {
                right: 1rem;
            }

            @media (max-width: 575.98px) {
                .seeker-gallery-stage {
                    min-height: 55vh;
                    padding: .75rem 3.25rem;
                }

                .seeker-gallery-navigation {
                    width: 2.25rem;
                    height: 2.25rem;
                }

                .seeker-gallery-previous {
                    left: .5rem;
                }

                .seeker-gallery-next {
                    right: .5rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="{{ plugin_asset('seeker', 'js/image-gallery.js') }}" defer></script>
    @endpush
@endonce

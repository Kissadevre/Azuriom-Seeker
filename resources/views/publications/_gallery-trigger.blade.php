<button
    type="button"
    class="seeker-gallery-trigger {{ $triggerClass ?? '' }}"
    data-bs-toggle="modal"
    data-bs-target="#{{ $galleryId }}"
    data-seeker-gallery-trigger="{{ $galleryId }}"
    data-gallery-index="{{ $galleryIndex }}"
    data-gallery-src="{{ route('seeker.images.show', $image) }}"
    data-gallery-alt="{{ $image->original_name }}"
    data-gallery-name="{{ $image->original_name }}"
    aria-label="@lang('seeker::messages.gallery.open_image', ['current' => $galleryIndex + 1, 'total' => $galleryTotal])"
>
    <img
        src="{{ route('seeker.images.show', $image) }}"
        class="{{ $imageClass }}"
        loading="lazy"
        alt="{{ $image->original_name }}"
    >
    @if($showName ?? false)
        <span class="small text-body-secondary text-truncate d-block mt-1">{{ $image->original_name }}</span>
    @endif
</button>

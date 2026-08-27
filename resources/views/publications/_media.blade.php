@php
    $mediaUrl = route('seeker.media.show', $media);
@endphp

@if($media->type === \Azuriom\Plugin\Seeker\Models\PublicationMedia::TYPE_VIDEO)
    <video class="{{ $mediaClass ?? 'seeker-portfolio-media' }}" controls preload="metadata" playsinline>
        <source src="{{ $mediaUrl }}" type="{{ $media->mime_type }}">
        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">@lang('seeker::messages.media.open_video')</a>
    </video>
@else
    <audio class="{{ $mediaClass ?? 'seeker-portfolio-audio' }}" controls preload="metadata">
        <source src="{{ $mediaUrl }}" type="{{ $media->mime_type }}">
        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">@lang('seeker::messages.media.open_audio')</a>
    </audio>
@endif

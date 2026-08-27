@php
    $mediaUrl = route('seeker.media.show', $media);
    $mediaSize = $media->size >= 1048576
        ? number_format($media->size / 1048576, 2).' MB'
        : number_format(max(1, $media->size / 1024)).' KB';
@endphp

@if($media->type === \Azuriom\Plugin\Seeker\Models\PublicationMedia::TYPE_VIDEO)
    <video class="{{ $mediaClass ?? 'seeker-portfolio-media' }}" controls preload="metadata" playsinline>
        <source src="{{ $mediaUrl }}" type="{{ $media->mime_type }}">
        <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">@lang('seeker::messages.media.open_video')</a>
    </video>
@else
    <div class="seeker-audio-player {{ $mediaClass ?? 'seeker-portfolio-audio' }}"
         data-seeker-audio-player
         data-media-id="{{ $media->id }}"
         data-label-play="@lang('seeker::messages.media.play_audio')"
         data-label-pause="@lang('seeker::messages.media.pause_audio')"
         data-label-seek="@lang('seeker::messages.media.seek_audio')"
         data-label-speed="@lang('seeker::messages.media.playback_speed')">
        <audio class="seeker-audio-native" controls preload="metadata" data-seeker-audio>
            <source src="{{ $mediaUrl }}" type="{{ $media->mime_type }}">
            <a href="{{ $mediaUrl }}" target="_blank" rel="noopener">@lang('seeker::messages.media.open_audio')</a>
        </audio>
        <div class="seeker-audio-interface" data-seeker-audio-interface hidden>
            <button class="seeker-audio-play" type="button" data-seeker-audio-play aria-label="@lang('seeker::messages.media.play_audio')">
                <i class="bi bi-play-fill" aria-hidden="true"></i>
            </button>
            <div class="seeker-audio-content">
                <div class="seeker-audio-title text-truncate" title="{{ $media->original_name }}">{{ $media->original_name }}</div>
                <button class="seeker-audio-waveform" type="button" data-seeker-audio-seek aria-label="@lang('seeker::messages.media.seek_audio')">
                    <canvas data-seeker-audio-waveform aria-hidden="true"></canvas>
                </button>
                <div class="seeker-audio-meta">
                    <span><span data-seeker-audio-current>0:00</span><span class="mx-1" aria-hidden="true">/</span><span data-seeker-audio-duration>--:--</span></span>
                    <span>{{ $mediaSize }}</span>
                </div>
            </div>
            <button class="seeker-audio-speed" type="button" data-seeker-audio-speed aria-label="@lang('seeker::messages.media.playback_speed')">1×</button>
        </div>
    </div>

    @once
        @push('styles')
            <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/audio-player.css') }}">
        @endpush
        @push('scripts')
            <script src="{{ plugin_asset('seeker', 'js/audio-player.js') }}" defer></script>
        @endpush
    @endonce
@endif

@csrf

<div class="mb-4">
    <label class="form-label fw-semibold" for="publicationType">@lang('seeker::messages.fields.type')</label>
    <select id="publicationType" name="type" class="form-select @error('type') is-invalid @enderror" required>
        @foreach(\Azuriom\Plugin\Seeker\Models\Publication::types() as $publicationType)
            <option value="{{ $publicationType }}" @selected(old('type', $publication->type ?? '') === $publicationType)>@lang('seeker::messages.types.'.$publicationType)</option>
        @endforeach
    </select>
    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">@lang('seeker::messages.help.commission') @lang('seeker::messages.help.talent')</div>
</div>

<div class="mb-4">
    <label class="form-label fw-semibold" for="publicationTitle">@lang('seeker::messages.fields.title')</label>
    <input id="publicationTitle" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $publication->title ?? '') }}" minlength="5" maxlength="120" required>
    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="mb-4">
    <label class="form-label fw-semibold" for="publicationDescription">@lang('seeker::messages.fields.description')</label>
    <textarea id="publicationDescription" name="description" class="form-control @error('description') is-invalid @enderror" rows="10" minlength="20" maxlength="10000" required>{{ old('description', $publication->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">@lang('seeker::messages.help.description')</div>
</div>

@php
    $selectedPortfolioType = old(
        'portfolio_type',
        $publication->portfolio_type ?? \Azuriom\Plugin\Seeker\Models\Publication::PORTFOLIO_EXTERNAL
    );
    $serverUploadLimit = \Illuminate\Http\UploadedFile::getMaxFilesize();
@endphp

@if($serverUploadLimit < 10 * 1024 * 1024)
    <div class="alert alert-warning d-flex gap-2 align-items-start mb-4" role="alert">
        <i class="bi bi-exclamation-triangle flex-shrink-0" aria-hidden="true"></i>
        <div>@lang('seeker::messages.help.server_upload_limit', ['size' => number_format($serverUploadLimit / 1048576, 0).' MB'])</div>
    </div>
@endif

<fieldset class="mb-4" data-portfolio-choice>
    <legend class="form-label fw-semibold">@lang('seeker::messages.fields.portfolio_type')</legend>
    <p class="form-text mt-0">@lang('seeker::messages.help.portfolio_type')</p>
    <div class="row g-3">
        @foreach(\Azuriom\Plugin\Seeker\Models\Publication::portfolioTypes() as $portfolioType)
            <div class="col-md-6">
                <label class="seeker-choice-card card h-100 p-3">
                    <span class="d-flex gap-3">
                        <input class="form-check-input mt-1" type="radio" name="portfolio_type" value="{{ $portfolioType }}" @checked($selectedPortfolioType === $portfolioType) required>
                        <span><strong class="d-block"><i class="bi bi-{{ ['external' => 'box-arrow-up-right', 'images' => 'images', 'video' => 'camera-video', 'audio' => 'soundwave'][$portfolioType] }} me-1" aria-hidden="true"></i>@lang('seeker::messages.portfolio_types.'.$portfolioType)</strong><small class="text-muted">@lang('seeker::messages.help.portfolio_'.$portfolioType)</small></span>
                    </span>
                </label>
            </div>
        @endforeach
    </div>
    @error('portfolio_type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</fieldset>

<div class="mb-4" data-portfolio-panel="external">
    <label class="form-label fw-semibold" for="publicationPortfolio">@lang('seeker::messages.fields.portfolio_url')</label>
    <input id="publicationPortfolio" type="url" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror" value="{{ old('portfolio_url', $publication->portfolio_url ?? '') }}" maxlength="2048" placeholder="https://">
    @error('portfolio_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">@lang('seeker::messages.help.portfolio_url')</div>
</div>

<div class="mb-4" data-portfolio-panel="images" data-has-existing="{{ isset($publication) && $publication->images->isNotEmpty() ? 'true' : 'false' }}">
    @isset($publication)
        @if($publication->images->isNotEmpty())
            <fieldset class="mb-4">
                <legend class="h6">@lang('seeker::messages.fields.current_images')</legend>
                <div class="row g-3">
                    @foreach($publication->images as $image)
                        <div class="col-6 col-md-4">
                            <label class="card h-100 p-2 cursor-pointer">
                                <img src="{{ route('seeker.images.show', $image) }}" class="seeker-form-image rounded mb-2" alt="">
                                <span class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" @checked(in_array($image->id, old('remove_images', [])))>
                                    <span class="form-check-label">@lang('seeker::messages.delete')</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="form-text">@lang('seeker::messages.help.remove_images')</div>
            </fieldset>
        @endif
    @endisset

    <label class="form-label fw-semibold" for="publicationImages">@lang('seeker::messages.fields.images')</label>
    <input id="publicationImages" type="file" name="images[]" class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror" accept="image/jpeg,image/png,image/webp" multiple>
    @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
    @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">@lang('seeker::messages.help.images')</div>
</div>

@foreach(\Azuriom\Plugin\Seeker\Models\Publication::uploadedPortfolioTypes() as $mediaType)
    @php
        $currentMedia = isset($publication) ? $publication->media->firstWhere('type', $mediaType) : null;
    @endphp
    <div class="mb-4" data-portfolio-panel="{{ $mediaType }}" data-has-existing="{{ $currentMedia ? 'true' : 'false' }}">
        @if($currentMedia)
            <div class="card bg-body-tertiary border mb-3">
                <div class="card-body">
                    <div class="small fw-semibold mb-2">@lang('seeker::messages.fields.current_'.$mediaType)</div>
                    @include('seeker::publications._media', ['media' => $currentMedia, 'mediaClass' => $mediaType === 'video' ? 'seeker-form-video rounded' : 'seeker-form-audio'])
                </div>
            </div>
        @endif
        <label class="form-label fw-semibold" for="publication{{ ucfirst($mediaType) }}">@lang('seeker::messages.fields.'.$mediaType)</label>
        <input id="publication{{ ucfirst($mediaType) }}" type="file" name="{{ $mediaType }}" class="form-control @error($mediaType) is-invalid @enderror" accept="{{ $mediaType === 'video' ? 'video/mp4,video/webm,.mp4,.webm' : 'audio/mpeg,audio/wav,audio/ogg,audio/mp4,.mp3,.wav,.ogg,.m4a' }}">
        @error($mediaType)<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text">@lang('seeker::messages.help.'.$mediaType)</div>
    </div>
@endforeach

<div class="card mb-4 seeker-setting-card">
    <div class="card-body p-4">
        <div class="form-check form-switch d-flex align-items-start gap-3 p-0">
            <div class="flex-grow-1">
                <label class="form-check-label fw-semibold d-block" for="guestVisibility">@lang('seeker::messages.fields.guest_visibility')</label>
                <div class="form-text mt-1">@lang('seeker::messages.help.guest_visibility')</div>
            </div>
            <div>
                <input type="hidden" name="is_guest_visible" value="0">
                <input id="guestVisibility" class="form-check-input seeker-switch m-0" type="checkbox" role="switch" name="is_guest_visible" value="1" @checked((bool) old('is_guest_visible', $publication->is_guest_visible ?? true))>
            </div>
        </div>
        @error('is_guest_visible')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </div>
</div>

@php
    $selectedPricingType = old(
        'pricing_type',
        $publication->pricing_type ?? \Azuriom\Plugin\Seeker\Models\Publication::PRICING_NEGOTIABLE
    );
@endphp

<fieldset class="mb-4" data-pricing-choice>
    <legend class="form-label fw-semibold">@lang('seeker::messages.fields.pricing_type')</legend>
    <p class="form-text mt-0">@lang('seeker::messages.help.pricing_type')</p>
    <div class="row g-3">
        @foreach(\Azuriom\Plugin\Seeker\Models\Publication::pricingTypes() as $pricingType)
            <div class="col-md-4">
                <label class="seeker-choice-card card h-100 p-3">
                    <span class="d-flex gap-3">
                        <input class="form-check-input mt-1" type="radio" name="pricing_type" value="{{ $pricingType }}" @checked($selectedPricingType === $pricingType) required>
                        <span><strong class="d-block">@lang('seeker::messages.pricing_types.'.$pricingType)</strong><small class="text-muted">@lang('seeker::messages.help.pricing_'.$pricingType)</small></span>
                    </span>
                </label>
            </div>
        @endforeach
    </div>
    @error('pricing_type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</fieldset>

<div class="mb-4" data-pricing-panel="points">
    @php
        $selectedPriceBasis = old(
            'price_basis',
            $publication->price_basis ?? \Azuriom\Plugin\Seeker\Models\Publication::PRICE_BASIS_FIXED
        );
    @endphp
    <label class="form-label fw-semibold" for="publicationPrice">@lang('seeker::messages.fields.price')</label>
    <div class="input-group">
        <input id="publicationPrice" type="number" name="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $publication->price ?? '') }}" min="0.01" max="999999999999.99" step="0.01" inputmode="decimal">
        <span class="input-group-text">{{ money_name() }}</span>
        @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-text">@lang('seeker::messages.help.price')</div>

    <fieldset class="mt-3">
        <legend class="form-label fw-semibold">@lang('seeker::messages.fields.price_basis')</legend>
        <div class="row g-2">
            @foreach(\Azuriom\Plugin\Seeker\Models\Publication::priceBases() as $priceBasis)
                <div class="col-sm-6">
                    <label class="seeker-choice-card card h-100 p-3">
                        <span class="d-flex gap-3">
                            <input class="form-check-input mt-1" type="radio" name="price_basis" value="{{ $priceBasis }}" @checked($selectedPriceBasis === $priceBasis)>
                            <span><strong class="d-block">@lang('seeker::messages.price_bases.'.$priceBasis)</strong><small class="text-muted">@lang('seeker::messages.help.price_basis_'.$priceBasis)</small></span>
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
        @error('price_basis')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
    </fieldset>
</div>

@include('elements.captcha', ['center' => true])

<div class="d-flex justify-content-end gap-2">
    <a class="btn btn-outline-secondary" href="{{ isset($publication) ? route('seeker.publications.show', $publication) : route('seeker.index') }}">@lang('messages.actions.cancel')</a>
    <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i> @lang(isset($publication) ? 'seeker::messages.actions.update' : 'seeker::messages.actions.save')</button>
</div>

@once
    @push('scripts')
        <script src="{{ plugin_asset('seeker', 'js/portfolio-choice.js') }}" defer></script>
    @endpush
@endonce

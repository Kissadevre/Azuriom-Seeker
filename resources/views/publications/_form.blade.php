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
@endphp

<fieldset class="mb-4" data-portfolio-choice>
    <legend class="form-label fw-semibold">@lang('seeker::messages.fields.portfolio_type')</legend>
    <p class="form-text mt-0">@lang('seeker::messages.help.portfolio_type')</p>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="seeker-choice-card card h-100 p-3">
                <span class="d-flex gap-3">
                    <input class="form-check-input mt-1" type="radio" name="portfolio_type" value="external" @checked($selectedPortfolioType === 'external') required>
                    <span><strong class="d-block">@lang('seeker::messages.portfolio_types.external')</strong><small class="text-muted">@lang('seeker::messages.help.external')</small></span>
                </span>
            </label>
        </div>
        <div class="col-md-6">
            <label class="seeker-choice-card card h-100 p-3">
                <span class="d-flex gap-3">
                    <input class="form-check-input mt-1" type="radio" name="portfolio_type" value="images" @checked($selectedPortfolioType === 'images') required>
                    <span><strong class="d-block">@lang('seeker::messages.portfolio_types.images')</strong><small class="text-muted">@lang('seeker::messages.help.uploaded_images')</small></span>
                </span>
            </label>
        </div>
    </div>
    @error('portfolio_type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
</fieldset>

<div class="mb-4" data-portfolio-panel="external">
    <label class="form-label fw-semibold" for="publicationPortfolio">@lang('seeker::messages.fields.portfolio_url')</label>
    <input id="publicationPortfolio" type="url" name="portfolio_url" class="form-control @error('portfolio_url') is-invalid @enderror" value="{{ old('portfolio_url', $publication->portfolio_url ?? '') }}" maxlength="2048" placeholder="https://">
    @error('portfolio_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
    <div class="form-text">@lang('seeker::messages.help.portfolio_url')</div>
</div>

<div class="mb-4" data-portfolio-panel="images">
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

<div class="d-flex justify-content-end gap-2">
    <a class="btn btn-outline-secondary" href="{{ isset($publication) ? route('seeker.publications.show', $publication) : route('seeker.index') }}">@lang('messages.actions.cancel')</a>
    <button class="btn btn-primary px-4"><i class="bi bi-check-lg me-1" aria-hidden="true"></i> @lang(isset($publication) ? 'seeker::messages.actions.update' : 'seeker::messages.actions.save')</button>
</div>

@once
    @push('scripts')
        <script src="{{ plugin_asset('seeker', 'js/portfolio-choice.js') }}" defer></script>
    @endpush
@endonce

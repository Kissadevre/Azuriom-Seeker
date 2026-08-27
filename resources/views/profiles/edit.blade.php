@extends('layouts.app')

@section('title', trans('seeker::messages.profiles.edit'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="mb-4"><a class="text-decoration-none" href="{{ route('seeker.profiles.show', $user) }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('seeker::messages.profiles.back')</a></div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('seeker.profiles.update', $user) }}" class="card">
                @csrf @method('PUT')
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3">@lang('seeker::messages.profiles.edit')</h1>
                    <p class="text-muted">@lang('seeker::messages.profiles.edit_description')</p>
                    <label class="form-label fw-semibold" for="profileBio">@lang('seeker::messages.profiles.bio')</label>
                    <textarea id="profileBio" name="bio" rows="8" maxlength="1000" class="form-control @error('bio') is-invalid @enderror">{{ old('bio', $profile->bio) }}</textarea>
                    <div class="form-text">@lang('seeker::messages.profiles.bio_help')</div>
                    @error('bio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-outline-secondary" href="{{ route('seeker.profiles.show', $user) }}">@lang('seeker::messages.profiles.cancel')</a>
                        <button class="btn btn-primary" type="submit">@lang('seeker::messages.profiles.save')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

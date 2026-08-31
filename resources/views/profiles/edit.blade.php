@extends('layouts.app')

@section('title', trans('seeker::messages.profiles.edit'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @include('seeker::_page-header', [
                    'pageIcon' => 'bi-person-lines-fill',
                    'pageTitle' => trans('seeker::messages.profiles.edit'),
                    'pageSubtitle' => trans('seeker::messages.profiles.edit_description'),
                    'breadcrumbs' => [
                        ['label' => $user->name, 'url' => route('seeker.profiles.show', $user)],
                        ['label' => trans('seeker::messages.profiles.edit')],
                    ],
                ])
                <form method="POST" action="{{ route('seeker.profiles.update', $user) }}" class="card seeker-form-card">
                @csrf @method('PUT')
                <div class="card-body p-4 p-md-5">
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
    </div>
@endsection

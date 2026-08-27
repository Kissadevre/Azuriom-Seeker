@extends('layouts.app')

@section('title', trans('seeker::messages.publication_reports.title'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                @include('seeker::_page-header', [
                    'pageIcon' => 'bi-flag',
                    'pageTitle' => trans('seeker::messages.publication_reports.title'),
                    'pageSubtitle' => trans('seeker::messages.publication_reports.reporting', ['publication' => $publication->title]),
                    'backUrl' => route('seeker.publications.show', $publication),
                    'backLabel' => trans('seeker::messages.publication_reports.back'),
                ])
                <form method="POST" action="{{ route('seeker.publications.reports.store', $publication) }}" class="card seeker-form-card seeker-report-card">
                @csrf
                <div class="card-body p-4 p-md-5">
                    <div class="alert alert-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.publication_reports.notice')</div>
                    <div class="mb-3">
                        <label class="form-label" for="publicationReportReason">@lang('seeker::messages.publication_reports.reason')</label>
                        <select id="publicationReportReason" name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                            <option value="" disabled @selected(old('reason') === null)>@lang('seeker::messages.publication_reports.select_reason')</option>
                            @foreach(\Azuriom\Plugin\Seeker\Models\PublicationReport::reasons() as $reason)<option value="{{ $reason }}" @selected(old('reason') === $reason)>@lang('seeker::messages.publication_reports.reasons.'.$reason)</option>@endforeach
                        </select>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="publicationReportDetails">@lang('seeker::messages.publication_reports.details')</label>
                        <textarea id="publicationReportDetails" name="details" rows="7" minlength="20" maxlength="2000" class="form-control @error('details') is-invalid @enderror" required>{{ old('details') }}</textarea>
                        <div class="form-text">@lang('seeker::messages.publication_reports.details_help')</div>
                        @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('seeker.publications.show', $publication) }}">@lang('messages.actions.cancel')</a><button class="btn btn-danger"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::messages.publication_reports.submit')</button></div>
                </div>
                </form>
            </div>
        </div>
    </div>
@endsection

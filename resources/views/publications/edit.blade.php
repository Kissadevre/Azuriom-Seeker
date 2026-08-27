@extends('layouts.app')

@section('title', trans('seeker::messages.edit'))

@include('seeker::_assets')
@include('seeker::publications._editor')

@section('content')
    <div class="seeker-public-shell">
        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('seeker::_page-header', [
                    'pageIcon' => 'bi-pencil',
                    'pageTitle' => trans('seeker::messages.edit'),
                    'pageSubtitle' => trans('seeker::messages.edit_description', ['publication' => $publication->title]),
                    'backUrl' => route('seeker.publications.show', $publication),
                    'backLabel' => trans('seeker::messages.publication_reports.back'),
                ])
                <form method="POST" action="{{ route('seeker.publications.update', $publication) }}" enctype="multipart/form-data" class="card seeker-form-card" id="captcha-form">
                @method('PUT')
                <div class="card-body p-4">@include('seeker::publications._form')</div>
                </form>
            </div>
        </div>
    </div>
@endsection

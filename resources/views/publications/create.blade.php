@extends('layouts.app')

@section('title', trans('seeker::messages.publish'))

@include('seeker::_assets')
@include('seeker::publications._editor')

@section('content')
    <div class="seeker-public-shell">
        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('seeker::_page-header', [
                    'pageIcon' => 'bi-plus-lg',
                    'pageTitle' => trans('seeker::messages.publish'),
                    'pageSubtitle' => trans('seeker::messages.publish_description'),
                    'backUrl' => route('seeker.index'),
                    'backLabel' => trans('seeker::messages.back'),
                ])
                <form method="POST" action="{{ route('seeker.publications.store') }}" enctype="multipart/form-data" class="card seeker-form-card" id="captcha-form">
                <div class="card-body p-4">@include('seeker::publications._form')</div>
                </form>
            </div>
        </div>
    </div>
@endsection

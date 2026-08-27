@extends('layouts.app')

@section('title', trans('seeker::messages.publish'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="seeker-title-icon"><i class="bi bi-plus-lg" aria-hidden="true"></i></span>
                <h1 class="h2 mb-0">@lang('seeker::messages.publish')</h1>
            </div>
            <form method="POST" action="{{ route('seeker.publications.store') }}" enctype="multipart/form-data" class="card" id="captcha-form">
                <div class="card-body p-4">@include('seeker::publications._form')</div>
            </form>
        </div>
    </div>
@endsection

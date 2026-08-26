@extends('layouts.app')

@section('title', trans('seeker::messages.edit'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="d-flex align-items-center gap-3 mb-4">
                <span class="seeker-title-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span>
                <h1 class="h2 mb-0">@lang('seeker::messages.edit')</h1>
            </div>
            <form method="POST" action="{{ route('seeker.publications.update', $publication) }}" enctype="multipart/form-data" class="card">
                @method('PUT')
                <div class="card-body p-4">@include('seeker::publications._form')</div>
            </form>
        </div>
    </div>
@endsection

@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.nav.'.$section))

@section('content')
    <div class="card">
        <div class="card-body p-5 text-center">
            <i class="bi bi-tools display-4 text-primary" aria-hidden="true"></i>
            <h1 class="h3 mt-3">@lang('seeker::admin.nav.'.$section)</h1>
            <p class="text-muted mb-0">@lang('seeker::admin.coming_soon')</p>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('title', trans('seeker::messages.publish'))

@include('seeker::_assets')
@include('seeker::publications._markdown-editor')

@section('content')
    <div class="seeker-public-shell">
        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('seeker::_page-header', [
                    'pageIcon' => 'bi-plus-lg',
                    'pageTitle' => trans('seeker::messages.publish'),
                    'pageSubtitle' => trans('seeker::messages.publish_description'),
                    'breadcrumbs' => [
                        ['label' => trans('seeker::messages.my_publications'), 'url' => route('seeker.publications.mine')],
                        ['label' => trans('seeker::messages.publish')],
                    ],
                ])
                <form method="POST" action="{{ route('seeker.publications.store') }}" enctype="multipart/form-data" class="card seeker-form-card" id="captcha-form">
                <div class="card-body p-4">@include('seeker::publications._form')</div>
                </form>
            </div>
        </div>
    </div>
@endsection

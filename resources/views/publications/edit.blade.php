@extends('layouts.app')

@section('title', trans('seeker::messages.edit'))

@include('seeker::_assets')
@include('seeker::publications._markdown-editor')

@section('content')
    <div class="seeker-public-shell">
        <div class="row justify-content-center">
            <div class="col-xl-9">
                @include('seeker::_page-header', [
                    'pageIcon' => 'bi-pencil',
                    'pageTitle' => trans('seeker::messages.edit'),
                    'pageSubtitle' => trans('seeker::messages.edit_description', ['publication' => $publication->title]),
                    'breadcrumbs' => [
                        ['label' => trans('seeker::messages.my_publications'), 'url' => route('seeker.publications.mine')],
                        ['label' => $publication->title, 'url' => route('seeker.publications.show', $publication)],
                        ['label' => trans('seeker::messages.edit')],
                    ],
                ])
                <form method="POST" action="{{ route('seeker.publications.update', $publication) }}" enctype="multipart/form-data" class="card seeker-form-card" id="captcha-form">
                @method('PUT')
                <div class="card-body p-4">@include('seeker::publications._form')</div>
                </form>
            </div>
        </div>
    </div>
@endsection

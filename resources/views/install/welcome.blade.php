@extends('service::layouts.app_install', ['title' => __('service::install.welcome')])
@section('content')
    <!-- from section -->
    <div class="col-8 from-section">
        <div class="padding-left-top">
            <div class="bg-white w-75 rounded show-section tab-section" step-count="1">
                <div class="text-title p-3 text-center text-white">
                    <h3>{{ __('service::install.welcome_title') }}</h3>
                </div>
                <div class="px-5 py-4 d-flex flex-column justify-content-center align-items-center gap-3 content-body">
                    <img src="{{ asset( $data['asset_path']. '/') }}/images/illustration.png" alt="" />
                    <p class="text-center mb-3">
                        {{ __('service::install.welcome_description') }}
                    </p>
                    <a href="{{ route('service.preRequisite') }}"
                        class="btn color btn-primary px-5 py-3 mb-3 align-items-center follow-next-step">
                        {{ __('service::install.get_started') }} »</a>
                </div>
            </div>
        </div>
    </div>
@stop

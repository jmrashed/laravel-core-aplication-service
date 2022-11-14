@extends('service::layouts.app_install', ['title' => __('service::install.welcome')])
@section('content')
    <!-- from section -->
    <div class="col-8 from-section">
        <div class="padding-left-top">
            <div class="bg-white w-75 rounded" step-count="6">
                <div class="text-title p-3 text-center text-white">
                    <h3>{{__('Congratulations !')}}</h3>
                </div>
                <div class="px-5 py-4 d-flex flex-column justify-content-center align-items-center gap-3 content-body">
                    <img src="{{ asset($data['asset_path'] . '/') }}/images/complete-installation.png" alt="" />
                    <p class="text-center pb-3">
                        {{__('Congratulations! You successfully installed.')}}
                    </p> 
                    <a href="{{ url('/') }}" class="btn color mb-3 btn-primary px-5 py-3 align-items-center">
                        {{ __('service::install.goto_home') }} </a>
                </div>
            </div>
        </div>
    </div>
@stop
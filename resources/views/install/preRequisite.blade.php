@extends('service::layouts.app_install', ['title' => __('lms::install.welcome')])

@php
$base_path = $data['asset_path'];
@endphp
@section('content')

<!-- from section -->
<div class="col-8 from-section">
    <div class="padding-left-top">

        <div class="bg-white w-75 rounded" step-count="2">
            <div class="text-title p-3 text-center text-white">
                <h3>{{ __('service::install.environment_title') }}</h3>
            </div>
            <div class="row p-5">
                <h3>Serviver Requirements</h3>
                <hr />

                @foreach ($server_checks as $server)
                @php
                if(gv($server, 'type') == 'error' and !$has_false){
                $has_false = true;
                }
                @endphp
                <div class="col-md-6">
                    <div class="list-item">
                        <img src="{{ asset($base_path . '/') }}/images/{{ gv($server, 'type') == 'error' ? 'cross' : 'check' }}.svg" alt="" />
                        <p class="text-{{ gv($server, 'type') == 'error' ? 'danger' : '' }}">{{ gv($server, 'message') }}</p>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="row px-5">
                <h3>Folder Requirements</h3>
                <hr />
                @foreach ($folder_checks as $folder)
                @php
                if(gv($folder, 'type') == 'error' and !$has_false){
                $has_false = true;
                }
                @endphp
                <div class="col-md-6">
                    <div class="list-item">
                        <img src="{{ asset($base_path . '/') }}/images/{{ gv($folder, 'type') == 'error' ? 'cross' : 'check' }}.svg" alt="" />
                        <p class="text-{{ gv($folder, 'type') == 'error' ? 'danger' : '' }}"> {{ gv($folder, 'message') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="px-5 py-4 d-flex flex-column justify-content-center align-items-center gap-3">
                @if($has_false)
                <div class="py-3 rounded text-center px-5 btn-with-opacity system_req_err">
                    <p class="px-5 all-the system_req_err_msg">
                        <b>{{ __('Please solve the requirements issue.') }}</b>
                    </p>
                </div>
                <a href="{{ route('service.preRequisite') }}" class="btn mb-3 color btn-primary px-5 py-3 align-items-center">
                    {{ __('service::install.refresh') }} </a>
                @else
                <div class="py-3 rounded text-center px-5 btn-with-opacity">
                    <p class="px-5 all-the">
                        <b>{{ __("All the Requirements look's Fine. Let;s Dig in") }}</b>
                    </p>
                </div>
                <a href="{{ route('service.license') }}" class="btn mb-3 color btn-primary px-5 py-3 align-items-center follow-next-step">
                    {{ strtoupper(__('service::install.lets_go_next')) }} »</a>

                @endif
            </div>
        </div>
    </div>
</div>

@stop
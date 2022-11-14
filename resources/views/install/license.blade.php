@extends('service::layouts.app_install', ['title' => __('service::install.license_verification')])
@section('content')

    <!-- from section -->
    <div class="col-8 from-section">
        <div class="padding-left-top">

            <div class="bg-white w-75 rounded" step-count="3">
                <div class="text-title p-3 text-center text-white">
                    <h3>{{ __('service::install.license_verification') }}</h3>
                </div>
                <form class="pb-3" data-parsley-validate method="post" action="{{ route('service.license') }}"
                    id="content_form">
                    <div class="mb-3 px-5 pt-5">
                        <label class="form-label" for="access_code"><b>{{ __('service::install.access_code') }}<span
                                    class="star">*</span></b></label>
                        <input type="text" name="access_code" id="access_code" class="form-control" required="required"
                            autofocus="" value="{{ old('access_code', request('access_code')) }}"
                            placeholder="{{ __('service::install.access_code') }}" />
                        @if (request('message'))
                            <span class="text-danger">{{ request('message') }}</span>
                        @endif
                    </div>
                    <div class="mb-3 px-5">
                        <label class="form-label" for="envato_email"><b>{{ __('service::install.envato_email') }}<span
                                    class="star">*</span></b></label>
                        <input type="email" class="form-control" data-parsley-type="email" name="envato_email"
                            id="envato_email" value="{{ old('envato_email', request('envato_email')) }}" required="email"
                            placeholder="{{ __('service::install.envato_email') }}">
                    </div>
                    <div class="mb-3 px-5 pb-3">
                        <label class="form-label"
                            for="installed_domain"><b>{{ __('service::install.installed_domain') }}<span
                                    class="star">*</span></b></label>
                        <input type="text" class="form-control" name="installed_domain" id="installed_domain"
                            required="required" readonly value="{{ app_url() }}">
                    </div>
                    @if ($reinstall)
                        <div class="form-group">
                            <label data-id="bg_option" class="primary_checkbox d-flex mr-12 ">
                                <input name="re_install" type="checkbox">
                                <span class="checkmark"></span>
                                <span class="ml-2">{{__('Re install System')}}</span>
                            </label>
                        </div>
                    @endif
                    <div class="px-5 pb-4 d-flex flex-column justify-content-center align-items-start gap-3">
                        <button type="submit"
                            class="btn color btn-primary px-5 py-3 align-items-start follow-next-step submit">
                            <b>{{ __('service::install.lets_go_next') }} »</b> </button>
                        <button type="button"
                            class="btn color btn-primary px-5 py-3 align-items-start follow-next-step submitting" disabled
                            style="display:none"> <b>{{ __('service::install.submitting') }} »</b> </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

@stop
@push('js')
    <script>
        _formValidation('content_form');
        $(document).ready(function() {
            setTimeout(function() {
                $('.preloader h2').text({{ __('service::install.preloader_text') }});
            }, 2000);
        })
    </script>
@endpush

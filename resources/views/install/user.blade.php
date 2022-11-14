@extends('service::layouts.app_install', ['title' => __('service::install.admin_setup')])
@section('content')

    <!-- from section -->
    <div class="col-8 from-section">
        <div class="padding-left-top">
            <div class="bg-white w-75 rounded" step-count="5">
                <div class="text-title p-3 text-center text-white">
                    <h3>{{ __('service::install.admin_setup') }}</h3>
                </div>
                <form class="pb-3 content-body" method="post" action="{{ route('service.user') }}" id="content_form">
                    <div class="mb-3 px-5 pt-5">
                        <label class="form-label"><b>{{ __('service::install.email') }}<span
                                    class="star">*</span></b></label>
                        <input type="email" class="form-control" name="email" id="email" required="required"
                            placeholder="{{ __('service::install.email') }}">
                    </div>
                    <div class="mb-3 px-5">
                        <label class="form-label" for="password"><b>{{ __('service::install.password') }}<span
                                    class="star">*</span></b></label>
                        <input type="password" class="form-control" name="password" id="password"
                            placeholder="{{ __('service::install.password') }}" required>

                    </div>
                    <div class="mb-3 px-5 pb-3">
                        <label class="form-label"
                            for="password_confirmation"><b>{{ __('service::install.password_confirmation') }}<span
                                    class="star">*</span></b></label>
                        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation"
                            required placeholder="{{ __('service::install.password_confirmation') }}"
                            data-parsley-equalto="#password">
                    </div>
                    @if ((env('APP_DEMO')==true) && ( env('APP_ENV')=='local') )
                        <div class="px-5 pb-4 d-flex align-items-center gap-2">
                            <input class="form-check-input" type="checkbox" name="seed" id="flexRadioDefault2" />
                            <label class="form-check-label" for="flexRadioDefault2">
                                {{ __('Install With Demo Data') }}
                            </label>
                        </div>
                    @endif
                    <div class="px-5 pb-4 d-flex flex-column justify-content-center align-items-start gap-3">
                        <button type="submit"
                            class="btn color btn-primary px-5 py-3 align-items-start follow-next-step submit bc-color">{{ __('service::install.ready_to_go') }}
                            »</button>
                        <button type="button"
                            class="btn color btn-primary px-5 py-3 align-items-start follow-next-step submitting bc-color"
                            disabled style="display:none">{{ __('service::install.submitting') }} »</button>
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

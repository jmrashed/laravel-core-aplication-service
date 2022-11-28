<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ isset($title) ? $title .' | '. config('app.name') :  config('app.name') }}</title>
    @php
    $base_path = $data['asset_path'];
    @endphp
    <input type="hidden" id="base_path" value="{{ $base_path }}">
     <link rel="stylesheet" href="{{ $base_path }}/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ $base_path }}/css/toastr.min.css" />
    <link rel="stylesheet" href="{{ $base_path }}/css/style.css" />
</head>

<body>
    <div class="preloader">
        <div class="loader_img">
            <img src="{{ $base_path }}/loader.gif" alt="loading..." height="200" width="200">
            <h2>{{__('Please Wait...')}}</h2>
        </div>
    </div>
    <div class="row installer-container">
        <div class="col-4">
            <div class="padding-left-top">
                <div class="mt-5 pe-2 follow-next-step-side" step-count="1">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 step-with-border completed rounded-circle d-flex flex-column justify-content-center align-items-center image-icon tab-button">
                            <img src="{{ $base_path}}/images/check-mark.svg" alt="" />
                        </div>
                        <div>
                            <p>{{__('01.')}}</p>
                            <h5><b>{{__("Welcome Note")}}</b></h5>
                        </div>
                    </div>
                    <span class="next-step-status-line"></span>
                </div>
                <div class="pe-4 follow-next-step-side" step-count="2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 border step-with-border completed rounded-circle d-flex flex-column justify-content-center align-items-center image-icon tab-button">
                            <img src="{{ $base_path}}/images/check-mark.svg" alt="" />
                        </div>
                        <div class="col-9">
                            <p>{{__('02.')}}</p>
                            <h5><b>{{__("Check Environment")}}</b></h5>
                        </div>
                    </div>
                    <span class="next-step-status-line"></span>
                </div>
                <div class="pe-4 follow-next-step-side" step-count="3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="border step-with-border completed  rounded-circle d-flex flex-column justify-content-center align-items-center image-icon tab-button">
                            <img src="{{ $base_path}}/images/check-mark.svg" alt="" />
                        </div>
                        <div class="ps-2">
                            <p>{{__('03.')}}</p>
                            <h5><b>{{__("Licence Verification")}}</b></h5>
                        </div>
                    </div>
                    <span class="next-step-status-line"></span>
                </div>
                <div class="pe-4 follow-next-step-side" step-count="4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 border step-with-border completed rounded-circle d-flex flex-column justify-content-center align-items-center image-icon tab-button">
                            <img src="{{ $base_path }}/images/check-mark.svg" alt="" />
                        </div>
                        <div class="col-9">
                            <p>{{__('04.')}}</p>
                            <h5><b>{{__("Database Setup")}}</b></h5>
                        </div>
                    </div>
                    <span class="next-step-status-line"></span>
                </div>
                <div class="pe-4 follow-next-step-side" step-count="5">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 border step-with-border completed rounded-circle d-flex flex-column justify-content-center align-items-center image-icon tab-button">
                            <img src="{{ $base_path }}/images/check-mark.svg" alt="" />
                        </div>
                        <div class="ps-2">
                            <p>{{__('05.')}}</p>
                            <h5><b>{{__("Admin Setup")}}</b></h5>
                        </div>
                    </div>
                    <span class="next-step-status-line"></span>
                </div>
                <div class="pe-4 follow-next-step-side" step-count="6">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 border step-with-border initial rounded-circle d-flex flex-column justify-content-center align-items-center image-icon tab-button">
                            <img src="{{ $base_path  }}/images/icon-white/complete.svg" alt="" />
                        </div>
                        <div>
                            <p>{{__('06.')}}</p>
                            <h5><b>{{__("Complete")}}</b></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @yield('content')
    </div>
    <script type="text/javascript" src="{{ $base_path }}/js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="{{ $base_path }}/js/bootstrap.bundle.min.js"></script>
    <script src="{{ $base_path }}/js/toastr.min.js"></script>
    <script src="{{ $base_path }}/js/parsley.min.js"></script>
    <script src="{{ $base_path }}/js/function.js"></script>
    <script src="{{ $base_path }}/js/common.js"></script>

    @if (session("message"))
    <script>
        toastr. {
            {
                session('status');
            }
        }('{{ session("message") }}', '{{ ucfirst(session('
            status ', '
            error ')) }}');
    </script>
    @endif
    @stack('js')
</body>

</html>
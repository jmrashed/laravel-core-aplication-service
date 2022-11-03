<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ isset($title) ? $title .' | '. config('app.name') :  config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" />

    @php
    $base_path = $data['asset_path'];
    @endphp
    <input type="hidden" id="base_path" value="{{ $base_path }}">
    <link rel="stylesheet" href="{{ $base_path}}/css/style.css" />
    
</head>

<body>
    <div class="preloader">
        <div class="loader_img">
            <img src="{{ $base_path }}/loader.gif" alt="loading..." height="200" width="200">
            <h2>Please Wait</h2>
        </div>
    </div>
    <div class="row installer-container">
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
        toastr.{{ session('status') }}('{{ session("message") }}', '{{ ucfirst(session('status', 'error')) }}');
    </script>
    @endif
    @stack('js')
</body>

</html>
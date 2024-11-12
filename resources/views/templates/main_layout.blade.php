<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link id="favicon-site" href="{{ asset('assets/img/small-logo.png') }}" rel="shortcut icon" />
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('assets/styles/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/bootstrap/dark/bootstrap.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Font Awesome via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />

</head>

<body>

    @if(Auth::check())
        @include('partials.main.nav')
    @else
        @include('partials.visitant.nav')
    @endif

    <main class="d-flex flex-column min-vh-100">
        @yield('content')
    </main>

    @include('partials.main.footer')

    <script src="{{ asset('assets/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ asset('assets/scripts/script.js') }}"></script>
    <script src="https://unpkg.com/htmx.org@1.9.12"></script>
    <script src="{{ asset('assets/moment/moment.js') }}"></script>
</body>

</html>

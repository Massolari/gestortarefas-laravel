@extends('templates.main_layout')
@section('content')
    @include('partials.nav')

    @include('partials.tasks', ['tasks' => $tasks])

    @include('partials.footer')
@endsection

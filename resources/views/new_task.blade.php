@extends('templates.main_layout')
@section('content')
    @include('partials.nav')

    @include('partials.task.form_new_task')

    @include('partials.footer')
@endsection

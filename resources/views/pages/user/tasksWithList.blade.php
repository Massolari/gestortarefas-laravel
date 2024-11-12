@extends('templates.main_layout')
@section('content')

    @include('partials.task.tasks', ['tasks' => $tasks])

@endsection

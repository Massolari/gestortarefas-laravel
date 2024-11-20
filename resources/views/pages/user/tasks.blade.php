@extends('layout.main')
@section('content')
    @include('partials.task.tasks', ['tasks' => $tasks])
@endsection

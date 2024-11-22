@extends('layout.main')

@section('content')
<div class="d-flex justify-content-center my-auto">
    <div class="card shadow shadow-md">
        <div class="card-body">
            <h3 class="card-title text-info">Cadastro</h3>
            <p class="card-text">
                Informe um e-mail para continuar com o cadastro
            </p>
            <form class="d-flex flex-column gap-2 form-group" action="{{ route('signup.send-code') }}" method="POST">
                @csrf
                <div class="form-floating">
                    <input type="email" name="email" required class="form-control" title="Digite seu e-mail para continuar com o cadastro" value="{{ isset($email) ? $email : '' }}">
                    <label for="email" class="form-label">Digite seu e-mail</label>
                </div>
                @error('email')
                    <p class="text-danger text-center my-2">{{ $message }}</p>
                @enderror
                @isset($error)
                    <p class="text-danger text-center my-2">{{ $error }}</p>
                @endisset
                <button type="submit" class="btn btn-primary" title="Continuar com o cadastro">Continuar</button>
            </form>
        </div>
    </div>
</div>
@endsection
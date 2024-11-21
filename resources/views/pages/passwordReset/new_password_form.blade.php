@extends('layout.main')

@section('content')
<div class="d-flex justify-content-center my-auto">
    <div class="card shadow shadow-md">
        <div class="card-body">
            <h3 class="card-title text-info">Redefina sua senha</h3>
            <form class="form-group d-flex flex-column gap-2" action="{{ route('password.reset.new.submit', ['token' => request()->route('token')]) }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ request()->route('token') }}">

                <div class="form-floating">
                    <input type="password" name="password" required class="form-control">
                    <label for="password">Senha:</label>
                </div>
            
                <div class="form-floating">
                    <input type="password" name="password_confirmation" required class="form-control">
                    <label for="password_confirmation">Confirme a senha:</label>
                </div>

                @if (isset($error))
                    <p class="text-danger text-center my-2">{{ $error }}</p>
                @endif
    
                <button type="submit" class="btn btn-primary">Redefinir senha</button>
            </form>
        </div>
    </div>
</div>
@endsection
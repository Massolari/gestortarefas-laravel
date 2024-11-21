@extends('layout.main')

@section('content')
<div class="d-flex justify-content-center my-auto">
    <div class="card shadow shadow-md">
        <div class="card-body">
            <h3 class="card-title text-info">Finalize seu cadastro</h3>
            <form class="form-group d-flex flex-column gap-2" action="{{ route('signup.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="email" value="{{ session('email') }}">
                
                <div class="form-floating">
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        class="form-control"
                        value="{{ old('name') }}"
                    >
                    <label for="name">Nome:</label>
                    @error('name')
                        <p class="text-danger text-center">{{ $message }}</p>
                    @enderror
                </div>
            
                <div class="form-floating">
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        class="form-control"
                    >
                    <label for="password">Senha:</label>
                    @error('password')
                        <p class="text-danger text-center">{{ $message }}</p>
                    @enderror
                </div>
            
                <div class="form-floating">
                    <input 
                        type="password" 
                        name="password_confirm" 
                        required 
                        class="form-control"
                    >
                    <label for="password_confirm">Confirme a senha:</label>
                    @error('password_confirm')
                        <p class="text-danger text-center">{{ $message }}</p>
                    @enderror
                </div>
    
                <button type="submit" class="btn btn-primary">Cadastrar</button>
            </form>
        </div>
    </div>
</div>
@endsection

@extends('layout.main')

@section('content')
<div class="d-flex justify-content-center my-auto">
    <div class="card shadow shadow-md">
        <div class="card-body">
            <h3 class="card-title text-info">Verificação de E-mail</h3>
            <p class="card-text">Digite o código enviado para:<br/> {{ session('email') }}</p>
            
            <form class="d-flex flex-column gap-2 form-group" action="{{ route('signup.verify.submit') }}" method="POST">
                @csrf
                <input class="form-control" type="hidden" name="email" value="{{ session('email') }}">
                
                <div class="d-flex justify-content-center gap-2">
                    <input type="text" class="form-control text-center fs-2 otp-input rounded-circle bg-secondary text-light shadow shadow-md" maxlength="1" style="width: 60px; height: 60px;">
                    <input type="text" class="form-control text-center fs-2 otp-input rounded-circle bg-secondary text-light shadow shadow-md" maxlength="1" style="width: 60px; height: 60px;">
                    <input type="text" class="form-control text-center fs-2 otp-input rounded-circle bg-secondary text-light shadow shadow-md" maxlength="1" style="width: 60px; height: 60px;">
                    <input type="text" class="form-control text-center fs-2 otp-input rounded-circle bg-secondary text-light shadow shadow-md" maxlength="1" style="width: 60px; height: 60px;">
                    <input type="text" class="form-control text-center fs-2 otp-input rounded-circle bg-secondary text-light shadow shadow-md" maxlength="1" style="width: 60px; height: 60px;">
                    <input type="text" class="form-control text-center fs-2 otp-input rounded-circle bg-secondary text-light shadow shadow-md" maxlength="1" style="width: 60px; height: 60px;">
                </div>
                <input type="hidden" name="code" id="otp-value">
                @if (isset($error))
                    <p class="text-danger text-center my-2">{{ $error }}</p>
                @endif
                <button type="submit" class="btn btn-primary shadow shadow-sm" title="Verificar código">Verificar</button>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const inputs = document.querySelectorAll('.otp-input');
                    const form = document.querySelector('form');
                    const otpValue = document.getElementById('otp-value');

                    // Handle input
                    inputs.forEach((input, index) => {
                        input.addEventListener('input', function(e) {
                            if (this.value.length === 1) {
                                if (index < inputs.length - 1) {
                                    inputs[index + 1].focus();
                                }
                            }
                            combineOTPValue();
                        });

                        // Handle paste
                        input.addEventListener('paste', function(e) {
                            e.preventDefault();
                            const pastedData = e.clipboardData.getData('text');
                            const otpArray = pastedData.split('').slice(0, 6);
                            
                            otpArray.forEach((digit, i) => {
                                if (i < inputs.length) {
                                    inputs[i].value = digit;
                                }
                            });
                            
                            if (inputs[5].value) {
                                inputs[5].focus();
                            }
                            combineOTPValue();
                        });

                        // Handle backspace
                        input.addEventListener('keydown', function(e) {
                            if (e.key === 'Backspace' && !this.value && index > 0) {
                                inputs[index - 1].focus();
                                inputs[index - 1].value = '';
                            }
                            combineOTPValue();
                        });
                    });

                    function combineOTPValue() {
                        const combinedValue = Array.from(inputs).map(input => input.value).join('');
                        otpValue.value = combinedValue;
                    }

                    form.addEventListener('submit', function(e) {
                        combineOTPValue();
                    });
                });
            </script>
        </div>
    </div>
</div>
@endsection

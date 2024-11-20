<!DOCTYPE html>
<html>
<head>
    <style>
        .verification-code {
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 5px;
            color: #333;
            text-align: center;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 5px;
            margin: 20px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Confirmação de E-mail</h1>
        
        <p>Use o código abaixo para confirmar seu e-mail:</p>
        
        <div class="verification-code">
            <p>{{ $verificationCode }}</p>
        </div>
        
        <p>Este código expirará em 5 minutos.</p>
        
        <p>Se você não solicitou este código, pode ignorar este e-mail.</p>
        <p><em><strong>Esta é uma mensagem automática, não responda este e-mail.</strong></em></p>
    </div>
</body>
</html>
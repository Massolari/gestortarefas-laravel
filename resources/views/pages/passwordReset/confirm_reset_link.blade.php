<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Redefinição de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9f9f9;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Redefinição de Senha</h2>
        <p>Olá,</p>
        <p>Recebemos uma solicitação para redefinir sua senha. Se você não fez esta solicitação, por favor ignore este e-mail.</p>
        <p>Para redefinir sua senha, clique no botão abaixo:</p>
        
        <a href="{{ url('/password/reset/' . $token) }}" class="btn">Redefinir Senha</a>
        
        <p>Ou copie e cole o seguinte link no seu navegador:</p>
        <p>{{ url('/password/reset/' . $token) }}</p>
        
        <div class="footer">
            <p>Este link expirará em 60 minutos por motivos de segurança.</p>
            <p>Se você não solicitou a redefinição de senha, nenhuma ação é necessária.</p>
            <p><em><strong>Esta é uma mensagem automática, não responda este e-mail.</strong></em></p>
        </div>
    </div>
</body>
</html>

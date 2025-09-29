<!DOCTYPE html>
<html>
<head>
    <title>Restablecer Contraseña</title>
    <style>
        body { font-family: sans-serif; background-color: #fcfaf8; color: #3d475c; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border: 1px solid #e6e1da; border-radius: 8px; }
        .header { text-align: center; border-bottom: 1px solid #e6e1da; padding-bottom: 20px; margin-bottom: 20px; }
        .logo { max-height: 50px; }
        .content { font-size: 16px; line-height: 1.5; }
        .button { display: inline-block; padding: 12px 24px; margin: 20px 0; background-color: #e4a11b; color: #3d475c; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #8c96a9; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('img/icons/icon-72x72.png') }}" alt="Logo" class="logo">
        </div>
        <div class="content">
            <h2>¡Hola, {{ $userName }}!</h2>
            <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta. Si no fuiste tú, puedes ignorar este correo.</p>
            <p>Para continuar, haz clic en el siguiente botón:</p>
            <a href="{{ $resetUrl }}" class="button">Restablecer Contraseña</a>
            <p>Este enlace para restablecer la contraseña expirará en 60 minutos.</p>
            <p>Saludos,<br>El equipo de EON Grupo Empresarial</p>
        </div>
        <div class="footer">
            <p>Si tienes problemas con el botón, copia y pega la siguiente URL en tu navegador:</p>
            <p style="word-break: break-all;">{{ $resetUrl }}</p>
        </div>
    </div>
</body>
</html>
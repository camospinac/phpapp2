<!DOCTYPE html>
<html>
<head>
    <title>¡Bienvenido!</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; color: #333; }
        .container { max-width: 600px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 8px; }
        .header { text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .content { padding: 20px 0; }
        .button { display: inline-block; padding: 12px 24px; background-color: #e4a11b; color: #3d475c; text-decoration: none; border-radius: 5px; font-weight: bold; }
        .footer { margin-top: 20px; font-size: 12px; color: #888; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('img/icons/icon-72x72.png') }}" alt="Logo" class="logo">
            <h2>¡Bienvenido a EON Grupo Empresarial!</h2>
        </div>
        <div class="content">
            <h3>¡Hola, {{ $user->nombres }}!</h3>
            <p>Gracias por registrarte. Estamos muy contentos de tenerte con nosotros.</p>
            <p>Ahora puedes iniciar sesión y explorar todas las oportunidades de inversión que tenemos para ti.</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('login') }}" class="button">Ir a mi cuenta</a>
            </div>
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
            <p>Saludos,<br>El equipo de EON Grupo Empresarial</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} EON Grupo Empresarial. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
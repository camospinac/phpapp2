<!DOCTYPE html>
<html>
<head>
    <title>Tu Código de Retiro</title>
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
            <h2>Código de Retiro Generado</h2>
        </div>
        <div class="content">
            <h3>¡Hola, {{ $withdrawal->user->nombres }}!</h3>
            <p>Hemos generado con éxito tu código único para retirar <strong>{{ number_format($withdrawal->amount, 0, ',', '.') }} COP</strong>.</p>
            <p>Tu código es:</p>
            <div style="text-align: center; margin: 20px 0;">
                <p style="font-size: 36px; font-weight: bold; letter-spacing: 5px; background-color: #f4f4f4; padding: 15px; border-radius: 8px; display: inline-block;">
                    {{ $withdrawal->code }}
                </p>
            </div>
            <p>Por favor, presenta este código a un asesor en la oficina para completar tu retiro.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} EON Grupo Empresarial. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
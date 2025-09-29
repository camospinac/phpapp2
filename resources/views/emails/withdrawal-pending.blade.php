<!DOCTYPE html>
<html>
<head>
    <title>Alerta de Retiro</title>
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
            <h2>Nueva Solicitud de Retiro por Pagar</h2>
        </div>
        <div class="content">
            <p>El siguiente usuario ha solicitado un retiro y está esperando en la oficina:</p>
            <ul>
                <li><strong>Usuario:</strong> {{ $withdrawal->user->nombres }} {{ $withdrawal->user->apellidos }}</li>
                <li><strong>Monto a Pagar:</strong> {{ number_format($withdrawal->amount, 0, ',', '.') }} COP</li>
                <li><strong>Código de Verificación:</strong> {{ $withdrawal->code }}</li>
            </ul>
            <p>Por favor, busca la solicitud en el panel de administración usando el código para marcarla como completada.</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('admin.withdrawals.index') }}" class="button">Ir a Gestión de Retiros</a>
            </div>
        </div>
    </div>
</body>
</html>
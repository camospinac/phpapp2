<!DOCTYPE html>
<html>
<head>
    <title>Alerta de Aprobación</title>
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
            <h2>Nueva Suscripción por Verificar</h2>
        </div>
        <div class="content">
            <p>Se ha creado una nueva suscripción que requiere tu aprobación.</p>
            <ul>
                <li><strong>Usuario:</strong> {{ $subscription->user->nombres }} {{ $subscription->user->apellidos }}</li>
                <li><strong>Plan:</strong> {{ $subscription->plan->name }}</li>
                <li><strong>Monto:</strong> {{ number_format($subscription->initial_investment, 0, ',', '.') }} COP</li>
            </ul>
            <p>Por favor, revisa el comprobante de pago y aprueba la suscripción en el panel de administración.</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('admin.subscriptions.pending') }}" class="button">Ir a Aprobaciones</a>
            </div>
        </div>
        <div class="footer">
            <p>Este es un correo automático.</p>
        </div>
    </div>
</body>
</html>
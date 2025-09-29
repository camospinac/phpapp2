<!DOCTYPE html>
<html>
<head>
    <title>Confirmación de Inversión</title>
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
            <h2>¡Tu Inversión ha sido Creada!</h2>
        </div>
        <div class="content">
            <h3>¡Hola, {{ $subscription->user->nombres }}!</h3>
            <p>Te confirmamos que hemos recibido y registrado tu nueva inversión en el <strong>{{ $subscription->plan->name }}</strong>.</p>
            <ul>
                <li><strong>Monto Invertido:</strong> {{ number_format($subscription->initial_investment, 0, ',', '.') }} COP</li>
                <li><strong>Tipo de Contrato:</strong> {{ ucfirst($subscription->contract_type) }}</li>
                <li><strong>Estado:</strong> {{ $subscription->status === 'pending_verification' ? 'Pendiente de Verificación' : 'Activa' }}</li>
            </ul>
            <p>Puedes ver todos los detalles y el plan de pagos en tu dashboard.</p>
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ route('dashboard') }}" class="button">Ir a mi Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} EON Grupo Empresarial. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
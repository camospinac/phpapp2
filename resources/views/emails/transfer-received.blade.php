<!DOCTYPE html>
<html>
<body>
    <div class="container">
        <h3>¡Hola, {{ $recipient->nombres }}!</h3>
        <p>Te notificamos que has recibido <strong>{{ number_format($amount, 2, ',', '.') }} COP</strong> de parte de {{ $sender->nombres }}.</p>
        <p>El saldo ya ha sido abonado a tu cuenta y puedes verlo en tu historial de transacciones.</p>
    </div>
</body>
</html>
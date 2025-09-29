<!DOCTYPE html>
<html>
<body>
    <div class="container">
        <h3>¡Hola, {{ $sender->nombres }}!</h3>
        <p>Te confirmamos que has enviado exitosamente <strong>{{ number_format($amount, 2, ',', '.') }} COP</strong> a {{ $recipient->nombres }}.</p>
        <p>Puedes ver el detalle en tu historial de transacciones.</p>
    </div>
</body>
</html>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Extracto de Cuenta</title>
    <style>
        /* Estilos Generales */
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .page-break {
            page-break-after: always;
        }
        /* Contenedor Principal */
        .container {
            width: 100%;
            margin: 0 auto;
        }
        /* Encabezado */
        .header {
            border-bottom: 2px solid #e4a11b; /* Dorado */
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header .logo {
            width: 120px;
            height: auto;
        }
        .header .user-info {
            float: right;
            text-align: right;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #3d475c; /* Gris Carbón */
        }
        /* Sección de Resumen */
        .summary-section {
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-card {
            width: 44%;
            float: left;
            margin: 1%;
            padding: 15px;
            border: 1px solid #e6e1da; /* Borde cálido */
            border-radius: 5px;
            text-align: center;
             box-sizing: border-box;
        }
        .summary-card h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
            color: #8c96a9; /* Gris Muted */
            text-transform: uppercase;
        }
        .summary-card p {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
            color: #3d475c;
        }
        /* Tablas */
        .section-title {
            font-size: 18px;
            color: #3d475c;
            border-bottom: 1px solid #e6e1da;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .table th, .table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #fcfaf8; /* Blanco hueso */
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #28a745; }
        .text-red { color: #dc3545; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .capitalize { text-transform: capitalize; }
        /* Utilidades */
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div style="float: left;">
                <h1>Extracto de Cuenta</h1>
            </div>
            <div class="user-info">
                <strong>{{ $user->nombres }} {{ $user->apellidos }}</strong><br>
                {{ $user->identification_type }} - {{ $user->identification_number }}<br>
                Extracto generado el: {{ now()->format('d/m/Y') }}
            </div>
        </div>

        <div class="summary-section clearfix">
            <h2 class="section-title">Resumen General</h2>
            <div class="summary-card"><h4>Inversión Total</h4><p>{{ number_format($stats['totalInversion'], 0, ',', '.') }}</p></div>
            <div class="summary-card"><h4>Ganancia Total</h4><p class="text-green">+{{ number_format($stats['totalProfit'], 0, ',', '.') }}</p></div>
        </div>
        <div class="summary-section clearfix">
            <div class="summary-card"><h4>Retorno Total</h4><p>{{ number_format($stats['totalGanancia'], 0, ',', '.') }}</p></div>
            <div class="summary-card"><h4>Saldo Disponible</h4><p style="color: #0d9488;">{{ number_format($stats['totalAvailable'], 0, ',', '.') }}</p></div>
        </div>

        <div class="subscriptions-section">
            <h2 class="section-title">Detalle de Inversiones</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plan</th>
                        <th>Tipo</th>
                        <th class="text-right">Inversión</th>
                        <th class="text-right">Ganancia</th>
                        <th class="text-center">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscriptions as $sub)
                        <tr>
                            <td>#{{ $sub->sequence_id }}</td>
                            <td>{{ $sub->plan->name }}</td>
                            <td class="capitalize">{{ $sub->contract_type }}</td>
                            <td class="text-right font-mono">{{ number_format($sub->initial_investment, 0, ',', '.') }}</td>
                            <td class="text-right font-mono text-green">+{{ number_format($sub->profit_amount, 0, ',', '.') }}</td>
                            <td class="text-center capitalize">{{ str_replace('_', ' ', $sub->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center; padding: 20px;">No hay suscripciones para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="transactions-section">
            <h2 class="section-title">Historial de Transacciones</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Observación</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>
                <tbody>
                     @forelse($transactions as $tx)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y h:i A') }}</td>
                            <td class="capitalize {{ $tx->tipo === 'abono' ? 'text-green' : 'text-red' }}">
                                <strong>{{ $tx->tipo }}</strong><br>
                                <small>({{ $tx->type_detail }})</small>
                            </td>
                            <td>{{ $tx->observacion }}</td>
                            <td class="text-right font-mono {{ $tx->tipo === 'abono' ? 'text-green' : 'text-red' }}">
                                {{ $tx->tipo === 'abono' ? '+' : '-' }} {{ number_format($tx->monto, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="text-align:center; padding: 20px;">No hay transacciones para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>
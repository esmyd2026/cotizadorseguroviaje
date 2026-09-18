<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <title>{{ $quote->reference }}</title>
        <style>
            @page {
                margin: 40px 48px;
            }

            body {
                font-family: 'Helvetica', 'Arial', sans-serif;
                color: #16243d;
                font-size: 12px;
            }

            .header {
                display: table;
                width: 100%;
                margin-bottom: 24px;
                border-bottom: 2px solid #fcb333;
                padding-bottom: 16px;
            }

            .header .brand {
                display: table-cell;
                vertical-align: middle;
            }

            .header .brand img {
                width: 185px;
                height: auto;
            }

            .header .reference {
                display: table-cell;
                text-align: right;
                vertical-align: bottom;
            }

            .header .reference .label {
                color: #64748b;
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .header .reference .value {
                font-size: 14px;
                font-weight: bold;
                font-family: 'Courier New', monospace;
            }

            .badge {
                display: inline-block;
                margin-top: 4px;
                padding: 3px 10px;
                border-radius: 10px;
                font-size: 10px;
                font-weight: bold;
            }

            .badge-quoted {
                background-color: #fdecc4;
                color: #16243d;
            }

            .badge-contracted {
                background-color: #dce8ec;
                color: #16243d;
            }

            .price-banner {
                display: table;
                width: 100%;
                margin-bottom: 24px;
                border-radius: 10px;
                background-color: #16243d;
                color: #ffffff;
            }

            .price-banner .trip,
            .price-banner .price-wrap {
                display: table-cell;
                padding: 18px 20px;
                vertical-align: middle;
            }

            .price-banner .trip {
                width: 55%;
            }

            .price-banner .eyebrow {
                color: #fcb333;
                font-size: 9px;
                font-weight: bold;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .price-banner .destination {
                margin-top: 4px;
                font-size: 20px;
                font-weight: bold;
            }

            .price-banner .dates {
                margin-top: 5px;
                color: #dbe2ee;
                font-size: 10px;
            }

            .price-banner .price-wrap {
                text-align: right;
            }

            .price-banner .price {
                margin-top: 4px;
                font-size: 26px;
                font-weight: bold;
            }

            .section {
                margin-bottom: 20px;
            }

            .section-title {
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                color: #64748b;
                margin-bottom: 8px;
            }

            .box {
                border: 1px solid #e2e8f0;
                border-radius: 6px;
                padding: 12px 16px;
            }

            table.data {
                width: 100%;
                border-collapse: collapse;
            }

            table.data td {
                padding: 4px 0;
                vertical-align: top;
            }

            table.data td.label {
                color: #64748b;
                width: 40%;
            }

            table.breakdown {
                width: 100%;
                border-collapse: collapse;
                margin-top: 4px;
            }

            table.breakdown td {
                padding: 6px 0;
                border-bottom: 1px solid #e2e8f0;
            }

            table.breakdown td.amount {
                text-align: right;
            }

            table.breakdown tr.total td {
                border-bottom: none;
                border-top: 2px solid #16243d;
                font-weight: bold;
                font-size: 14px;
                padding-top: 10px;
            }

            .footer {
                margin-top: 32px;
                padding-top: 12px;
                border-top: 1px solid #e2e8f0;
                color: #94a3b8;
                font-size: 9px;
                text-align: center;
            }

            .footer .contact {
                margin-top: 4px;
                color: #696e77;
            }
        </style>
    </head>
    <body>
        @php($logo = base64_encode(file_get_contents(public_path('image/logo.png'))))
        <div class="header">
            <div class="brand">
                <img src="data:image/png;base64,{{ $logo }}" alt="Gestión Segura">
            </div>
            <div class="reference">
                <div class="label">Referencia</div>
                <div class="value">{{ $quote->reference }}</div>
                <div>
                    <span class="badge {{ $quote->status->value === 'contracted' ? 'badge-contracted' : 'badge-quoted' }}">
                        {{ $quote->status->label() }}
                    </span>
                </div>
            </div>
        </div>

        <div class="price-banner">
            <div class="trip">
                <div class="eyebrow">Seguro de viaje</div>
                <div class="destination">{{ $quote->destination_country_name }}</div>
                <div class="dates">
                    {{ $quote->departure_date->format('d/m/Y') }} &mdash; {{ $quote->return_date->format('d/m/Y') }}
                    &middot; {{ $quote->days }} días
                </div>
            </div>
            <div class="price-wrap">
                <div class="eyebrow">Total de la cotización</div>
                <div class="price">USD ${{ number_format($quote->total, 2) }}</div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Asegurado</div>
            <div class="box">
                <table class="data">
                    <tr>
                        <td class="label">Nombre</td>
                        <td>{{ $quote->insured->first_name }} {{ $quote->insured->last_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">{{ $quote->insured->document_type->label() }}</td>
                        <td>{{ $quote->insured->document_id }}</td>
                    </tr>
                    <tr>
                        <td class="label">Correo</td>
                        <td>{{ $quote->insured->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">Teléfono</td>
                        <td>{{ $quote->insured->phone }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Viaje</div>
            <div class="box">
                <table class="data">
                    <tr>
                        <td class="label">Destino</td>
                        <td>{{ $quote->destination_country_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fechas</td>
                        <td>{{ $quote->departure_date->format('d/m/Y') }} &mdash; {{ $quote->return_date->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Duración</td>
                        <td>{{ $quote->days }} días</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Cálculo</div>
            <div class="box">
                <table class="breakdown">
                    <tr>
                        <td>{{ $quote->days }} días &times; USD ${{ number_format($quote->daily_rate, 2) }}</td>
                        <td class="amount">USD ${{ number_format($quote->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Recargo {{ $regionLabel }} &middot; {{ (float) $quote->surcharge_percentage }}%</td>
                        <td class="amount">USD ${{ number_format($quote->surcharge_amount, 2) }}</td>
                    </tr>
                    <tr class="total">
                        <td>Total</td>
                        <td class="amount">USD ${{ number_format($quote->total, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="footer">
            Cotización generada el {{ $quote->created_at->format('d/m/Y H:i') }}
            @if ($quote->contracted_at)
                &middot; Contratada el {{ $quote->contracted_at->format('d/m/Y H:i') }}
            @endif
            <div class="contact">contacto@gestionsegura.com.ec &middot; +593 989596590 &middot; Cuenca, Ecuador</div>
        </div>
    </body>
</html>

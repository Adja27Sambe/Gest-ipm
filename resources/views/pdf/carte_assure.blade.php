<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carte d'Assuré - {{ $carte->numero_carte }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .carte-container {
            width: 100%;
            height: 100%;
            padding: 20px;
            box-sizing: border-box;
            background-color: #f8f9fa;
            border: 2px solid #0d6efd;
            border-radius: 15px;
            position: relative;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #0d6efd;
            text-transform: uppercase;
        }
        .header h2 {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #6c757d;
        }
        .content {
            display: table;
            width: 100%;
        }
        .info {
            display: table-cell;
            width: 65%;
            vertical-align: top;
        }
        .qr-code {
            display: table-cell;
            width: 35%;
            vertical-align: top;
            text-align: right;
        }
        .info-row {
            margin-bottom: 15px;
        }
        .info-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }
        .info-value {
            font-size: 18px;
            font-weight: bold;
            color: #212529;
        }
        .footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            font-size: 10px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="carte-container">
        <div class="header">
            <h1>Institution de Prévoyance Maladie</h1>
            <h2>Carte Virtuelle d'Assuré</h2>
        </div>

        <div class="content">
            <div class="info">
                <div class="info-row">
                    <div class="info-label">Numéro de Carte</div>
                    <div class="info-value">{{ $carte->numero_carte }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Salarié</div>
                    <div class="info-value">{{ $carte->salarie->prenom }} {{ $carte->salarie->nom }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Matricule</div>
                    <div class="info-value">{{ $carte->salarie->matricule }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Entreprise</div>
                    <div class="info-value">{{ $carte->salarie->entreprise->nom_entreprise }}</div>
                </div>
            </div>
            
            <div class="qr-code">
                <!-- QR Code base64 encoded by service -->
                <img src="data:image/svg+xml;base64,{{ base64_encode($carte->qr_code) }}" alt="QR Code" width="150" height="150">
            </div>
        </div>

        <div class="footer">
            Émise le : {{ \Carbon\Carbon::parse($carte->date_emission)->format('d/m/Y') }}
        </div>
    </div>
</body>
</html>

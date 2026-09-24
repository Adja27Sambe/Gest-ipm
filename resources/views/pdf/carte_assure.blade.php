<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carte d'Assuré - {{ $carte->numero_carte }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: 242.65pt 153.01pt;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 242.65pt;
            height: 153.01pt;
            overflow: hidden;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            color: #111111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .card-page {
            width: 242.65pt;
            height: 153.01pt;
            position: relative;
            overflow: hidden;
            box-sizing: border-box;
            background: #ffffff;
        }
        .page-break {
            page-break-after: always;
        }
        .bg-img {
            position: absolute;
            top: 0;
            left: 0;
            width: 242.65pt;
            height: 153.01pt;
            z-index: 1;
        }
        .card-content {
            position: absolute;
            top: 0;
            left: 0;
            width: 242.65pt;
            height: 153.01pt;
            z-index: 2;
        }
        .header {
            position: absolute;
            top: 14pt;
            left: 56pt;
            right: 6pt;
            height: 23pt;
        }
        .header-logo {
    
            height: 18pt;
            width: auto;
            vertical-align: middle;
        }
        .header-text {
            display: inline-block;
            vertical-align: middle;
            margin-left: 5pt;
        }
        .header-title {
            font-size: 14px;
            font-weight: bold;
            color: #045689;
            text-transform: uppercase;
            letter-spacing: 0.2pt;
            line-height: 1.15;
        }
        .header-sub {
            font-size: 12px;
            color: #1ea3e4;
            font-weight: bold;
            letter-spacing: 0.1pt;
        }
        .header-line {
            position: absolute;
            top: 28pt;
            left: 12pt;
            right: 8pt;
            height: 0.8pt;
            background-color: #045689;
            opacity: 0.25;
        }
        .photo-box {
            position: absolute;
            top: 31pt;
            left: 8pt;
            width: 50pt;
            height: 55pt;
            border: 1.2pt solid #045689;
            border-radius: 3pt;
            overflow: hidden;
            background: #f0f4f8;
            text-align: center;
        }
        .photo-img {
            width: 50pt;
            height: 55pt;
            object-fit: cover;
        }
        .qr-box {
            position: absolute;
            top: 90pt;
            left: 8pt;
            width: 50pt;
            height: 50pt;
            background: #ffffff;
            border: 0.8pt solid #d0d7de;
            border-radius: 2pt;
            padding: 1.5pt;
            text-align: center;
            box-sizing: border-box;
        }
        .qr-img {
            width: 47pt;
            height: 47pt;
            display: block;
            margin: 0 auto;
        }
        .details {
            position: absolute;
            top: 30pt;
            left: 63pt;
            right: 6pt;
        }
        .matricule-badge {
            background-color: #076B27;
            color: #ffffff;
            font-size: 14px;
            font-weight: bold;
            padding: 2pt 8pt;
            border-radius: 3pt;
            display: inline-block;
            letter-spacing: 0.3pt;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2pt;
        }
        .info-table td {
            padding: 1pt 0;
            font-size: 14px;
            vertical-align: middle;
        }
        .info-label {
            color: #555555;
            width: 44pt;
            font-weight: 500;
            font-size: 14px;
        }
        .info-val {
            color: #111111;
            font-weight: 600;
            font-size: 14px;
        }
        .info-nom {
            font-size: 15.5px;
            font-weight: bold;
            color: #045689;
            text-transform: uppercase;
        }
        .card-footer {
            position: absolute;
            bottom: 3pt;
            left: 63pt;
            right: 6pt;
            border-top: 0.5pt solid #d0d7de;
            padding-top: 1.5pt;
            font-size: 10.5px;
            color: #555555;
        }
        .verso-footer {
            position: absolute;
            bottom: 2pt;
            left: 8pt;
            right: 8pt;
            text-align: center;
            font-size: 11px;
            color: #045689;
            font-weight: bold;
            letter-spacing: 0.3pt;
        }
    </style>
</head>
<body>
    @php
        $salarie = $carte->salarie;
        $entrepriseNom = mb_strtoupper($salarie->entreprise->raison_sociale ?? $salarie->entreprise->nom_entreprise ?? 'SOCIETE');
        $matricule = $salarie->matricule ?? $carte->matricule ?? 'XXXXX';
        $nom = mb_strtoupper($salarie->nom ?? 'XXXX');
        $prenom = $salarie->prenom ?? 'XXXXXX';
        $telephone = $salarie->telephone ?? '';
        $dateNaissance = $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->format('d/m/Y') : '—';
        $numeroCarte = $carte->numero_carte ?? '—';
        $dateEmission = $carte->date_emission ? \Carbon\Carbon::parse($carte->date_emission)->format('d/m/Y') : date('d/m/Y');

        // Préparation des images en base64 pour DomPDF (100% fiable, 0 latence)
        $rectoBgPath = public_path('images/template_carte_recto_bg.png');
        $rectoBgBase64 = file_exists($rectoBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($rectoBgPath)) : '';

        $versoBgPath = public_path('images/template_carte_verso.png');
        $versoBgBase64 = file_exists($versoBgPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($versoBgPath)) : '';

        $logoPath = public_path('images/logo_clean.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($carte->qr_code);

        $photoBase64 = null;
        if ($salarie && $salarie->photo) {
            $p = public_path('storage/' . ltrim($salarie->photo->path, '/'));
            if (file_exists($p)) {
                $ext = strtolower(pathinfo($p, PATHINFO_EXTENSION));
                $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg' : 'image/png';
                $photoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($p));
            }
        }
    @endphp

    <!-- PAGE 1: RECTO -->
    <div class="card-page page-break">
        @if($rectoBgBase64)
            <img src="{{ $rectoBgBase64 }}" class="bg-img">
        @endif
        <div class="card-content">
            <div class="header">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="header-logo">
                @endif
                <div class="header-text">
                    <div class="header-title">INSTITUT DE PRÉVOYANCE MALADIE</div>
                    <div class="header-sub">INTER-ENTREPRISES</div>
                    <div class="header-sub">CARTE DE PARTICIPANT ASSURÉ</div>
                </div>
            </div>
            <div class="header-line"></div>

            <div class="photo-box">
                @if($photoBase64)
                    <img src="{{ $photoBase64 }}" class="photo-img">
                @else
                    <div style="font-size: 8pt; color: #888888; margin-top: 17pt; font-weight: bold;">PHOTO</div>
                @endif
            </div>

            <div class="qr-box">
                <img src="{{ $qrCodeBase64 }}" class="qr-img">
            </div>

            <div class="details">
                <div class="matricule-badge">MATRICULE : {{ $matricule }}</div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Nom :</td>
                        <td class="info-val info-nom">{{ $nom }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Prénom :</td>
                        <td class="info-val" style="font-size: 8.5pt;">{{ $prenom }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Né(e) le :</td>
                        <td class="info-val">{{ $dateNaissance }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Société :</td>
                        <td class="info-val" style="font-weight: bold; color: #045689; font-size: 7.8pt;">{{ $entrepriseNom }}</td>
                    </tr>
                    @if($telephone)
                    <tr>
                        <td class="info-label">Téléphone :</td>
                        <td class="info-val">{{ $telephone }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div class="card-footer">
                <span style="float: right;">Émise le : <strong>{{ $dateEmission }}</strong></span>
            </div>
        </div>
    </div>

    <!-- PAGE 2: VERSO -->
    <div class="card-page">
        @if($versoBgBase64)
            <img src="{{ $versoBgBase64 }}" class="bg-img">
        @endif
        <div class="card-content">
            <div class="verso-footer">
            Présentez cette carte aux prestataires agréés &bull;
            </div>
        </div>
    </div>
</body>
</html>

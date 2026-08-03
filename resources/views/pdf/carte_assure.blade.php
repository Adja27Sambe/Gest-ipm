<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carte d'Assuré - {{ $carte->numero_carte }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
            size: 85mm 54mm;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 85mm;
            height: 54mm;
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #ffffff;
            color: #111111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .page {
            width: 85mm;
            height: 54mm;
            margin: 0;
            padding: 0;
            position: relative;
            box-sizing: border-box;
            overflow: hidden;
            background: #ffffff;
            border: 1.5pt solid #005689;
        }
        .page-break {
            page-break-after: always;
        }
        /* Top Header Banner */
        .header-banner {
            background-color: #005689;
            color: #ffffff;
            text-align: center;
            padding: 3mm 2mm;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        /* Bottom Footer Banner */
        .footer-banner {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 3mm;
            background-color: #005689;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .header-title {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            line-height: 1.1;
        }
        .header-subtitle {
            font-size: 5.5pt;
            margin-top: 1px;
            opacity: 0.95;
        }
        /* Content Layout */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
        }
        .left-col {
            width: 32%;
            vertical-align: top;
            text-align: center;
            padding-left: 2mm;
        }
        .right-col {
            width: 68%;
            vertical-align: top;
            padding-left: 2mm;
            padding-right: 2mm;
        }
        /* Photo Circle */
        .photo-circle {
            width: 16mm;
            height: 16mm;
            border-radius: 50%;
            border: 1.5pt solid #005689;
            margin: 0 auto;
            overflow: hidden;
            background: #eef2f5;
        }
        .photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .societe-text {
            font-size: 6.5pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 1mm;
            color: #111111;
            line-height: 1;
        }
        .qr-box {
            margin-top: 1.5mm;
            text-align: center;
        }
        /* Green Matricule Badge */
        .matricule-badge {
            background-color: #076B27;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 1.5mm 3mm;
            display: inline-block;
            text-transform: uppercase;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        /* Info Table */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2mm;
        }
        .info-table td {
            padding: 1px 0;
            font-size: 7pt;
        }
        .label-cell {
            color: #333333;
            width: 32mm;
        }
        .value-cell {
            font-weight: bold;
            color: #000000;
        }
        /* Verso styling */
        .verso-container {
            width: 100%;
            height: calc(100% - 6mm);
            text-align: center;
            vertical-align: middle;
            display: table;
        }
        .verso-cell {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
        }
        .verso-logo-title {
            font-size: 16pt;
            font-weight: bold;
            color: #1EA3E4;
            letter-spacing: 0.5px;
        }
        .verso-logo-subtitle {
            font-size: 8pt;
            font-weight: bold;
            color: #005689;
            margin-top: 2mm;
        }
    </style>
</head>
<body>
    @php
        $salarie = $carte->salarie;
        $entrepriseNom = $salarie->entreprise->raison_sociale ?? $salarie->entreprise->nom_entreprise ?? 'SOCIETE';
        $matricule = $salarie->matricule ?? $carte->matricule ?? 'XXXXX';
        $nom = mb_strtoupper($salarie->nom ?? 'XXXX');
        $prenom = $salarie->prenom ?? 'XXXXXX';
        $telephone = $salarie->telephone ?? 'XXXXXX';
        $dateNaissance = $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->format('d/m/Y') : 'XXXX';
        
        $logoPath = public_path('logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
    @endphp

    <!-- PAGE 1: RECTO -->
    <div class="page page-break">
        <div class="header-banner">
            <div class="header-title">INSTITUT DE PREVOYANCE MALADIE INTER–ENTREPRISE MBAARUM KOOLUTE</div>
            <div class="header-subtitle">SIEGE : Cité de l’Emergence ADDOHA Immeuble 7 Appartement N°4</div>
        </div>

        <table class="content-table">
            <tr>
                <!-- Left Column -->
                <td class="left-col">
                    <div class="photo-circle">
                        @if($salarie->photo)
                            <img src="{{ $salarie->photo->url }}" class="photo-img">
                        @else
                            <div style="font-size: 10pt; color: #777; margin-top: 4mm;">P</div>
                        @endif
                    </div>
                    <div class="societe-text">{{ $entrepriseNom }}</div>
                    <div class="qr-box">
                        <img src="data:image/svg+xml;base64,{{ base64_encode($carte->qr_code) }}" width="65" height="65">
                    </div>
                </td>

                <!-- Right Column -->
                <td class="right-col">
                    <div class="matricule-badge">
                        MATRICULE : {{ $matricule }}
                    </div>

                    <table class="info-table">
                        <tr>
                            <td class="label-cell">Nom :</td>
                            <td class="value-cell">{{ $nom }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Prénom :</td>
                            <td class="value-cell">{{ $prenom }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Téléphone :</td>
                            <td class="value-cell">{{ $telephone }}</td>
                        </tr>
                        <tr>
                            <td class="label-cell">Date de Naissance :</td>
                            <td class="value-cell">{{ $dateNaissance }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <div class="footer-banner"></div>
    </div>

    <!-- PAGE 2: VERSO -->
    <div class="page">
        <div class="header-banner" style="padding: 1.5mm 0;"></div>
        <div class="verso-container">
            <div class="verso-cell">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-height: 25mm; max-width: 60mm; object-fit: contain;">
                @else
                    <div class="verso-logo-title">MBAARUM KOOLUTE</div>
                @endif
                <div class="verso-logo-subtitle">Institut de prévoyance maladie inter-entreprises</div>
            </div>
        </div>
        <div class="footer-banner"></div>
    </div>
</body>
</html>

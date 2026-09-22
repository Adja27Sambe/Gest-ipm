<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Rapport' }}</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            width: 100%;
            margin-bottom: 30px;
            border-bottom: 2px solid #004b93;
            padding-bottom: 10px;
        }
        .header table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }
        .header td {
            vertical-align: middle;
            border: none;
            padding: 0;
        }
        .header-title {
            text-align: right;
            color: #004b93;
        }
        .header-title h1 {
            margin: 0;
            font-size: 20px;
            text-transform: uppercase;
        }
        .header-title p {
            margin: 5px 0 0;
            color: #666;
            font-size: 11px;
        }
        h2 {
            color: #444;
            font-size: 16px;
            margin-bottom: 15px;
            text-align: center;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        table.data-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .footer {
            position: fixed;
            bottom: 0px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 10px;
            color: #777;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
        }
        .summary-title { font-weight: bold; margin-bottom: 10px; font-size: 14px; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header">
        @php
            $logoPath = public_path('logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
            }
        @endphp
        <table>
            <tr>
                <td width="50%">
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="Logo IPM" style="max-height: 45px; object-fit: contain;">
                    @else
                        <strong style="font-size: 24px; color: #004b93;">IPM GEST</strong>
                    @endif
                    <br>
                    <span style="color: #666; font-size: 11px;">Institution de Prévoyance Maladie</span>
                </td>
                <td width="50%" class="header-title">
                    <h1>{{ $title }}</h1>
                    <p>Édité le {{ date('d/m/Y à H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="content">
        @include($view_content)
    </div>

    <div class="footer">
        IPM GEST - Rapport généré automatiquement - Page 1
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Historique des Paiements</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #004b93; padding-bottom: 10px; }
        .header table { width: 100%; border: none; }
        .header td { border: none; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
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
                <td width="50%" style="text-align: right;">
                    <h2 style="margin: 0; color: #004b93;">Historique des Paiements</h2>
                    <p style="margin: 5px 0 0 0;">Édité le {{ date('d/m/Y à H:i') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Paiement</th>
                <th>Date</th>
                <th>Facture N°</th>
                <th>Prestataire</th>
                <th>Montant Payé</th>
                <th>Mode de Paiement</th>
                <th>Référence Transaction</th>
            </tr>
        </thead>
        <tbody>
            @foreach($paiements as $paiement)
                <tr>
                    <td>{{ $paiement->id_paiement }}</td>
                    <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                    <td>{{ $paiement->facture->numero_facture ?? 'N/A' }}</td>
                    <td>{{ $paiement->facture->prestataire->nom ?? 'N/A' }}</td>
                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                    <td>{{ $paiement->mode_paiement }}</td>
                    <td>{{ $paiement->reference_transaction }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

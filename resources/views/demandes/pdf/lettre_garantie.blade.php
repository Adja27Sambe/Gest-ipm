<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Lettre de Garantie</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .details { margin-bottom: 20px; }
        .details th, .details td { text-align: left; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Lettre de Garantie</h1>
        <p>Date d'émission: {{ $lettreGarantie->date_emission ? \Carbon\Carbon::parse($lettreGarantie->date_emission)->format('d/m/Y') : date('d/m/Y') }}</p>
        <p>N° {{ $lettreGarantie->numero_lettre }}</p>
    </div>

    <div class="details">
        <table>
            <tr>
                <th>Participant :</th>
                <td>{{ $demande->salarie->nom }} {{ $demande->salarie->prenom }} (Matricule: {{ $demande->salarie->matricule ?? 'N/A' }})</td>
            </tr>
            <tr>
                <th>Bénéficiaire :</th>
                <td>
                    @if($demande->ayantDroit)
                        {{ $demande->ayantDroit->nom }} {{ $demande->ayantDroit->prenom }} 
                        (Né(e) le: {{ $demande->ayantDroit->date_naissance ? \Carbon\Carbon::parse($demande->ayantDroit->date_naissance)->format('d/m/Y') : 'N/A' }})
                    @else
                        Lui-même
                    @endif
                </td>
            </tr>
            <tr>
                <th>Praticien :</th>
                <td>{{ $demande->prestataire->nom ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Acte couvert :</th>
                <td>{{ ucfirst($lettreGarantie->choix_acte) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Validité de la lettre: {{ $lettreGarantie->date_validite ? \Carbon\Carbon::parse($lettreGarantie->date_validite)->format('d/m/Y') : ($demande->date_limite_validite ? \Carbon\Carbon::parse($demande->date_limite_validite)->format('d/m/Y') : 'Fin du mois') }}</p>
        <p>Signature & Cachet de l'IPM</p>
    </div>
</body>
</html>

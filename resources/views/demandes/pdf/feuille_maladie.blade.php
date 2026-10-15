<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Feuille de Maladie</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px; }
        .details { margin-bottom: 20px; }
        .details th, .details td { text-align: left; padding: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Feuille de Maladie</h1>
        <p>Date d'émission: {{ $feuilleMaladie->date_emission ? \Carbon\Carbon::parse($feuilleMaladie->date_emission)->format('d/m/Y') : date('d/m/Y') }}</p>
        <p>Date limite de validité: {{ $demande->date_limite_validite ? \Carbon\Carbon::parse($demande->date_limite_validite)->format('d/m/Y') : 'Fin du mois' }}</p>
        <p>N° {{ $feuilleMaladie->numero_feuille }}</p>
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
                        {{ $demande->ayantDroit->nom }} {{ $demande->ayantDroit->prenom }} (Ayant droit)
                    @else
                        Lui-même
                    @endif
                </td>
            </tr>
            <tr>
                <th>Praticien :</th>
                <td>{{ $demande->prestataire->nom ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Signature & Cachet du Praticien</p>
    </div>
</body>
</html>

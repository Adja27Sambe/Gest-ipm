<div class="summary-box">
    <div class="summary-title">Résumé des Frais pour {{ $salarie->nom_complet }}</div>
    <table style="width: 100%; border:none;">
        <tr>
            <td>Total Facturé: <span class="font-bold">{{ number_format($prestations->sum('montant'), 0, ',', ' ') }} FCFA</span></td>
            <td>Prise en charge (IPM): <span class="font-bold text-success">{{ number_format($prestations->sum('taux_prise_charge'), 0, ',', ' ') }} FCFA</span></td>
            <td>Ticket Modérateur: <span class="font-bold text-danger">{{ number_format($prestations->sum('reste_a_charge'), 0, ',', ' ') }} FCFA</span></td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>N° Demande</th>
            <th>Prestation</th>
            <th>Prestataire</th>
            <th class="text-right">Total</th>
            <th class="text-right">Prise en charge</th>
            <th class="text-right">Reste à charge</th>
        </tr>
    </thead>
    <tbody>
        @forelse($prestations as $prestation)
            <tr>
                <td>{{ \Carbon\Carbon::parse($prestation->date_prestation)->format('d/m/Y') }}</td>
                <td>{{ $prestation->demande->numero_demande ?? '-' }}</td>
                <td>{{ $prestation->typePrestation->libelle ?? 'Autre' }}</td>
                <td>
                    @if($prestation->praticien)
                        {{ $prestation->praticien->nom_complet }}
                    @elseif($prestation->pharmacie)
                        {{ $prestation->pharmacie->nom_pharmacie }}
                    @else
                        -
                    @endif
                </td>
                <td class="text-right font-bold">{{ number_format($prestation->montant, 0, ',', ' ') }}</td>
                <td class="text-right text-success">{{ number_format($prestation->taux_prise_charge, 0, ',', ' ') }}</td>
                <td class="text-right text-danger">{{ number_format($prestation->reste_a_charge, 0, ',', ' ') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center">Aucune prestation enregistrée pour ce salarié.</td>
            </tr>
        @endforelse
    </tbody>
</table>

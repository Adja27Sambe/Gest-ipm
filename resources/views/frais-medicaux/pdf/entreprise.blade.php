<div class="summary-box">
    <div class="summary-title">Résumé des Frais pour l'Entreprise</div>
    <table style="width: 100%; border:none;">
        <tr>
            <td>Total Facturé: <span class="font-bold">{{ number_format($salaries->sum('total_frais'), 0, ',', ' ') }} FCFA</span></td>
            <td>Prise en charge (IPM): <span class="font-bold text-success">{{ number_format($salaries->sum('total_prise_charge'), 0, ',', ' ') }} FCFA</span></td>
        </tr>
    </table>
</div>

@forelse($salaries->sortByDesc('total_frais') as $salarie)
    @if($salarie->total_frais > 0)
        <div style="margin-top: 25px; padding: 10px; background-color: #e9ecef; font-weight: bold; font-size: 13px;">
            Participant : {{ $salarie->nom_complet }} (Matricule: {{ $salarie->matricule }})
        </div>
        <table class="data-table" style="margin-top: 0; margin-bottom: 5px;">
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
                @foreach($salarie->prestations_list as $prestation)
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
                        <td class="text-right text-success">{{ number_format($prestation->montant - $prestation->reste_a_charge, 0, ',', ' ') }} ({{ number_format($prestation->taux_prise_charge, 0) }}%)</td>
                        <td class="text-right text-danger">{{ number_format($prestation->reste_a_charge, 0, ',', ' ') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <table style="width: 100%; font-weight: bold; margin-bottom: 20px; font-size: 11px;">
            <tr>
                <td style="text-align: right; width: 60%;">Sous-total Salarié :</td>
                <td class="text-right" style="width: 13.33%;">{{ number_format($salarie->total_frais, 0, ',', ' ') }}</td>
                <td class="text-right text-success" style="width: 13.33%;">{{ number_format($salarie->total_prise_charge, 0, ',', ' ') }}</td>
                <td class="text-right text-danger" style="width: 13.33%;">{{ number_format($salarie->total_frais - $salarie->total_prise_charge, 0, ',', ' ') }}</td>
            </tr>
        </table>
    @endif
@empty
    <p class="text-center">Aucun participant avec des frais trouvés pour cet adhérent.</p>
@endforelse

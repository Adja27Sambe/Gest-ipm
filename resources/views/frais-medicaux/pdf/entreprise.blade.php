<div class="summary-box">
    <div class="summary-title">Résumé des Frais pour l'Entreprise</div>
    <table style="width: 100%; border:none;">
        <tr>
            <td>Total Facturé: <span class="font-bold">{{ number_format($salaries->sum('total_frais'), 0, ',', ' ') }} FCFA</span></td>
            <td>Prise en charge (IPM): <span class="font-bold text-success">{{ number_format($salaries->sum('total_prise_charge'), 0, ',', ' ') }} FCFA</span></td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Participant (Salarié)</th>
            <th class="text-right">Frais Total</th>
            <th class="text-right">Prise en charge</th>
        </tr>
    </thead>
    <tbody>
        @forelse($salaries->sortByDesc('total_frais') as $salarie)
            @if($salarie->total_frais > 0)
            <tr>
                <td>{{ $salarie->matricule }}</td>
                <td>{{ $salarie->nom_complet }}</td>
                <td class="text-right font-bold">{{ number_format($salarie->total_frais, 0, ',', ' ') }} FCFA</td>
                <td class="text-right text-success">{{ number_format($salarie->total_prise_charge, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
        @empty
            <tr>
                <td colspan="4" class="text-center">Aucun participant avec des frais trouvés.</td>
            </tr>
        @endforelse
    </tbody>
</table>

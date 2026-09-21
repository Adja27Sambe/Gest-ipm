<div class="summary-box">
    <div class="summary-title">Résumé des Totaux</div>
    <table style="width: 100%; border:none;">
        <tr>
            <td>Total Général Facturé: <span class="font-bold">{{ number_format($entreprises->sum('total_frais'), 0, ',', ' ') }} FCFA</span></td>
            <td>Prise en charge (IPM): <span class="font-bold text-success">{{ number_format($entreprises->sum('total_prise_charge'), 0, ',', ' ') }} FCFA</span></td>
        </tr>
    </table>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>Adhérent (Entreprise)</th>
            <th>Code</th>
            <th class="text-right">Montant Total Facturé</th>
            <th class="text-right">Prise en charge (IPM)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($entreprises as $entreprise)
            <tr>
                <td>{{ $entreprise->raison_sociale }}</td>
                <td>{{ $entreprise->code_adherent }}</td>
                <td class="text-right font-bold">{{ number_format($entreprise->total_frais ?? 0, 0, ',', ' ') }} FCFA</td>
                <td class="text-right text-success">{{ number_format($entreprise->total_prise_charge ?? 0, 0, ',', ' ') }} FCFA</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">Aucune entreprise trouvée.</td>
            </tr>
        @endforelse
    </tbody>
</table>

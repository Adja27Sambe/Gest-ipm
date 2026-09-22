<table>
    <thead>
        <tr>
            <th colspan="4" style="font-weight: bold; text-align: center; font-size: 16px;">{{ $title }}</th>
        </tr>
        <tr>
            <th colspan="4">Date d'édition : {{ date('d/m/Y H:i') }}</th>
        </tr>
        <tr>
            <th colspan="4"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f5f5f5;">Adhérent (Entreprise)</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Code</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Montant Total Facturé</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Prise en charge (IPM)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($entreprises as $entreprise)
            <tr>
                <td>{{ $entreprise->raison_sociale }}</td>
                <td>{{ $entreprise->code_adherent }}</td>
                <td>{{ $entreprise->total_frais ?? 0 }}</td>
                <td>{{ $entreprise->total_prise_charge ?? 0 }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="2" style="font-weight: bold; text-align: right;">TOTAL GÉNÉRAL</td>
            <td style="font-weight: bold;">{{ collect($entreprises)->sum('total_frais') }}</td>
            <td style="font-weight: bold;">{{ collect($entreprises)->sum('total_prise_charge') }}</td>
        </tr>
    </tbody>
</table>

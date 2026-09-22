<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; text-align: center; font-size: 16px;">{{ $title }}</th>
        </tr>
        <tr>
            <th colspan="7">Date d'édition : {{ date('d/m/Y H:i') }}</th>
        </tr>
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f5f5f5;">Matricule / Salarié</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Date</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">N° Demande</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Prestation</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Total</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Prise en charge</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Reste à charge</th>
        </tr>
    </thead>
    <tbody>
        @foreach($salaries->sortByDesc('total_frais') as $salarie)
            @if($salarie->total_frais > 0)
                <tr>
                    <td colspan="7" style="font-weight: bold; background-color: #e9ecef;">{{ $salarie->nom_complet }} ({{ $salarie->matricule }})</td>
                </tr>
                @foreach($salarie->prestations_list as $prestation)
                    <tr>
                        <td></td>
                        <td>{{ \Carbon\Carbon::parse($prestation->date_prestation)->format('d/m/Y') }}</td>
                        <td>{{ $prestation->demande->numero_demande ?? '-' }}</td>
                        <td>{{ $prestation->typePrestation->libelle ?? 'Autre' }}</td>
                        <td>{{ $prestation->montant }}</td>
                        <td>{{ $prestation->montant - $prestation->reste_a_charge }}</td>
                        <td>{{ $prestation->reste_a_charge }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="font-weight: bold; text-align: right;">Sous-total Salarié :</td>
                    <td style="font-weight: bold;">{{ $salarie->total_frais }}</td>
                    <td style="font-weight: bold;">{{ $salarie->total_prise_charge }}</td>
                    <td style="font-weight: bold;">{{ $salarie->total_frais - $salarie->total_prise_charge }}</td>
                </tr>
            @endif
        @endforeach
        
        <tr>
            <th colspan="7"></th>
        </tr>
        <tr>
            <td colspan="4" style="font-weight: bold; text-align: right; font-size: 14px;">TOTAL GÉNÉRAL ENTREPRISE :</td>
            <td style="font-weight: bold; font-size: 14px;">{{ collect($salaries)->sum('total_frais') }}</td>
            <td style="font-weight: bold; font-size: 14px;">{{ collect($salaries)->sum('total_prise_charge') }}</td>
            <td style="font-weight: bold; font-size: 14px;">{{ collect($salaries)->sum('total_frais') - collect($salaries)->sum('total_prise_charge') }}</td>
        </tr>
    </tbody>
</table>

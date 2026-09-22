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
            <th style="font-weight: bold; background-color: #f5f5f5;">Date</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">N° Demande</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Prestation</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Prestataire</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Total</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Prise en charge</th>
            <th style="font-weight: bold; background-color: #f5f5f5;">Reste à charge</th>
        </tr>
    </thead>
    <tbody>
        @foreach($prestations as $prestation)
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
                <td>{{ $prestation->montant }}</td>
                <td>{{ $prestation->montant - $prestation->reste_a_charge }}</td>
                <td>{{ $prestation->reste_a_charge }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" style="font-weight: bold; text-align: right;">TOTAL :</td>
            <td style="font-weight: bold;">{{ collect($prestations)->sum('montant') }}</td>
            <td style="font-weight: bold;">{{ collect($prestations)->sum(function($p) { return $p->montant - $p->reste_a_charge; }) }}</td>
            <td style="font-weight: bold;">{{ collect($prestations)->sum('reste_a_charge') }}</td>
        </tr>
    </tbody>
</table>

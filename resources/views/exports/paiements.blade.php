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
                <td>{{ $paiement->montant }}</td>
                <td>{{ $paiement->mode_paiement }}</td>
                <td>{{ $paiement->reference_transaction }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

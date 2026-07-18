@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tableau de bord - Factures Impayées</h2>
        <div>
            <a href="{{ url('api/paiements/export?format=pdf') }}" target="_blank" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exporter PDF
            </a>
            <a href="{{ url('api/paiements/export?format=excel') }}" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exporter Excel
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form action="{{ route('factures.index') }}" method="GET" class="row align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Prestataire</label>
                    <select name="id_prestataire" class="form-select">
                        <option value="">Tous les prestataires</option>
                        @foreach($prestataires as $prest)
                            <option value="{{ $prest->id_prestataire }}" {{ request('id_prestataire') == $prest->id_prestataire ? 'selected' : '' }}>
                                {{ $prest->nom }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('factures.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Total Dû -->
    <div class="alert alert-warning shadow-sm">
        <h4 class="mb-0"><strong>Total Restant Dû : </strong> {{ number_format($totalDu, 2, ',', ' ') }} FCFA</h4>
    </div>

    <!-- Liste des factures -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>N° Facture</th>
                        <th>Date</th>
                        <th>Prestataire</th>
                        <th>Montant Initial</th>
                        <th>Reste à Payer</th>
                        <th>Statut</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                        <tr>
                            <td><strong>{{ $facture->numero_facture }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</td>
                            <td>{{ $facture->prestataire->nom ?? 'N/A' }}</td>
                            <td>{{ number_format($facture->montant, 2, ',', ' ') }} FCFA</td>
                            <td class="text-danger fw-bold">{{ number_format($facture->soldeRestant, 2, ',', ' ') }} FCFA</td>
                            <td>
                                @if($facture->statut_paiement == 'en_attente')
                                    <span class="badge bg-danger">En attente</span>
                                @elseif($facture->statut_paiement == 'partiellement_payee')
                                    <span class="badge bg-warning text-dark">Partiel</span>
                                @else
                                    <span class="badge bg-success">Soldée</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('factures.show', $facture->id_facture) }}" class="btn btn-sm btn-info text-white">
                                    <i class="fas fa-eye"></i> Gérer
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-3">Aucune facture impayée trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $factures->links() }}
    </div>
</div>
@endsection

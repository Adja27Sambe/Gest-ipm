@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h2>Détail de la Facture : <span class="text-primary">{{ $facture->numero_facture }}</span></h2>
        <a href="{{ route('factures.index') }}" class="btn btn-secondary">Retour aux factures</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Informations Facture -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white">
                    <strong>Informations Générales</strong>
                </div>
                <div class="card-body">
                    <p><strong>Prestataire :</strong> {{ $facture->prestataire->nom ?? 'N/A' }}</p>
                    <p><strong>Date d'émission :</strong> {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</p>
                    <p><strong>Montant Total (Part IPM) :</strong> {{ number_format($facture->montant, 2, ',', ' ') }} FCFA</p>
                    <p class="text-danger fw-bold"><strong>Reste à Payer :</strong> {{ number_format($facture->soldeRestant, 2, ',', ' ') }} FCFA</p>
                    <p>
                        <strong>Statut :</strong>
                        @if($facture->statut_paiement == 'en_attente')
                            <span class="badge bg-danger">En attente</span>
                        @elseif($facture->statut_paiement == 'partiellement_payee')
                            <span class="badge bg-warning text-dark">Partiel</span>
                        @else
                            <span class="badge bg-success">Soldée</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulaire de Paiement -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100 border-success">
                <div class="card-header bg-success text-white">
                    <strong>Enregistrer un paiement</strong>
                </div>
                <div class="card-body">
                    @if($facture->soldeRestant > 0)
                        <form action="{{ route('factures.paiements.store', $facture->id_facture) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Montant à payer</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" max="{{ $facture->soldeRestant }}" name="montant" class="form-control" value="{{ $facture->soldeRestant }}" required>
                                    <span class="input-group-text">FCFA</span>
                                </div>
                                <small class="text-muted">Maximum : {{ number_format($facture->soldeRestant, 2, ',', ' ') }}</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mode de paiement</label>
                                <select name="mode_paiement" class="form-select" required>
                                    <option value="virement">Virement Bancaire</option>
                                    <option value="cheque">Chèque</option>
                                    <option value="especes">Espèces</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Référence (N° Chèque/Virement)</label>
                                <input type="text" name="reference_transaction" class="form-control">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success">Valider le paiement</button>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-success text-center mt-4">
                            Cette facture a été entièrement soldée. Aucun paiement supplémentaire requis.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Historique des paiements -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <strong>Historique des paiements</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Mode</th>
                                <th>Réf.</th>
                                <th>Montant</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facture->paiementPrestataires as $paiement)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($paiement->mode_paiement) }}</span></td>
                                    <td>{{ $paiement->reference_transaction }}</td>
                                    <td class="text-success fw-bold">{{ number_format($paiement->montant, 2, ',', ' ') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Aucun paiement enregistré pour le moment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Prestations associées -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-secondary text-white">
                    <strong>Prestations regroupées dans cette facture</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Montant Total</th>
                                <th>Part IPM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($facture->prestations as $prestation)
                                <tr>
                                    <td>#{{ $prestation->id_prestation }}</td>
                                    <td>{{ \Carbon\Carbon::parse($prestation->date_prestation)->format('d/m/Y') }}</td>
                                    <td>{{ number_format($prestation->montant, 2, ',', ' ') }}</td>
                                    <td><strong>{{ number_format($prestation->montant - $prestation->reste_a_charge, 2, ',', ' ') }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3">Aucune prestation associée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

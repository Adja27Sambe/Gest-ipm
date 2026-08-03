@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-1">
                Détail de la Facture 
                <span class="text-primary">#{{ $facture->numero_facture }}</span>
            </h2>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted"><i class="bi bi-calendar me-1"></i> Émise le {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') }}</span>
                @if($facture->statut_paiement == 'en_attente')
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-1 rounded-pill"><i class="bi bi-clock-history me-1"></i> En attente</span>
                @elseif($facture->statut_paiement == 'partiellement_payee')
                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-3 py-1 rounded-pill"><i class="bi bi-pie-chart me-1"></i> Partiellement Payée</span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-1 rounded-pill"><i class="bi bi-check-circle me-1"></i> Soldée</span>
                @endif
            </div>
        </div>
        <a href="{{ route('factures.index') }}" class="btn btn-light border shadow-sm rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Retour aux factures
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>Veuillez corriger les erreurs ci-dessous:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $montantTotal = $facture->montant;
        $montantPaye = $montantTotal - $facture->soldeRestant;
        $pourcentage = $montantTotal > 0 ? min(100, round(($montantPaye / $montantTotal) * 100)) : 0;
    @endphp

    <div class="row g-4 mb-4">
        <!-- Informations Facture & Progression -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center">
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        Informations Générales
                    </h5>
                    
                    <div class="row mb-4">
                        <div class="col-6 mb-3">
                            <span class="d-block text-muted small fw-medium mb-1">Partenaire de Santé</span>
                            <strong class="text-dark">{{ $facture->partenaire->nom ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-6 mb-3">
                            <span class="d-block text-muted small fw-medium mb-1">Montant Total (Part IPM)</span>
                            <strong class="text-dark font-monospace fs-5">{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="col-6">
                            <span class="d-block text-muted small fw-medium mb-1">Déjà Payé</span>
                            <strong class="text-success font-monospace">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="col-6">
                            <span class="d-block text-muted small fw-medium mb-1">Reste à Payer</span>
                            <strong class="text-danger font-monospace">{{ number_format($facture->soldeRestant, 0, ',', ' ') }} FCFA</strong>
                        </div>
                    </div>

                    <!-- Barre de progression -->
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-medium text-muted">Progression du paiement</span>
                            <span class="small fw-bold text-primary">{{ $pourcentage }}%</span>
                        </div>
                        <div class="progress" style="height: 10px; border-radius: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: {{ $pourcentage }}%" aria-valuenow="{{ $pourcentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Formulaire de Paiement -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4 d-flex align-items-center">
                        <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-currency-exchange"></i>
                        </div>
                        Enregistrer un paiement
                    </h5>
                    
                    @if($facture->soldeRestant > 0)
                        <form action="{{ route('factures.paiements.store', $facture->id_facture) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-medium">Montant à payer</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-cash"></i></span>
                                    <input type="number" step="0.01" max="{{ $facture->soldeRestant }}" name="montant" class="form-control border-start-0 ps-0 fw-bold" value="{{ $facture->soldeRestant }}" required>
                                    <span class="input-group-text bg-light text-muted">FCFA</span>
                                </div>
                                <div class="form-text">Maximum autorisé : {{ number_format($facture->soldeRestant, 0, ',', ' ') }} FCFA</div>
                            </div>

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-medium">Mode de paiement</label>
                                    <select name="mode_paiement" class="form-select border-light-subtle bg-light" required>
                                        <option value="virement">Virement Bancaire</option>
                                        <option value="cheque">Chèque</option>
                                        <option value="especes">Espèces</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted small fw-medium">Référence transaction</label>
                                    <input type="text" name="reference_transaction" class="form-control" placeholder="N° Chèque/Virement...">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm py-2 fw-medium">
                                <i class="bi bi-check-circle me-2"></i>Valider le paiement
                            </button>
                        </form>
                    @else
                        <div class="d-flex flex-column align-items-center justify-content-center h-100 py-4 text-center">
                            <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center mb-3" style="width: 80px; height: 80px;">
                                <i class="bi bi-check2-all" style="font-size: 2.5rem;"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Facture Soldée</h5>
                            <p class="text-muted mb-0">Cette facture a été entièrement réglée. Aucun paiement supplémentaire requis.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Historique des paiements -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-clock-history text-info me-2"></i>Historique des paiements
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 small text-uppercase text-secondary fw-semibold">Date</th>
                                    <th class="py-3 small text-uppercase text-secondary fw-semibold">Mode</th>
                                    <th class="py-3 small text-uppercase text-secondary fw-semibold">Réf.</th>
                                    <th class="pe-4 py-3 small text-uppercase text-secondary fw-semibold text-end">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facture->paiementPrestataires as $paiement)
                                    <tr>
                                        <td class="ps-4 py-3">{{ \Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y') }}</td>
                                        <td class="py-3">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                                {{ ucfirst($paiement->mode_paiement) }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-muted small">{{ $paiement->reference_transaction ?? '-' }}</td>
                                        <td class="pe-4 py-3 text-success fw-bold text-end font-monospace">{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-cash-stack fs-2 d-block mb-3 opacity-25"></i>
                                            Aucun paiement enregistré pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Prestations associées -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-4">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-list-check text-secondary me-2"></i>Prestations regroupées
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 small text-uppercase text-secondary fw-semibold">ID</th>
                                    <th class="py-3 small text-uppercase text-secondary fw-semibold">Date</th>
                                    <th class="py-3 small text-uppercase text-secondary fw-semibold text-end">Total</th>
                                    <th class="pe-4 py-3 small text-uppercase text-secondary fw-semibold text-end">Part IPM</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($facture->prestations as $prestation)
                                    <tr>
                                        <td class="ps-4 py-3 text-muted">#{{ $prestation->id_prestation }}</td>
                                        <td class="py-3">{{ \Carbon\Carbon::parse($prestation->date_prestation)->format('d/m/Y') }}</td>
                                        <td class="py-3 text-end text-muted font-monospace">{{ number_format($prestation->montant, 0, ',', ' ') }}</td>
                                        <td class="pe-4 py-3 fw-bold text-end font-monospace">{{ number_format($prestation->montant - $prestation->reste_a_charge, 0, ',', ' ') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-journal-x fs-2 d-block mb-3 opacity-25"></i>
                                            Aucune prestation associée.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0">Factures Impayées</h2>
            <p class="text-muted mb-0">Suivi et gestion des règlements aux prestataires</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('factures.create') }}" class="btn btn-primary shadow-sm rounded-pill px-3">
                <i class="bi bi-plus-lg me-1"></i> Nouvelle Facture
            </a>
            <a href="{{ url('api/paiements/export?format=pdf') }}" target="_blank" class="btn btn-outline-danger shadow-sm rounded-pill px-3">
                <i class="bi bi-file-pdf me-1"></i> PDF
            </a>
            <a href="{{ url('api/paiements/export?format=excel') }}" class="btn btn-outline-success shadow-sm rounded-pill px-3">
                <i class="bi bi-file-excel me-1"></i> Excel
            </a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="row mb-4 g-3">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-circle d-flex justify-content-center align-items-center me-4" style="width: 60px; height: 60px;">
                        <i class="bi bi-wallet2 fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.85rem;">Total Restant Dû</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalDu, 0, ',', ' ') }} <small class="text-muted fs-6">FCFA</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body d-flex align-items-center p-4">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-4" style="width: 60px; height: 60px;">
                        <i class="bi bi-receipt fs-3"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.85rem;">Factures à Traiter</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ $factures->total() }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('factures.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label text-muted small fw-medium">Recherche dynamique</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-light-subtle text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control border-light-subtle bg-light ps-0 dynamic-search-input" placeholder="N° facture, partenaire..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-medium">Partenaire de santé</label>
                    <select name="partenaire" class="form-select border-light-subtle bg-light">
                        <option value="">Tous les partenaires</option>
                        @foreach($partenaires as $part)
                            <option value="{{ $part->value }}" {{ request('partenaire') == $part->value ? 'selected' : '' }}>
                                {{ $part->nom }} ({{ ucfirst($part->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label text-muted small fw-medium">Par page</label>
                    <select name="per_page" id="per_page" class="form-select border-light-subtle bg-light" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 / page</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('factures.index') }}" class="btn btn-light text-secondary w-100 rounded-pill border">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des factures -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">N° Facture</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Date & Heure</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Partenaire de Santé</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Montant</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Reste à Payer</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-center" style="font-size: 0.85rem;">Statut</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $facture)
                        <tr class="clickable-row" data-href="{{ route('factures.show', $facture->id_facture) }}">
                            <td class="px-4 py-3 fw-bold text-dark">{{ $facture->numero_facture }}</td>
                            <td class="px-4 py-3 text-muted text-nowrap">
                                <i class="bi bi-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y à H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 32px; height: 32px;">
                                        <i class="bi bi-building"></i>
                                    </div>
                                    <span class="fw-medium">{{ $facture->partenaire->nom ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-end font-monospace">{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-end font-monospace fw-bold text-danger">{{ number_format($facture->soldeRestant, 0, ',', ' ') }} FCFA</td>
                            <td class="px-4 py-3 text-center">
                                @if($facture->statut_paiement == 'en_attente')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill"><i class="bi bi-clock-history me-1"></i> En attente</span>
                                @elseif($facture->statut_paiement == 'partiellement_payee')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-3 py-2 rounded-pill"><i class="bi bi-pie-chart me-1"></i> Partiel</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2 rounded-pill"><i class="bi bi-check-circle me-1"></i> Soldée</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Actions facture">
                                    <a href="{{ route('factures.show', $facture->id_facture) }}" class="btn btn-outline-primary" title="Gérer le règlement">
                                        <i class="bi bi-gear me-1"></i> Gérer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                Aucune facture impayée trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            Affichage de {{ $factures->firstItem() ?? 0 }} à {{ $factures->lastItem() ?? 0 }} sur {{ $factures->total() }} factures
        </div>
        <div>
            {{ $factures->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

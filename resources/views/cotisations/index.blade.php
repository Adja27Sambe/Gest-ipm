@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Cotisations</h2>
            <p class="text-muted mb-0">Suivi des cotisations des entreprises et des salariés</p>
        </div>
        <a href="{{ route('cotisations.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nouvelle Cotisation
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('cotisations.index') }}" class="row g-3 align-items-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique cotisations (période, entreprise, salarié)..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-center justify-content-end">
                    <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                    <select name="per_page" id="per_page" class="form-select bg-light border-0" style="width: 100px;" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 / page</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Onglets de Navigation -->
    <ul class="nav nav-pills mb-4 gap-2" id="cotisationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill px-4 fw-medium" id="entreprises-tab" data-bs-toggle="tab" data-bs-target="#entreprises" type="button" role="tab" aria-controls="entreprises" aria-selected="true">
                <i class="bi bi-building me-2"></i>Cotisations Entreprises
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill px-4 fw-medium" id="salaries-tab" data-bs-toggle="tab" data-bs-target="#salaries" type="button" role="tab" aria-controls="salaries" aria-selected="false">
                <i class="bi bi-people me-2"></i>Cotisations Salariés
            </button>
        </li>
    </ul>

    <!-- Contenu des Onglets -->
    <div class="tab-content" id="cotisationTabsContent">
        
        <!-- Onglet Entreprises -->
        <div class="tab-pane fade show active" id="entreprises" role="tabpanel" aria-labelledby="entreprises-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Période</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Adhérent (Entreprise)</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Masse Salariale</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-center" style="font-size: 0.85rem;">Taux</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Montant</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-center" style="font-size: 0.85rem;">Statut</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cotisationsEntreprises as $cotisation)
                                <tr class="clickable-row" data-href="{{ route('cotisations.edit', $cotisation->id_cotisation) }}">
                                    <td class="px-4 py-3 fw-bold text-dark">{{ $cotisation->periode }}</td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-building"></i>
                                            </div>
                                            <span class="fw-medium">{{ $cotisation->entreprise->raison_sociale }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end font-monospace">{{ number_format($cotisation->masse_salariale, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-3 text-center"><span class="badge bg-light text-dark border">{{ $cotisation->taux }}%</span></td>
                                    <td class="px-4 py-3 text-end fw-bold text-primary font-monospace">{{ number_format($cotisation->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($cotisation->statut == 'payee')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> Payée
                                            </span>
                                            <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($cotisation->date_paiement)->format('d/m/Y') }}</div>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-x-circle me-1"></i> Impayée
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Actions cotisation">
                                            @if($cotisation->statut == 'impayee')
                                                <form action="{{ route('cotisations.payer', $cotisation->id_cotisation) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer le paiement ?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Marquer comme payée">
                                                        <i class="bi bi-cash-stack"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('cotisations.edit', $cotisation->id_cotisation) }}" class="btn btn-outline-warning border-start-0" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('cotisations.destroy', $cotisation->id_cotisation) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger border-start-0" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                        Aucune cotisation entreprise enregistrée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Affichage de {{ $cotisationsEntreprises->firstItem() ?? 0 }} à {{ $cotisationsEntreprises->lastItem() ?? 0 }} sur {{ $cotisationsEntreprises->total() }} cotisations
                </div>
                <div>
                    {{ $cotisationsEntreprises->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

        <!-- Onglet Salariés -->
        <div class="tab-pane fade" id="salaries" role="tabpanel" aria-labelledby="salaries-tab">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Période</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Participant (Salarié)</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Salaire Base</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-center" style="font-size: 0.85rem;">Taux</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Montant</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-center" style="font-size: 0.85rem;">Statut</th>
                                <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cotisationsSalaries as $cotisation)
                                <tr class="clickable-row" data-href="{{ route('cotisations.edit', $cotisation->id_cotisation) }}">
                                    <td class="px-4 py-3 fw-bold text-dark">{{ $cotisation->periode }}</td>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <div class="fw-medium">{{ $cotisation->salarie->prenom }} {{ $cotisation->salarie->nom }}</div>
                                                <div class="small text-muted">{{ $cotisation->salarie->entreprise->raison_sociale ?? 'Sans entreprise' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-end font-monospace">{{ number_format($cotisation->salaire_base, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-3 text-center"><span class="badge bg-light text-dark border">{{ $cotisation->taux }}%</span></td>
                                    <td class="px-4 py-3 text-end fw-bold text-primary font-monospace">{{ number_format($cotisation->montant, 0, ',', ' ') }} FCFA</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($cotisation->statut == 'payee')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-check-circle me-1"></i> Payée
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle px-3 py-2 rounded-pill">
                                                <i class="bi bi-x-circle me-1"></i> Impayée
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Actions cotisation">
                                            @if($cotisation->statut == 'impayee')
                                                <form action="{{ route('cotisations.payer', $cotisation->id_cotisation) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer le paiement ?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-success" title="Marquer comme payée">
                                                        <i class="bi bi-cash-stack"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('cotisations.edit', $cotisation->id_cotisation) }}" class="btn btn-outline-warning border-start-0" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('cotisations.destroy', $cotisation->id_cotisation) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger border-start-0" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                        Aucune cotisation salarié enregistrée.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Affichage de {{ $cotisationsSalaries->firstItem() ?? 0 }} à {{ $cotisationsSalaries->lastItem() ?? 0 }} sur {{ $cotisationsSalaries->total() }} cotisations
                </div>
                <div>
                    {{ $cotisationsSalaries->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Participants</h1>
        <a href="{{ route('salaries.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-2"></i>Nouveau Salarié
        </a>
    </div>

    <!-- Alertes -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filtres -->
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('salaries.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique (nom, prénom, matricule)..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select bg-light border-0">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="radie" {{ request('statut') == 'radie' ? 'selected' : '' }}>Radié</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                        <select name="per_page" id="per_page" class="form-select bg-light border-0" onchange="this.form.submit()">
                            <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 par page</option>
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-secondary w-100 rounded-3"><i class="bi bi-funnel me-1"></i> Filtrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des Salariés -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">N° Matricule</th>
                        <th>Participant (Salarié)</th>
                        <th>Adhérent (Entreprise)</th>
                        <th>N° Carte Assuré</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($salaries as $salarie)
                        <tr class="clickable-row" data-href="{{ route('salaries.show', $salarie) }}">
                            <td class="ps-4 text-muted fw-bold">{{ $salarie->matricule ?? '-' }}</td>
                            <td class="fw-medium">
                                <div class="d-flex align-items-center">
                                    @if($salarie->photo)
                                        <img src="{{ $salarie->photo->url }}" alt="Photo" class="rounded-circle object-fit-cover me-2 border" style="width: 32px; height: 32px;">
                                    @else
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 border text-secondary" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                    @endif
                                    {{ $salarie->prenom }} {{ $salarie->nom }}
                                </div>
                            </td>
                            <td>{{ $salarie->entreprise->raison_sociale ?? 'N/A' }}</td>
                            <td>
                                @if($salarie->carteAssure)
                                    <span class="badge bg-info text-dark">{{ $salarie->carteAssure->numero_carte }}</span>
                                @else
                                    <span class="badge bg-secondary">Non générée</span>
                                @endif
                            </td>
                            <td>
                                @if($salarie->statut == 'actif')
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">Actif</span>
                                @elseif($salarie->statut == 'suspendu')
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill">Suspendu</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">Radié</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Actions salarié">
                                    <a href="{{ route('salaries.show', $salarie) }}" class="btn btn-outline-primary" title="Dossier salarié">
                                        <i class="bi bi-person-lines-fill"></i>
                                    </a>
                                    @if($salarie->carteAssure)
                                        <a href="{{ route('cartes-assurees.show', $salarie->carteAssure) }}" class="btn btn-outline-info border-start-0" title="Carte Recto-Verso PNG/PDF">
                                            <i class="bi bi-credit-card-2-front"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('salaries.edit', $salarie) }}" class="btn btn-outline-warning border-start-0" title="Modifier">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('salaries.destroy', $salarie) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                                Aucun salarié trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted small">
            Affichage de {{ $salaries->firstItem() ?? 0 }} à {{ $salaries->lastItem() ?? 0 }} sur {{ $salaries->total() }} salariés
        </div>
        <div>
            {{ $salaries->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Gestion des Adhérents</h2>
    <a href="{{ route('entreprises.create') }}" class="btn btn-primary shadow-sm">
        + Nouvelle entreprise
    </a>
</div>

<div class="card mb-4 shadow-sm border-0">
    <div class="card-body bg-light rounded-3 p-3">
        <form action="{{ route('entreprises.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" class="form-control bg-white border-start-0 ps-0 dynamic-search-input" name="search" placeholder="Recherche dynamique (nom, code adhérent)..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select bg-white" name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    <option value="résilié" {{ request('statut') == 'résilié' ? 'selected' : '' }}>Résilié</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center">
                    <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                    <select name="per_page" id="per_page" class="form-select bg-white" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 par page</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 par page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 par page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 par page</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100"><i class="bi bi-funnel me-1"></i> Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Code Adhérent</th>
                        <th>Adhérent (Raison sociale)</th>
                        <th>Contact / Email</th>
                        <th>Effectif (Participants)</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entreprises as $entreprise)
                    <tr class="clickable-row" data-href="{{ route('entreprises.show', $entreprise->id_entreprise) }}">
                        <td class="ps-4 text-muted fw-bold">{{ $entreprise->code_adherent ?? 'N/A' }}</td>
                        <td class="fw-medium">{{ $entreprise->raison_sociale }}</td>
                        <td class="text-muted">{{ $entreprise->email ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary rounded-pill">{{ $entreprise->salaries_count }}</span>
                        </td>
                        <td>
                            @if($entreprise->statut == 'actif')
                                <span class="badge bg-success bg-opacity-10 text-success">Actif</span>
                            @elseif($entreprise->statut == 'suspendu')
                                <span class="badge bg-warning bg-opacity-10 text-warning">Suspendu</span>
                            @elseif($entreprise->statut == 'résilié')
                                <span class="badge bg-danger bg-opacity-10 text-danger">Résilié</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">{{ $entreprise->statut ?? 'Non défini' }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions entreprise">
                                <a href="{{ route('entreprises.show', $entreprise->id_entreprise) }}" class="btn btn-outline-primary" title="Voir les détails">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('entreprises.edit', $entreprise->id_entreprise) }}" class="btn btn-outline-warning border-start-0" title="Éditer">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('entreprises.destroy', $entreprise->id_entreprise) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
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
                            <i class="bi bi-building fs-1 d-block mb-3 opacity-50"></i>
                            Aucune entreprise trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">
        Affichage de {{ $entreprises->firstItem() ?? 0 }} à {{ $entreprises->lastItem() ?? 0 }} sur {{ $entreprises->total() }} entreprises
    </div>
    <div>
        {{ $entreprises->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

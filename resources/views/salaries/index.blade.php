@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Salariés</h1>
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
            <form action="{{ route('salaries.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <input type="text" name="search" class="form-control bg-light border-0" placeholder="Rechercher par nom, prénom ou matricule..." value="{{ request('search') }}">
                </div>
                <div class="col-md-4">
                    <select name="statut" class="form-select bg-light border-0">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                        <option value="radie" {{ request('statut') == 'radie' ? 'selected' : '' }}>Radié</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary w-100 rounded-3">Filtrer</button>
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
                        <th class="ps-4">Matricule</th>
                        <th>Nom Complet</th>
                        <th>Entreprise</th>
                        <th>Carte Assuré</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($salaries as $salarie)
                        <tr>
                            <td class="ps-4 text-muted">{{ $salarie->matricule ?? '-' }}</td>
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
                                <a href="{{ route('salaries.show', $salarie) }}" class="btn btn-sm btn-light text-primary rounded-circle" title="Voir famille">
                                    <i class="bi bi-people"></i>
                                </a>
                                <a href="{{ route('salaries.edit', $salarie) }}" class="btn btn-sm btn-light text-secondary rounded-circle ms-1" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('salaries.destroy', $salarie) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle ms-1" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                                Aucun salarié trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($salaries->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $salaries->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

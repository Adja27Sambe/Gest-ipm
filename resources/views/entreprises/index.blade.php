@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Entreprises adhérentes</h2>
    <a href="{{ route('entreprises.create') }}" class="btn btn-primary shadow-sm">
        + Nouvelle entreprise
    </a>
</div>

<div class="card mb-4">
    <div class="card-body bg-light rounded-3 p-3">
        <form action="{{ route('entreprises.index') }}" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <input type="text" class="form-control bg-white" name="search" placeholder="Rechercher par nom ou code adhérent..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <select class="form-select bg-white" name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                    <option value="suspendu" {{ request('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    <option value="résilié" {{ request('statut') == 'résilié' ? 'selected' : '' }}>Résilié</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-dark w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Raison sociale</th>
                        <th>Email</th>
                        <th>Salariés</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entreprises as $entreprise)
                    <tr>
                        <td class="ps-4 text-muted">{{ $entreprise->code_adherent ?? 'N/A' }}</td>
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
                            <a href="{{ route('entreprises.show', $entreprise->id_entreprise) }}" class="btn btn-sm btn-light text-primary me-1">Voir</a>
                            <a href="{{ route('entreprises.edit', $entreprise->id_entreprise) }}" class="btn btn-sm btn-light text-warning me-1">Éditer</a>
                            <form action="{{ route('entreprises.destroy', $entreprise->id_entreprise) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette entreprise ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            Aucune entreprise trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $entreprises->links('pagination::bootstrap-5') }}
</div>
@endsection

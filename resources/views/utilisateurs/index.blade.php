@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="h3 fw-bold text-dark mb-1">
                <i class="bi bi-people-fill text-primary me-2"></i>Gestion des Utilisateurs
            </h2>
            <p class="text-muted mb-0 small">Gérez les comptes utilisateurs, leurs rôles et leurs accès au système.</p>
        </div>
        <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
            <i class="bi bi-person-plus-fill me-2"></i>Nouvel Utilisateur
        </a>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3 border-0 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Card & Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <form method="GET" action="{{ route('utilisateurs.index') }}" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0 dynamic-search-input" name="search" value="{{ request('search') }}" placeholder="Recherche dynamique (nom, login, email)..." autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="id_role" class="form-select">
                        <option value="">Tous les rôles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id_role }}" {{ request('id_role') == $role->id_role ? 'selected' : '' }}>
                                {{ $role->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="bi bi-funnel-fill me-1"></i>Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'id_role', 'statut']))
                        <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary" title="Réinitialiser">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Utilisateur</th>
                        <th>Identifiant (Login)</th>
                        <th>Email</th>
                        <th>Rôle</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($utilisateurs as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                        {{ strtoupper(substr($user->prenom ?? $user->nom, 0, 1) . substr($user->nom, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $user->prenom }} {{ mb_strtoupper($user->nom) }}</div>
                                        <small class="text-muted">Créé le {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-6">
                                    <i class="bi bi-person me-1 text-secondary"></i>{{ $user->login }}
                                </span>
                            </td>
                            <td>
                                @if($user->email)
                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none text-body">
                                        <i class="bi bi-envelope text-muted me-1"></i>{{ $user->email }}
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1 rounded-pill fw-semibold">
                                    {{ $user->role->libelle ?? 'Aucun rôle' }}
                                </span>
                            </td>
                            <td>
                                @if($user->statut === 'actif')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i>Actif
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1 rounded-pill">
                                        <i class="bi bi-x-circle-fill me-1"></i>Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('utilisateurs.edit', $user) }}" class="btn btn-sm btn-outline-primary" title="Modifier">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    @if($user->id_utilisateur !== auth()->id())
                                        <form action="{{ route('utilisateurs.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-start-0" title="Supprimer">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-secondary border-start-0" disabled title="Vous êtes connecté avec ce compte">
                                            <i class="bi bi-lock-fill"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-6 d-block mb-3 opacity-50"></i>
                                Aucun utilisateur trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($utilisateurs->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $utilisateurs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

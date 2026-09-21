@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- Header & Action -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                    <i class="bi bi-shield-lock-fill me-1"></i>Sécurité & Administration Globale
                </span>
            </div>
            <h2 class="h3 fw-bold text-dark mb-1">Gestion des Rôles & Profils Métiers</h2>
            <p class="text-muted mb-0 small">
                Configurez chaque fonctionnalité comme profil avec des permissions granulaires par module pour l'ensemble des utilisateurs de l'IPM.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary rounded-pill px-3 shadow-sm fw-semibold">
                <i class="bi bi-people me-1"></i>Voir les Utilisateurs
            </a>
            <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#createRoleModal">
                <i class="bi bi-plus-circle-fill me-2"></i>Nouveau Profil Métier
            </button>
        </div>
    </div>

    <!-- Feedback messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                <div>
                    <strong>Succès !</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-octagon-fill fs-4 me-3 text-danger"></i>
                <div>
                    <strong>Attention :</strong> {{ session('error') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- KPIs Top Summary -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-person-badge fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Profils & Rôles Actifs</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_roles'] }} <small class="fs-6 text-muted">profils</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Utilisateurs Assignés</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_utilisateurs'] }} <small class="fs-6 text-muted">comptes</small></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-key-fill fs-3"></i>
                    </div>
                    <div>
                        <div class="text-muted small text-uppercase fw-semibold">Permissions Modulaires</div>
                        <h3 class="fw-bold mb-0 text-dark">{{ $stats['total_permissions'] }} <small class="fs-6 text-muted">droits d'accès</small></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres & Recherche -->
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('roles.index') }}" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" 
                            placeholder="Recherche par nom de rôle, description, fonctionnalité..." 
                            value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="categorie" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">Toutes les catégories de fonctionnalités</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('categorie') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-semibold">
                        <i class="bi bi-funnel me-1"></i>Filtrer
                    </button>
                    @if(request()->hasAny(['search', 'categorie']))
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary rounded-pill" title="Réinitialiser">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Grille des Profils Métiers -->
    <div class="row g-4 mb-4">
        @forelse($roles as $role)
            <div class="col-lg-4 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 role-card transition-hover position-relative bg-white overflow-hidden">
                    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-start">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 mb-2 fw-semibold" style="font-size: 0.75rem;">
                                <i class="bi bi-folder2 me-1"></i>{{ $role->categorie ?? 'Fonctionnalité' }}
                            </span>
                            <h4 class="h5 fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                @if($role->libelle === 'Administrateur')
                                    <i class="bi bi-shield-fill-check text-danger"></i>
                                @else
                                    <i class="bi bi-person-check-fill text-primary"></i>
                                @endif
                                {{ $role->libelle }}
                            </h4>
                        </div>
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold" title="Nombre d'utilisateurs affectés">
                            <i class="bi bi-people me-1 text-primary"></i>{{ $role->utilisateurs->count() }}
                        </span>
                    </div>

                    <div class="card-body px-4 py-2 d-flex flex-column justify-content-between">
                        <div>
                            <p class="text-muted small mb-3">
                                {{ $role->description ?? 'Profil métier dédié à la gestion des opérations associées.' }}
                            </p>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary small fw-semibold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                        Permissions associées ({{ $role->permissions->count() }})
                                    </span>
                                    @if($role->libelle === 'Administrateur')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill small">Passe-droit Total</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($role->permissions->take(4) as $perm)
                                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 fw-normal" style="font-size: 0.75rem;">
                                            <i class="bi bi-check2 text-success me-1"></i>{{ $perm->libelle }}
                                        </span>
                                    @empty
                                        <span class="text-muted fst-italic small">Aucune permission assignée</span>
                                    @endforelse
                                    @if($role->permissions->count() > 4)
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-2 py-1 fw-bold" style="font-size: 0.75rem;">
                                            +{{ $role->permissions->count() - 4 }} autres
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Utilisateurs rattachés aperçu -->
                        <div class="pt-2 border-top mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('utilisateurs.index', ['id_role' => $role->id_role]) }}" class="text-decoration-none small fw-semibold text-primary">
                                    <i class="bi bi-eye me-1"></i>Voir utilisateurs ({{ $role->utilisateurs->count() }})
                                </a>
                                
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" 
                                        data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id_role }}" title="Modifier le profil & permissions">
                                        <i class="bi bi-sliders me-1"></i>Droits
                                    </button>

                                    @if($role->libelle !== 'Administrateur')
                                        <form action="{{ route('roles.destroy', $role->id_role) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer le profil {{ addslashes($role->libelle) }} ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 32px; height: 32px;" title="Supprimer le profil" {{ $role->utilisateurs->count() > 0 ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modale Édition Profil & Permissions -->
            <div class="modal fade" id="editRoleModal{{ $role->id_role }}" tabindex="-1" aria-labelledby="editRoleModalLabel{{ $role->id_role }}" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-xl">
                    <div class="modal-content rounded-4 border-0 shadow-lg">
                        <div class="modal-header border-bottom px-4 py-3 bg-light">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-shield-check fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="modal-title fw-bold mb-0" id="editRoleModalLabel{{ $role->id_role }}">
                                        Configuration du Profil : {{ $role->libelle }}
                                    </h5>
                                    <small class="text-muted">Ajustez les fonctionnalités et permissions accordées aux utilisateurs rattachés à ce profil.</small>
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="{{ route('roles.update', $role->id_role) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-body p-4">
                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <label for="libelle_{{ $role->id_role }}" class="form-label fw-semibold text-dark">Intitulé du profil <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control rounded-3" id="libelle_{{ $role->id_role }}" name="libelle" value="{{ $role->libelle }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="categorie_{{ $role->id_role }}" class="form-label fw-semibold text-dark">Catégorie / Module</label>
                                        <input type="text" class="form-control rounded-3" id="categorie_{{ $role->id_role }}" name="categorie" value="{{ $role->categorie }}" placeholder="Ex: Soins & Demandes">
                                    </div>
                                    <div class="col-12">
                                        <label for="desc_{{ $role->id_role }}" class="form-label fw-semibold text-dark">Description de la mission du profil</label>
                                        <textarea class="form-control rounded-3" id="desc_{{ $role->id_role }}" name="description" rows="2" placeholder="Expliquez brièvement le rôle de ce profil...">{{ $role->description }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        <i class="bi bi-grid-fill text-primary"></i>Matrice des Permissions par Fonctionnalité
                                    </h6>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 check-all-btn" data-target="#modal-perms-{{ $role->id_role }}">
                                            <i class="bi bi-check-all me-1"></i>Tout cocher
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 uncheck-all-btn" data-target="#modal-perms-{{ $role->id_role }}">
                                            <i class="bi bi-x me-1"></i>Tout décocher
                                        </button>
                                    </div>
                                </div>

                                <div id="modal-perms-{{ $role->id_role }}" class="row g-3">
                                    @foreach($permissionsByCategory as $categoryName => $perms)
                                        <div class="col-md-6">
                                            <div class="card border rounded-4 h-100 shadow-none bg-light bg-opacity-50">
                                                <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                                                    <span class="fw-bold text-dark small">
                                                        <i class="bi bi-folder-fill text-primary me-2"></i>{{ $categoryName ?: 'Général' }}
                                                    </span>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small module-check-all" title="Tout cocher dans ce module">
                                                            <small>Cocher</small>
                                                        </button>
                                                        <span class="text-muted mx-1">|</span>
                                                        <button type="button" class="btn btn-link text-secondary p-0 text-decoration-none small module-uncheck-all" title="Tout décocher dans ce module">
                                                            <small>Décocher</small>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body p-3">
                                                    <div class="d-flex flex-column gap-2">
                                                        @foreach($perms as $p)
                                                            <div class="form-check custom-checkbox-card p-2 rounded-3 border bg-white">
                                                                <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" name="permissions[]" 
                                                                    value="{{ $p->id_permission }}" 
                                                                    id="perm_edit_{{ $role->id_role }}_{{ $p->id_permission }}"
                                                                    {{ $role->permissions->contains('id_permission', $p->id_permission) ? 'checked' : '' }}>
                                                                <label class="form-check-label w-100" for="perm_edit_{{ $role->id_role }}_{{ $p->id_permission }}">
                                                                    <span class="d-block fw-semibold text-dark small">{{ $p->libelle }}</span>
                                                                    @if($p->description)
                                                                        <span class="d-block text-muted" style="font-size: 0.72rem; line-height: 1.2;">{{ $p->description }}</span>
                                                                    @endif
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                                    <i class="bi bi-save-fill me-1"></i>Enregistrer les modifications
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                    <i class="bi bi-shield-x text-muted opacity-50 mb-3" style="font-size: 3rem;"></i>
                    <h5 class="fw-bold text-dark">Aucun profil métier trouvé</h5>
                    <p class="text-muted small">Aucun rôle ne correspond à vos critères de recherche.</p>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div class="text-muted small">
            Affichage de {{ $roles->firstItem() ?? 0 }} à {{ $roles->lastItem() ?? 0 }} sur {{ $roles->total() }} profils
        </div>
        <div>
            {{ $roles->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal Création Nouveau Rôle / Profil -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom px-4 py-3 bg-light">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-plus-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="createRoleModalLabel">Créer un Nouveau Profil Métier</h5>
                        <small class="text-muted">Définissez la fonctionnalité et sélectionnez les autorisations associées.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="create_libelle" class="form-label fw-semibold text-dark">Intitulé du profil <span class="text-danger">*</span></label>
                            <input type="text" class="form-control rounded-3" id="create_libelle" name="libelle" placeholder="Ex: Gestionnaire Règlements" required>
                        </div>
                        <div class="col-md-6">
                            <label for="create_categorie" class="form-label fw-semibold text-dark">Catégorie / Module</label>
                            <input type="text" class="form-control rounded-3" id="create_categorie" name="categorie" placeholder="Ex: Finances & Comptabilité">
                        </div>
                        <div class="col-12">
                            <label for="create_desc" class="form-label fw-semibold text-dark">Description de la mission</label>
                            <textarea class="form-control rounded-3" id="create_desc" name="description" rows="2" placeholder="Décrivez les responsabilités de ce profil..."></textarea>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="bi bi-grid-fill text-primary"></i>Permissions à Attribuer par Module
                        </h6>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 check-all-btn" data-target="#create-perms-container">
                                <i class="bi bi-check-all me-1"></i>Tout cocher
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 uncheck-all-btn" data-target="#create-perms-container">
                                <i class="bi bi-x me-1"></i>Tout décocher
                            </button>
                        </div>
                    </div>

                    <div id="create-perms-container" class="row g-3">
                        @foreach($permissionsByCategory as $categoryName => $perms)
                            <div class="col-md-6">
                                <div class="card border rounded-4 h-100 shadow-none bg-light bg-opacity-50">
                                    <div class="card-header bg-white border-bottom py-2 px-3 d-flex justify-content-between align-items-center rounded-top-4">
                                        <span class="fw-bold text-dark small">
                                            <i class="bi bi-folder-fill text-primary me-2"></i>{{ $categoryName ?: 'Général' }}
                                        </span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small module-check-all" title="Tout cocher dans ce module">
                                                <small>Cocher</small>
                                            </button>
                                            <span class="text-muted mx-1">|</span>
                                            <button type="button" class="btn btn-link text-secondary p-0 text-decoration-none small module-uncheck-all" title="Tout décocher dans ce module">
                                                <small>Décocher</small>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="d-flex flex-column gap-2">
                                            @foreach($perms as $p)
                                                <div class="form-check custom-checkbox-card p-2 rounded-3 border bg-white">
                                                    <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" name="permissions[]" 
                                                        value="{{ $p->id_permission }}" 
                                                        id="perm_create_{{ $p->id_permission }}">
                                                    <label class="form-check-label w-100" for="perm_create_{{ $p->id_permission }}">
                                                        <span class="d-block fw-semibold text-dark small">{{ $p->libelle }}</span>
                                                        @if($p->description)
                                                            <span class="d-block text-muted" style="font-size: 0.72rem; line-height: 1.2;">{{ $p->description }}</span>
                                                        @endif
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-top px-4 py-3 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i>Créer le profil métier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .transition-hover {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .transition-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
    }
    .custom-checkbox-card {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .custom-checkbox-card:hover {
        border-color: #0d6efd !important;
        background-color: #f8faff !important;
    }
    .custom-checkbox-card:has(input:checked) {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.04) !important;
    }
</style>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cocher tout le conteneur
        document.querySelectorAll('.check-all-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.querySelector(this.dataset.target);
                if (target) {
                    target.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
                }
            });
        });

        // Décocher tout le conteneur
        document.querySelectorAll('.uncheck-all-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const target = document.querySelector(this.dataset.target);
                if (target) {
                    target.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                }
            });
        });

        // Cocher / Décocher par module individuel
        document.querySelectorAll('.module-check-all').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.card');
                if (card) {
                    card.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
                }
            });
        });

        document.querySelectorAll('.module-uncheck-all').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.card');
                if (card) {
                    card.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
                }
            });
        });
    });
</script>
@endpush
@endsection

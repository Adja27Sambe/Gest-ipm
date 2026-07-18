@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0 text-dark">Gestion des Rôles</h2>
            <p class="text-muted mb-0">Créez et configurez les droits d'accès des utilisateurs</p>
        </div>
        <button type="button" class="btn btn-primary rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-toggle="modal" data-bs-target="#createRoleModal">
            <i class="bi bi-plus-lg me-2"></i>Nouveau Rôle
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm" role="alert">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Libellé du Rôle</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold" style="font-size: 0.85rem;">Permissions</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-center" style="font-size: 0.85rem;">Utilisateurs</th>
                        <th class="py-3 px-4 text-uppercase text-secondary fw-semibold text-end" style="font-size: 0.85rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                        <tr>
                            <td class="px-4 py-3 fw-medium">{{ $role->libelle }}</td>
                            <td class="px-4 py-3">
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($role->permissions as $perm)
                                        <span class="badge bg-soft-primary text-primary border border-primary-subtle rounded-pill fw-normal px-2 py-1">
                                            {{ ucwords(str_replace('_', ' ', $perm->libelle)) }}
                                        </span>
                                    @endforeach
                                    @if($role->permissions->isEmpty())
                                        <span class="text-muted fst-italic small">Aucune permission</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="badge bg-secondary rounded-pill">{{ $role->utilisateurs->count() }}</span>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle me-1" 
                                    data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $role->id_role }}" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                
                                <form action="{{ route('roles.destroy', $role->id_role) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce rôle ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Supprimer" {{ $role->utilisateurs->count() > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editRoleModal{{ $role->id_role }}" tabindex="-1" aria-labelledby="editRoleModalLabel{{ $role->id_role }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold" id="editRoleModalLabel{{ $role->id_role }}">Modifier le Rôle : {{ $role->libelle }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('roles.update', $role->id_role) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-4">
                                                <label for="libelle" class="form-label fw-semibold">Nom du rôle</label>
                                                <input type="text" class="form-control form-control-lg bg-light" id="libelle" name="libelle" value="{{ $role->libelle }}" required>
                                            </div>
                                            
                                            <label class="form-label fw-semibold mb-3">Permissions attribuées</label>
                                            <div class="row g-3">
                                                @foreach($permissions as $permission)
                                                    <div class="col-md-6 col-lg-4">
                                                        <div class="form-check custom-checkbox-card p-3 rounded-3 border h-100">
                                                            <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" name="permissions[]" 
                                                                value="{{ $permission->id_permission }}" 
                                                                id="permEdit{{ $role->id_role }}_{{ $permission->id_permission }}"
                                                                {{ $role->permissions->contains('id_permission', $permission->id_permission) ? 'checked' : '' }}>
                                                            <label class="form-check-label w-100 stretched-link" for="permEdit{{ $role->id_role }}_{{ $permission->id_permission }}">
                                                                {{ ucwords(str_replace('_', ' ', $permission->libelle)) }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Mettre à jour</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-x fs-1 d-block mb-3"></i>
                                Aucun rôle trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createRoleModal" tabindex="-1" aria-labelledby="createRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="createRoleModalLabel">Créer un Nouveau Rôle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-4">
                        <label for="libelle" class="form-label fw-semibold">Nom du rôle</label>
                        <input type="text" class="form-control form-control-lg bg-light" id="libelle" name="libelle" placeholder="Ex: Ressources Humaines" required>
                    </div>
                    
                    <label class="form-label fw-semibold mb-3">Permissions attribuées</label>
                    <div class="row g-3">
                        @foreach($permissions as $permission)
                            <div class="col-md-6 col-lg-4">
                                <div class="form-check custom-checkbox-card p-3 rounded-3 border h-100">
                                    <input class="form-check-input ms-0 mt-1 me-2" type="checkbox" name="permissions[]" 
                                        value="{{ $permission->id_permission }}" 
                                        id="permCreate{{ $permission->id_permission }}">
                                    <label class="form-check-label w-100 stretched-link" for="permCreate{{ $permission->id_permission }}">
                                        {{ ucwords(str_replace('_', ' ', $permission->libelle)) }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Créer le rôle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .bg-soft-primary {
        background-color: rgba(13, 110, 253, 0.1);
    }
    .custom-checkbox-card {
        position: relative;
        transition: all 0.2s ease-in-out;
        background-color: #fff;
    }
    .custom-checkbox-card:hover {
        border-color: #0d6efd !important;
        background-color: #f8faff;
    }
    .custom-checkbox-card:has(input:checked) {
        border-color: #0d6efd !important;
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-bold text-dark mb-1">
                        <i class="bi bi-pencil-square text-primary me-2"></i>Modifier l'Utilisateur
                    </h2>
                    <p class="text-muted mb-0 small">Modifiez le compte de {{ $utilisateur->prenom }} {{ $utilisateur->nom }}.</p>
                </div>
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('utilisateurs.update', $utilisateur) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nom" class="form-label fw-semibold text-dark">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $utilisateur->nom) }}" required>
                                @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label fw-semibold text-dark">Prénom</label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom', $utilisateur->prenom) }}">
                                @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="login" class="form-label fw-semibold text-dark">Identifiant (Login) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login', $utilisateur->login) }}" required>
                                </div>
                                @error('login') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-dark">Adresse Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $utilisateur->email) }}">
                                </div>
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="mot_de_passe" class="form-label fw-semibold text-dark">Changer le mot de passe</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" name="mot_de_passe" minlength="6" placeholder="Laisser vide si inchangé">
                                </div>
                                <div class="form-text text-muted">Laissez vide si vous ne souhaitez pas modifier le mot de passe actuel.</div>
                                @error('mot_de_passe') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="id_role" class="form-label fw-semibold text-dark">Rôle & Profil Métier <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_role') is-invalid @enderror" id="id_role" name="id_role" required onchange="updateRolePreview()">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id_role }}" {{ old('id_role', $utilisateur->id_role) == $role->id_role ? 'selected' : '' }}>
                                            {{ $role->libelle }} ({{ $role->categorie ?? 'Général' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <!-- Aperçu dynamique des permissions du profil sélectionné -->
                        <div id="role_preview_box" class="card border rounded-4 p-3 mb-4 bg-light d-none">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill" id="preview_role_category">Fonctionnalité</span>
                                    <strong class="text-dark" id="preview_role_title">Profil sélectionné</strong>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill" id="preview_perms_count">0 permission(s)</span>
                            </div>
                            <p class="text-muted small mb-2" id="preview_role_desc"></p>
                            <div class="d-flex flex-wrap gap-1" id="preview_role_badges"></div>
                        </div>

                        <div class="mb-4">
                            <label for="statut" class="form-label fw-semibold text-dark">Statut du compte <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 pt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statut" id="statut_actif" value="actif" {{ old('statut', $utilisateur->statut) === 'actif' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium text-success" for="statut_actif">
                                        <i class="bi bi-check-circle-fill me-1"></i>Actif (Accès autorisé)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statut" id="statut_inactif" value="inactif" {{ old('statut', $utilisateur->statut) === 'inactif' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium text-danger" for="statut_inactif">
                                        <i class="bi bi-x-circle-fill me-1"></i>Inactif (Accès bloqué)
                                    </label>
                                </div>
                            </div>
                            @error('statut') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('utilisateurs.index') }}" class="btn btn-light px-4">Annuler</a>
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm fw-semibold">
                                <i class="bi bi-check-lg me-1"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const availableRoles = @json($roles);

    function updateRolePreview() {
        const select = document.getElementById('id_role');
        const previewBox = document.getElementById('role_preview_box');
        const selectedId = parseInt(select.value);

        const role = availableRoles.find(r => r.id_role === selectedId);
        if (!role) {
            previewBox.classList.add('d-none');
            return;
        }

        previewBox.classList.remove('d-none');
        document.getElementById('preview_role_category').textContent = role.categorie || 'Fonctionnalité';
        document.getElementById('preview_role_title').textContent = role.libelle;
        document.getElementById('preview_role_desc').textContent = role.description || 'Aucune description disponible pour ce profil.';
        
        const count = role.permissions ? role.permissions.length : 0;
        document.getElementById('preview_perms_count').textContent = count + ' droit(s) d\'accès';

        const badgesContainer = document.getElementById('preview_role_badges');
        badgesContainer.innerHTML = '';
        if (role.permissions && role.permissions.length > 0) {
            role.permissions.forEach(p => {
                const badge = document.createElement('span');
                badge.className = 'badge bg-white text-dark border rounded-pill px-2 py-1 small fw-normal';
                badge.innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i>' + p.libelle;
                badgesContainer.appendChild(badge);
            });
        } else {
            badgesContainer.innerHTML = '<span class="text-muted small fst-italic">Aucune permission spécifique.</span>';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('id_role').value) {
            updateRolePreview();
        }
    });
</script>
@endpush
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h3 fw-bold text-dark mb-1">
                        <i class="bi bi-person-plus-fill text-primary me-2"></i>Nouvel Utilisateur
                    </h2>
                    <p class="text-muted mb-0 small">Renseignez les identifiants et le rôle du nouvel utilisateur.</p>
                </div>
                <a href="{{ route('utilisateurs.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="bi bi-arrow-left me-1"></i>Retour
                </a>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('utilisateurs.store') }}" method="POST">
                        @csrf

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="nom" class="form-label fw-semibold text-dark">Nom <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom') }}" required placeholder="Ex: Diop">
                                @error('nom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label fw-semibold text-dark">Prénom</label>
                                <input type="text" class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom') }}" placeholder="Ex: Amadou">
                                @error('prenom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="login" class="form-label fw-semibold text-dark">Identifiant (Login) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 @error('login') is-invalid @enderror" id="login" name="login" value="{{ old('login') }}" required placeholder="ex: adiop">
                                </div>
                                @error('login') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-dark">Adresse Email</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="ex: a.diop@ipm.com">
                                </div>
                                @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="mot_de_passe" class="form-label fw-semibold text-dark">Mot de passe <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-key"></i></span>
                                    <input type="password" class="form-control border-start-0 @error('mot_de_passe') is-invalid @enderror" id="mot_de_passe" name="mot_de_passe" required minlength="6" placeholder="Minimum 6 caractères">
                                </div>
                                @error('mot_de_passe') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="id_role" class="form-label fw-semibold text-dark">Rôle & Permissions <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_role') is-invalid @enderror" id="id_role" name="id_role" required>
                                    <option value="" disabled {{ old('id_role') ? '' : 'selected' }}>Sélectionner un rôle</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id_role }}" {{ old('id_role') == $role->id_role ? 'selected' : '' }}>
                                            {{ $role->libelle }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="statut" class="form-label fw-semibold text-dark">Statut du compte <span class="text-danger">*</span></label>
                            <div class="d-flex gap-4 pt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statut" id="statut_actif" value="actif" {{ old('statut', 'actif') === 'actif' ? 'checked' : '' }}>
                                    <label class="form-check-label fw-medium text-success" for="statut_actif">
                                        <i class="bi bi-check-circle-fill me-1"></i>Actif (Accès autorisé)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="statut" id="statut_inactif" value="inactif" {{ old('statut') === 'inactif' ? 'checked' : '' }}>
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
                                <i class="bi bi-check-lg me-1"></i>Créer l'utilisateur
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

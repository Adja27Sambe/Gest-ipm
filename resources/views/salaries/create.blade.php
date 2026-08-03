@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('salaries.index') }}" class="btn btn-link text-decoration-none text-secondary me-3">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h1 class="h3 mb-0 text-gray-800">Nouveau Salarié</h1>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('salaries.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <h5 class="mb-4 text-primary">Informations Professionnelles</h5>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Entreprise *</label>
                        <select name="id_entreprise" class="form-select bg-light border-0 @error('id_entreprise') is-invalid @enderror" required>
                            <option value="">Sélectionnez une entreprise</option>
                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id_entreprise }}" {{ old('id_entreprise') == $entreprise->id_entreprise ? 'selected' : '' }}>
                                    {{ $entreprise->raison_sociale }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_entreprise')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Matricule</label>
                        <input type="text" name="matricule" class="form-control bg-light border-0 @error('matricule') is-invalid @enderror" value="{{ old('matricule') }}" placeholder="Auto-généré après sélection" readonly>
                        @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Date d'embauche</label>
                        <input type="date" name="date_embauche" class="form-control bg-light border-0 @error('date_embauche') is-invalid @enderror" value="{{ old('date_embauche') }}">
                        @error('date_embauche')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Salaire (Optionnel)</label>
                        <input type="number" step="0.01" name="salaire" class="form-control bg-light border-0 @error('salaire') is-invalid @enderror" value="{{ old('salaire') }}">
                        @error('salaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Statut</label>
                        <select name="statut" class="form-select bg-light border-0 @error('statut') is-invalid @enderror">
                            <option value="actif" {{ old('statut', 'actif') == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="suspendu" {{ old('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            <option value="radie" {{ old('statut') == 'radie' ? 'selected' : '' }}>Radié</option>
                        </select>
                        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <h5 class="mb-4 text-primary">Informations Personnelles</h5>

                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Photo de profil</label>
                        <input type="file" name="photo" class="form-control bg-light border-0 @error('photo') is-invalid @enderror" accept="image/*">
                        @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Prénom</label>
                        <input type="text" name="prenom" class="form-control bg-light border-0 @error('prenom') is-invalid @enderror" value="{{ old('prenom') }}">
                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Nom *</label>
                        <input type="text" name="nom" class="form-control bg-light border-0 @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control bg-light border-0 @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance') }}">
                        @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Sexe</label>
                        <select name="sexe" class="form-select bg-light border-0 @error('sexe') is-invalid @enderror">
                            <option value="">Non spécifié</option>
                            <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Téléphone</label>
                        <input type="text" name="telephone" class="form-control bg-light border-0 @error('telephone') is-invalid @enderror" value="{{ old('telephone') }}">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Adresse</label>
                        <textarea name="adresse" rows="2" class="form-control bg-light border-0 @error('adresse') is-invalid @enderror">{{ old('adresse') }}</textarea>
                        @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <div class="d-flex justify-content-end gap-2">
                    <button type="reset" class="btn btn-light rounded-3 px-4">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const entrepriseSelect = document.querySelector('select[name="id_entreprise"]');
        const matriculeInput = document.querySelector('input[name="matricule"]');
        
        if(entrepriseSelect && matriculeInput) {
            entrepriseSelect.addEventListener('change', function() {
                const entrepriseId = this.value;
                if(!entrepriseId) {
                    matriculeInput.value = '';
                    return;
                }
                
                // Show loading state in placeholder
                const originalPlaceholder = matriculeInput.placeholder;
                matriculeInput.placeholder = 'Génération en cours...';
                
                fetch(`/entreprises/${entrepriseId}/next-matricule`)
                    .then(response => response.json())
                    .then(data => {
                        if(data.matricule) {
                            matriculeInput.value = data.matricule;
                        }
                    })
                    .catch(error => console.error('Erreur lors de la récupération du matricule:', error))
                    .finally(() => {
                        matriculeInput.placeholder = originalPlaceholder;
                    });
            });
        }
    });
</script>
@endpush
@endsection

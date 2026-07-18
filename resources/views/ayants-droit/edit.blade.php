@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('salaries.show', $salarie) }}" class="btn btn-link text-decoration-none text-secondary me-3">
            <i class="bi bi-arrow-left"></i> Retour au dossier famille
        </a>
        <h1 class="h3 mb-0 text-gray-800">Modifier Ayant Droit: {{ $ayantDroit->prenom }} {{ $ayantDroit->nom }}</h1>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('ayants-droit.update', $ayantDroit) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <h5 class="mb-4 text-primary">Informations Personnelles</h5>

                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Photo de profil</label>
                        <div class="d-flex align-items-center mb-2">
                            @if($ayantDroit->photo)
                                <img src="{{ $ayantDroit->photo->url }}" alt="Photo" class="rounded-circle object-fit-cover me-3 border" style="width: 60px; height: 60px;">
                            @else
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3 border text-secondary" style="width: 60px; height: 60px;">
                                    <i class="bi bi-person fs-3"></i>
                                </div>
                            @endif
                            <div class="flex-grow-1">
                                <input type="file" name="photo" class="form-control bg-light border-0 @error('photo') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">Laissez vide pour conserver la photo actuelle.</small>
                            </div>
                        </div>
                        @error('photo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Prénom</label>
                        <input type="text" name="prenom" class="form-control bg-light border-0 @error('prenom') is-invalid @enderror" value="{{ old('prenom', $ayantDroit->prenom) }}" required>
                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Nom *</label>
                        <input type="text" name="nom" class="form-control bg-light border-0 @error('nom') is-invalid @enderror" value="{{ old('nom', $ayantDroit->nom) }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Lien de parenté *</label>
                        <select name="lien_parente" class="form-select bg-light border-0 @error('lien_parente') is-invalid @enderror" required>
                            <option value="conjoint" {{ old('lien_parente', $ayantDroit->lien_parente) == 'conjoint' ? 'selected' : '' }}>Conjoint(e)</option>
                            <option value="enfant" {{ old('lien_parente', $ayantDroit->lien_parente) == 'enfant' ? 'selected' : '' }}>Enfant</option>
                        </select>
                        @error('lien_parente')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Sexe</label>
                        <select name="sexe" class="form-select bg-light border-0 @error('sexe') is-invalid @enderror">
                            <option value="">Non spécifié</option>
                            <option value="M" {{ old('sexe', $ayantDroit->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe', $ayantDroit->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control bg-light border-0 @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance', $ayantDroit->date_naissance ? \Carbon\Carbon::parse($ayantDroit->date_naissance)->format('Y-m-d') : '') }}">
                        @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Date de mariage</label>
                        <input type="date" name="date_mariage" class="form-control bg-light border-0 @error('date_mariage') is-invalid @enderror" value="{{ old('date_mariage', $ayantDroit->date_mariage ? \Carbon\Carbon::parse($ayantDroit->date_mariage)->format('Y-m-d') : '') }}">
                        @error('date_mariage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Statut</label>
                        <select name="statut" class="form-select bg-light border-0 @error('statut') is-invalid @enderror">
                            <option value="actif" {{ old('statut', $ayantDroit->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="inactif" {{ old('statut', $ayantDroit->statut) == 'inactif' ? 'selected' : '' }}>Inactif</option>
                        </select>
                        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('salaries.show', $salarie) }}" class="btn btn-light rounded-3 px-4">Annuler</a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>Enregistrer les modifications
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

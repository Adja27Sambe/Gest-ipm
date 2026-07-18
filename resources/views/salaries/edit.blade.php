@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('salaries.index') }}" class="btn btn-link text-decoration-none text-secondary me-3">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h1 class="h3 mb-0 text-gray-800">Modifier Salarié: {{ $salarie->prenom }} {{ $salarie->nom }}</h1>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('salaries.update', $salarie) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <h5 class="mb-4 text-primary">Informations Professionnelles</h5>
                
                <div class="row g-4 mb-5">
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Entreprise *</label>
                        <select name="id_entreprise" class="form-select bg-light border-0 @error('id_entreprise') is-invalid @enderror" required>
                            @foreach($entreprises as $entreprise)
                                <option value="{{ $entreprise->id_entreprise }}" {{ old('id_entreprise', $salarie->id_entreprise) == $entreprise->id_entreprise ? 'selected' : '' }}>
                                    {{ $entreprise->raison_sociale }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_entreprise')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Matricule</label>
                        <input type="text" name="matricule" class="form-control bg-light border-0 @error('matricule') is-invalid @enderror" value="{{ old('matricule', $salarie->matricule) }}">
                        @error('matricule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Date d'embauche</label>
                        <input type="date" name="date_embauche" class="form-control bg-light border-0 @error('date_embauche') is-invalid @enderror" value="{{ old('date_embauche', $salarie->date_embauche ? \Carbon\Carbon::parse($salarie->date_embauche)->format('Y-m-d') : '') }}">
                        @error('date_embauche')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Salaire (Optionnel)</label>
                        <input type="number" step="0.01" name="salaire" class="form-control bg-light border-0 @error('salaire') is-invalid @enderror" value="{{ old('salaire', $salarie->salaire) }}">
                        @error('salaire')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-medium">Statut</label>
                        <select name="statut" class="form-select bg-light border-0 @error('statut') is-invalid @enderror">
                            <option value="actif" {{ old('statut', $salarie->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                            <option value="suspendu" {{ old('statut', $salarie->statut) == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                            <option value="radie" {{ old('statut', $salarie->statut) == 'radie' ? 'selected' : '' }}>Radié</option>
                        </select>
                        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($salarie->statut != 'radie')
                            <div class="form-text text-warning"><i class="bi bi-info-circle me-1"></i>Passer au statut "Radié" désactivera tous les ayants droit.</div>
                        @endif
                    </div>
                </div>

                <h5 class="mb-4 text-primary">Informations Personnelles</h5>

                <div class="row g-4 mb-4">
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Photo de profil</label>
                        <div class="d-flex align-items-center mb-2">
                            @if($salarie->photo)
                                <img src="{{ $salarie->photo->url }}" alt="Photo" class="rounded-circle object-fit-cover me-3 border" style="width: 60px; height: 60px;">
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
                        <input type="text" name="prenom" class="form-control bg-light border-0 @error('prenom') is-invalid @enderror" value="{{ old('prenom', $salarie->prenom) }}">
                        @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Nom *</label>
                        <input type="text" name="nom" class="form-control bg-light border-0 @error('nom') is-invalid @enderror" value="{{ old('nom', $salarie->nom) }}" required>
                        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Date de naissance</label>
                        <input type="date" name="date_naissance" class="form-control bg-light border-0 @error('date_naissance') is-invalid @enderror" value="{{ old('date_naissance', $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->format('Y-m-d') : '') }}">
                        @error('date_naissance')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Sexe</label>
                        <select name="sexe" class="form-select bg-light border-0 @error('sexe') is-invalid @enderror">
                            <option value="">Non spécifié</option>
                            <option value="M" {{ old('sexe', $salarie->sexe) == 'M' ? 'selected' : '' }}>Masculin</option>
                            <option value="F" {{ old('sexe', $salarie->sexe) == 'F' ? 'selected' : '' }}>Féminin</option>
                        </select>
                        @error('sexe')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Téléphone</label>
                        <input type="text" name="telephone" class="form-control bg-light border-0 @error('telephone') is-invalid @enderror" value="{{ old('telephone', $salarie->telephone) }}">
                        @error('telephone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    
                    <div class="col-md-12">
                        <label class="form-label fw-medium">Adresse</label>
                        <textarea name="adresse" rows="2" class="form-control bg-light border-0 @error('adresse') is-invalid @enderror">{{ old('adresse', $salarie->adresse) }}</textarea>
                        @error('adresse')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('salaries.index') }}" class="btn btn-light rounded-3 px-4">Annuler</a>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

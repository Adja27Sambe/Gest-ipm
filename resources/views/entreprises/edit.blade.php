@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-dark mb-0">Éditer : {{ $entreprise->raison_sociale }}</h2>
            <a href="{{ route('entreprises.index') }}" class="btn btn-light text-muted">
                Retour
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-5">
                <form action="{{ route('entreprises.update', $entreprise->id_entreprise) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="code_adherent" class="form-label fw-medium text-muted">Code adhérent</label>
                            <input type="text" class="form-control @error('code_adherent') is-invalid @enderror" id="code_adherent" name="code_adherent" value="{{ old('code_adherent', $entreprise->code_adherent) }}" placeholder="Optionnel">
                            @error('code_adherent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code_comptable" class="form-label fw-medium text-muted">Code comptable</label>
                            <input type="text" class="form-control @error('code_comptable') is-invalid @enderror" id="code_comptable" name="code_comptable" value="{{ old('code_comptable', $entreprise->code_comptable) }}" placeholder="Optionnel">
                            @error('code_comptable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="raison_sociale" class="form-label fw-medium text-muted">Raison sociale *</label>
                        <input type="text" class="form-control form-control-lg @error('raison_sociale') is-invalid @enderror" id="raison_sociale" name="raison_sociale" value="{{ old('raison_sociale', $entreprise->raison_sociale) }}" required>
                        @error('raison_sociale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="adresse" class="form-label fw-medium text-muted">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2">{{ old('adresse', $entreprise->adresse) }}</textarea>
                        @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="telephone" class="form-label fw-medium text-muted">Téléphone</label>
                            <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone', $entreprise->telephone) }}">
                            @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium text-muted">Email de contact</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $entreprise->email) }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="date_adhesion" class="form-label fw-medium text-muted">Date d'adhésion</label>
                            <input type="date" class="form-control @error('date_adhesion') is-invalid @enderror" id="date_adhesion" name="date_adhesion" value="{{ old('date_adhesion', $entreprise->date_adhesion) }}">
                            @error('date_adhesion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="statut" class="form-label fw-medium text-muted">Statut</label>
                            <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut">
                                <option value="actif" {{ old('statut', $entreprise->statut) == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="suspendu" {{ old('statut', $entreprise->statut) == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                <option value="résilié" {{ old('statut', $entreprise->statut) == 'résilié' ? 'selected' : '' }}>Résilié</option>
                            </select>
                            @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4 text-muted">

                    <div class="d-grid">
                        <button type="submit" class="btn btn-warning btn-lg">Enregistrer les modifications</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

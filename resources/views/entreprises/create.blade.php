@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-dark mb-0">Nouvelle Entreprise</h2>
            <a href="{{ route('entreprises.index') }}" class="btn btn-light text-muted">
                Retour
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-5">
                <form action="{{ route('entreprises.store') }}" method="POST">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="code_adherent" class="form-label fw-medium text-muted">Code adhérent <span class="badge bg-light text-primary border ms-1">Auto</span></label>
                            <input type="text" class="form-control @error('code_adherent') is-invalid @enderror" id="code_adherent" name="code_adherent" value="{{ old('code_adherent', \App\Observers\EntrepriseObserver::generateCodeAdherent()) }}" placeholder="Généré automatiquement (ex: ADH001)">
                            <div class="form-text text-muted">Généré automatiquement (ex: ADH001), vous pouvez le personnaliser si nécessaire.</div>
                            @error('code_adherent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="code_comptable" class="form-label fw-medium text-muted">Code comptable</label>
                            <input type="text" class="form-control @error('code_comptable') is-invalid @enderror" id="code_comptable" name="code_comptable" value="{{ old('code_comptable') }}" placeholder="Optionnel">
                            @error('code_comptable') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="raison_sociale" class="form-label fw-medium text-muted">Raison sociale *</label>
                        <input type="text" class="form-control form-control-lg @error('raison_sociale') is-invalid @enderror" id="raison_sociale" name="raison_sociale" value="{{ old('raison_sociale') }}" required>
                        @error('raison_sociale') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="adresse" class="form-label fw-medium text-muted">Adresse</label>
                        <textarea class="form-control @error('adresse') is-invalid @enderror" id="adresse" name="adresse" rows="2">{{ old('adresse') }}</textarea>
                        @error('adresse') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="telephone" class="form-label fw-medium text-muted">Téléphone</label>
                            <input type="text" class="form-control @error('telephone') is-invalid @enderror" id="telephone" name="telephone" value="{{ old('telephone') }}">
                            @error('telephone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label fw-medium text-muted">Email de contact</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="date_adhesion" class="form-label fw-medium text-muted">Date d'adhésion</label>
                            <input type="date" class="form-control @error('date_adhesion') is-invalid @enderror" id="date_adhesion" name="date_adhesion" value="{{ old('date_adhesion', date('Y-m-d')) }}">
                            @error('date_adhesion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="statut" class="form-label fw-medium text-muted">Statut</label>
                            <select class="form-select @error('statut') is-invalid @enderror" id="statut" name="statut">
                                <option value="actif" {{ old('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                                <option value="suspendu" {{ old('statut') == 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                <option value="résilié" {{ old('statut') == 'résilié' ? 'selected' : '' }}>Résilié</option>
                            </select>
                            @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="my-4 text-muted">

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Ajouter l'entreprise</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

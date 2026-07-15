@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-dark mb-0">Nouvelle Demande</h2>
            <a href="{{ route('demandes.index') }}" class="btn btn-light text-muted">
                Retour
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-5">
                <form action="{{ route('demandes.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="id_salarie" class="form-label fw-medium text-muted">ID Salarié *</label>
                        <input type="number" class="form-control form-control-lg @error('id_salarie') is-invalid @enderror" id="id_salarie" name="id_salarie" value="{{ old('id_salarie') }}" placeholder="Ex: 1" required>
                        @error('id_salarie')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="id_type_demande" class="form-label fw-medium text-muted">Type de demande *</label>
                        <input type="number" class="form-control form-control-lg @error('id_type_demande') is-invalid @enderror" id="id_type_demande" name="id_type_demande" value="{{ old('id_type_demande') }}" placeholder="Ex: 1" required>
                        @error('id_type_demande')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="motif" class="form-label fw-medium text-muted">Motif de la consultation *</label>
                        <textarea class="form-control @error('motif') is-invalid @enderror" id="motif" name="motif" rows="4" placeholder="Décrivez le motif médical..." required>{{ old('motif') }}</textarea>
                        @error('motif')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="auto_approuver" name="auto_approuver" value="1" {{ old('auto_approuver') ? 'checked' : '' }}>
                        <label class="form-check-label text-muted" for="auto_approuver">
                            Approuver automatiquement et générer le bon de commande
                        </label>
                    </div>

                    <hr class="my-4 text-muted">

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">Soumettre la demande</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

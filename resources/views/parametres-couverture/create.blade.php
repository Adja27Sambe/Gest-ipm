@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('parametres-couverture.index') }}" class="btn btn-light rounded-circle shadow-sm border me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left text-primary"></i>
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Nouveau paramètre</h3>
                <p class="text-muted small mb-0">Configurer une nouvelle couverture</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('parametres-couverture.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Type de Prestation <span class="text-danger">*</span></label>
                        <select name="id_type_prestation" class="form-select form-select-lg bg-light border-0 shadow-none @error('id_type_prestation') is-invalid @enderror" required>
                            <option value="">Sélectionnez un type...</option>
                            @foreach($typesPrestation as $type)
                                <option value="{{ $type->id_type_prestation }}" {{ old('id_type_prestation') == $type->id_type_prestation ? 'selected' : '' }}>
                                    {{ $type->libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_prestation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Seuls les types sans paramètres configurés sont affichés.</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Taux de Prise en Charge <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" name="taux_prise_charge" class="form-control bg-transparent border-0 shadow-none @error('taux_prise_charge') is-invalid @enderror" value="{{ old('taux_prise_charge', 80) }}" min="0" max="100" required>
                            <span class="input-group-text bg-transparent border-0 fw-bold text-muted">%</span>
                        </div>
                        @error('taux_prise_charge')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Plafond par Acte</label>
                            <div class="input-group input-group-lg bg-light rounded-3">
                                <input type="number" name="plafond_par_acte" class="form-control bg-transparent border-0 shadow-none @error('plafond_par_acte') is-invalid @enderror" value="{{ old('plafond_par_acte') }}" min="0">
                                <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                            </div>
                            <div class="form-text">Laissez vide si illimité</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Plafond Annuel</label>
                            <div class="input-group input-group-lg bg-light rounded-3">
                                <input type="number" name="plafond_annuel" class="form-control bg-transparent border-0 shadow-none @error('plafond_annuel') is-invalid @enderror" value="{{ old('plafond_annuel') }}" min="0">
                                <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                            </div>
                            <div class="form-text">Laissez vide si illimité</div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-muted small fw-bold text-uppercase">Ticket Modérateur</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" name="ticket_moderateur" class="form-control bg-transparent border-0 shadow-none @error('ticket_moderateur') is-invalid @enderror" value="{{ old('ticket_moderateur') }}" min="0" max="100">
                            <span class="input-group-text bg-transparent border-0 fw-bold text-muted">%</span>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="bi bi-check2 me-2"></i> Enregistrer le paramètre
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

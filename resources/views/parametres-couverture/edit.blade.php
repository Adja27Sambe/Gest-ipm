@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('parametres-couverture.index') }}" class="btn btn-light rounded-circle shadow-sm border me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                <i class="bi bi-arrow-left text-primary"></i>
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Modifier le paramètre</h3>
                <p class="text-muted small mb-0">{{ $parametres_couverture->typePrestation->libelle ?? 'Type de prestation inconnu' }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('parametres-couverture.update', $parametres_couverture->id_parametre) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Type de Prestation</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 shadow-none text-muted" value="{{ $parametres_couverture->typePrestation->libelle ?? 'N/A' }}" disabled>
                        <div class="form-text">Le type de prestation ne peut pas être modifié une fois créé.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Taux de Prise en Charge <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" name="taux_prise_charge" class="form-control bg-transparent border-0 shadow-none @error('taux_prise_charge') is-invalid @enderror" value="{{ old('taux_prise_charge', $parametres_couverture->taux_prise_charge) }}" min="0" max="100" required>
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
                                <input type="number" name="plafond_par_acte" class="form-control bg-transparent border-0 shadow-none @error('plafond_par_acte') is-invalid @enderror" value="{{ old('plafond_par_acte', $parametres_couverture->plafond_par_acte) }}" min="0">
                                <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                            </div>
                            <div class="form-text">Laissez vide si illimité</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-bold text-uppercase">Plafond Annuel</label>
                            <div class="input-group input-group-lg bg-light rounded-3">
                                <input type="number" name="plafond_annuel" class="form-control bg-transparent border-0 shadow-none @error('plafond_annuel') is-invalid @enderror" value="{{ old('plafond_annuel', $parametres_couverture->plafond_annuel) }}" min="0">
                                <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                            </div>
                            <div class="form-text">Laissez vide si illimité</div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label text-muted small fw-bold text-uppercase">Ticket Modérateur</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" name="ticket_moderateur" class="form-control bg-transparent border-0 shadow-none @error('ticket_moderateur') is-invalid @enderror" value="{{ old('ticket_moderateur', $parametres_couverture->ticket_moderateur) }}" min="0" max="100">
                            <span class="input-group-text bg-transparent border-0 fw-bold text-muted">%</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i> Mettre à jour
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

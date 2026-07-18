@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('cotisations.index') }}" class="btn btn-link text-decoration-none text-secondary me-3">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <h2 class="fw-bold mb-0 text-dark">Modifier la Cotisation</h2>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('cotisations.update', $cotisation->id_cotisation) }}" method="POST">
                @csrf
                @method('PUT')
                
                <h5 class="mb-4 text-primary">
                    Cotisation : 
                    @if($cotisation->id_entreprise)
                        Part Entreprise ({{ $cotisation->entreprise->raison_sociale }})
                    @else
                        Part Salarié ({{ $cotisation->salarie->prenom }} {{ $cotisation->salarie->nom }})
                    @endif
                </h5>
                
                <div class="row g-4 mb-4">

                    @if($cotisation->id_entreprise)
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Masse Salariale *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="masse_salariale" class="form-control bg-light border-0" value="{{ old('masse_salariale', $cotisation->masse_salariale) }}" required>
                                <span class="input-group-text bg-light border-0">FCFA</span>
                            </div>
                            @error('masse_salariale')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    @else
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Salaire de Base *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="salaire_base" class="form-control bg-light border-0" value="{{ old('salaire_base', $cotisation->salaire_base) }}" required>
                                <span class="input-group-text bg-light border-0">FCFA</span>
                            </div>
                            @error('salaire_base')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>
                    @endif

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Période *</label>
                        <input type="text" name="periode" class="form-control bg-light border-0" value="{{ old('periode', $cotisation->periode) }}" required>
                        @error('periode')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Taux de cotisation (%) *</label>
                        <input type="number" step="0.01" name="taux" class="form-control bg-light border-0" value="{{ old('taux', $cotisation->taux) }}" required>
                        @error('taux')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-medium">Statut du paiement *</label>
                        <select name="statut" id="statut" class="form-select bg-light border-0" required>
                            <option value="impayee" {{ old('statut', $cotisation->statut) == 'impayee' ? 'selected' : '' }}>Impayée</option>
                            <option value="payee" {{ old('statut', $cotisation->statut) == 'payee' ? 'selected' : '' }}>Payée</option>
                        </select>
                        @error('statut')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6 section-date" style="display: {{ old('statut', $cotisation->statut) == 'payee' ? 'block' : 'none' }};">
                        <label class="form-label fw-medium">Date de paiement</label>
                        <input type="date" name="date_paiement" class="form-control bg-light border-0" value="{{ old('date_paiement', $cotisation->date_paiement ? \Carbon\Carbon::parse($cotisation->date_paiement)->format('Y-m-d') : date('Y-m-d')) }}">
                        @error('date_paiement')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('cotisations.index') }}" class="btn btn-light rounded-pill px-4">Annuler</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i>Mettre à jour
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statutSelect = document.getElementById('statut');
        const sectionDate = document.querySelector('.section-date');

        statutSelect.addEventListener('change', function() {
            if (this.value === 'payee') {
                sectionDate.style.display = 'block';
            } else {
                sectionDate.style.display = 'none';
            }
        });
    });
</script>
@endsection

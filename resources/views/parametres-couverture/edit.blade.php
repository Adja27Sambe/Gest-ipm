@extends('layouts.app')

@section('title', 'Modifier le Paramètre de Couverture')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-9 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('parametres-couverture.index') }}" class="btn btn-light rounded-circle shadow-sm border me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-arrow-left text-primary"></i>
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Modifier la règle de couverture</h3>
                <p class="text-muted small mb-0">{{ $parametres_couverture->typePrestation->libelle ?? 'Acte médical' }}</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('parametres-couverture.update', $parametres_couverture->id_parametre) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Acte Médical (Read-only) -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Acte Médical</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 shadow-none text-dark fw-bold" value="{{ $parametres_couverture->typePrestation->libelle ?? 'N/A' }}" disabled>
                        <div class="form-text">L'acte médical associé ne peut pas être modifié.</div>
                    </div>

                    <!-- Mode de Calcul -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Mode de Calcul de la Couverture <span class="text-danger">*</span></label>
                        <select name="type_calcul" id="type_calcul_select" class="form-select form-select-lg bg-light border-0 shadow-none @error('type_calcul') is-invalid @enderror" required onchange="handleTypeCalculChange(this.value)">
                            @php $currentMode = old('type_calcul', $parametres_couverture->type_calcul ?? 'POURCENTAGE'); @endphp
                            <option value="POURCENTAGE" {{ $currentMode === 'POURCENTAGE' ? 'selected' : '' }}>Pourcentage standard (%)</option>
                            <option value="MONTANT_REFERENTIEL" {{ $currentMode === 'MONTANT_REFERENTIEL' ? 'selected' : '' }}>Montant Référentiel (FCFA)</option>
                            <option value="FORFAIT" {{ $currentMode === 'FORFAIT' ? 'selected' : '' }}>Forfait fixe (ex: Maternité 50 000 FCFA)</option>
                            <option value="TARIF_JOURNALIER" {{ $currentMode === 'TARIF_JOURNALIER' ? 'selected' : '' }}>Tarif journalier (ex: Chambre Hôpital 20 000 FCFA/jour)</option>
                            <option value="PLAFOND" {{ $currentMode === 'PLAFOND' ? 'selected' : '' }}>Plafond spécifique par acte (ex: Optique 30 000 FCFA)</option>
                            <option value="EXCLUSION" {{ $currentMode === 'EXCLUSION' ? 'selected' : '' }}>Exclusion de prise en charge (0% IPM)</option>
                            <option value="100_PERCENT_PARTICIPANT" {{ $currentMode === '100_PERCENT_PARTICIPANT' ? 'selected' : '' }}>Participant 100% (0% IPM, 100% Participant)</option>
                            <option value="SOUS_COUVERT" {{ $currentMode === 'SOUS_COUVERT' ? 'selected' : '' }}>Prise en charge Sous Couvert (100% IPM, 0% Participant)</option>
                        </select>
                        @error('type_calcul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Taux de Prise en Charge (Facultatif) -->
                    <div class="mb-4" id="group_taux">
                        <label class="form-label text-muted small fw-bold text-uppercase">
                            Taux de Prise en Charge <small class="text-muted fw-normal">(Optionnel)</small>
                        </label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="0.01" name="taux_prise_charge" id="taux_input" class="form-control bg-transparent border-0 shadow-none @error('taux_prise_charge') is-invalid @enderror" value="{{ old('taux_prise_charge', $parametres_couverture->taux_prise_charge) }}" min="0" max="100">
                            <span class="input-group-text bg-transparent border-0 fw-bold text-muted">%</span>
                        </div>
                        <div class="form-text">Facultatif si l'acte fonctionne au forfait, montant référentiel ou tarif journalier.</div>
                        @error('taux_prise_charge')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Montant Référentiel -->
                    <div class="mb-4" id="group_montant_referentiel">
                        <label class="form-label text-muted small fw-bold text-uppercase">
                            Montant Référentiel (FCFA) <small class="text-muted fw-normal">(Non obligatoire)</small>
                        </label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="montant_referentiel" class="form-control bg-transparent border-0 shadow-none @error('montant_referentiel') is-invalid @enderror" value="{{ old('montant_referentiel', $parametres_couverture->montant_referentiel ?? $parametres_couverture->ticket_moderateur) }}" min="0" placeholder="Ex: 15000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                        </div>
                        <div class="form-text">Base maximale prise en charge ou tarif de référence pour cet acte.</div>
                    </div>

                    <!-- Forfait Métier Spécifique -->
                    <div class="mb-4" id="group_forfait" style="display: none;">
                        <label class="form-label text-muted small fw-bold text-uppercase">Montant du Forfait Fixe (FCFA)</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="montant_forfait" class="form-control bg-transparent border-0 shadow-none @error('montant_forfait') is-invalid @enderror" value="{{ old('montant_forfait', $parametres_couverture->montant_forfait) }}" min="0" placeholder="Ex: 50000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                        </div>
                    </div>

                    <!-- Tarif Journalier -->
                    <div class="mb-4" id="group_tarif_journalier" style="display: none;">
                        <label class="form-label text-muted small fw-bold text-uppercase">Tarif Journalier de Référence (FCFA/jour)</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="tarif_journalier" class="form-control bg-transparent border-0 shadow-none @error('tarif_journalier') is-invalid @enderror" value="{{ old('tarif_journalier', $parametres_couverture->tarif_journalier) }}" min="0" placeholder="Ex: 20000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA/jour</span>
                        </div>
                    </div>

                    <!-- Plafond Spécifique -->
                    <div class="mb-4" id="group_montant_plafond" style="display: none;">
                        <label class="form-label text-muted small fw-bold text-uppercase">Plafond Spécifique de l'Acte (FCFA)</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="montant_plafond" class="form-control bg-transparent border-0 shadow-none @error('montant_plafond') is-invalid @enderror" value="{{ old('montant_plafond', $parametres_couverture->montant_plafond) }}" min="0" placeholder="Ex: 30000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                        </div>
                    </div>

                    <!-- Conditions Particulières -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Conditions Particulières / Règles Métier</label>
                        <textarea name="conditions_particulieres" class="form-control bg-light border-0 shadow-none" rows="2" placeholder="Précisions de prise en charge...">{{ old('conditions_particulieres', $parametres_couverture->conditions_particulieres) }}</textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="bi bi-save me-2"></i> Mettre à jour la règle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function handleTypeCalculChange(mode) {
    const groupTaux = document.getElementById('group_taux');
    const groupRef = document.getElementById('group_montant_referentiel');
    const groupForfait = document.getElementById('group_forfait');
    const groupTarif = document.getElementById('group_tarif_journalier');
    const groupPlafond = document.getElementById('group_montant_plafond');

    groupForfait.style.display = (mode === 'FORFAIT') ? 'block' : 'none';
    groupTarif.style.display = (mode === 'TARIF_JOURNALIER') ? 'block' : 'none';
    groupPlafond.style.display = (mode === 'PLAFOND') ? 'block' : 'none';
    groupRef.style.display = (mode === 'MONTANT_REFERENTIEL') ? 'block' : 'none';

    if (mode === 'EXCLUSION' || mode === '100_PERCENT_PARTICIPANT' || mode === 'SOUS_COUVERT') {
        groupTaux.style.display = 'none';
    } else {
        groupTaux.style.display = 'block';
    }
}

document.addEventListener("DOMContentLoaded", function() {
    handleTypeCalculChange(document.getElementById('type_calcul_select').value);
});
</script>
@endsection

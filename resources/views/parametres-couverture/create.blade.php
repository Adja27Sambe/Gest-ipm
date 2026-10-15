@extends('layouts.app')

@section('title', 'Nouveau Paramètre de Couverture')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-md-9 col-lg-8">
        <div class="d-flex align-items-center mb-4">
            <a href="{{ route('parametres-couverture.index') }}" class="btn btn-light rounded-circle shadow-sm border me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-arrow-left text-primary"></i>
            </a>
            <div>
                <h3 class="fw-bold mb-0 text-dark">Nouveau paramètre de couverture</h3>
                <p class="text-muted small mb-0">Définir les règles de prise en charge pour un acte médical</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('parametres-couverture.store') }}" method="POST">
                    @csrf
                    
                    <!-- Acte Médical -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Acte Médical <span class="text-danger">*</span></label>
                        <select name="id_type_prestation" class="form-select form-select-lg bg-light border-0 shadow-none @error('id_type_prestation') is-invalid @enderror" required>
                            <option value="">Sélectionnez un acte médical...</option>
                            @foreach($typesPrestation as $type)
                                <option value="{{ $type->id_type_prestation }}" {{ old('id_type_prestation') == $type->id_type_prestation ? 'selected' : '' }}>
                                    {{ $type->libelle }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_type_prestation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Seuls les actes sans règle de couverture configurée sont proposés.</div>
                        @enderror
                    </div>

                    <!-- Mode de Calcul -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Mode de Calcul de la Couverture <span class="text-danger">*</span></label>
                        <select name="type_calcul" id="type_calcul_select" class="form-select form-select-lg bg-light border-0 shadow-none @error('type_calcul') is-invalid @enderror" required onchange="handleTypeCalculChange(this.value)">
                            <option value="POURCENTAGE" {{ old('type_calcul', 'POURCENTAGE') === 'POURCENTAGE' ? 'selected' : '' }}>Pourcentage standard (%)</option>
                            <option value="MONTANT_REFERENTIEL" {{ old('type_calcul') === 'MONTANT_REFERENTIEL' ? 'selected' : '' }}>Montant Référentiel (FCFA)</option>
                            <option value="FORFAIT" {{ old('type_calcul') === 'FORFAIT' ? 'selected' : '' }}>Forfait fixe (ex: Maternité forfait 50 000 FCFA)</option>
                            <option value="TARIF_JOURNALIER" {{ old('type_calcul') === 'TARIF_JOURNALIER' ? 'selected' : '' }}>Tarif journalier (ex: Chambre Hôpital 20 000 FCFA/jour)</option>
                            <option value="PLAFOND" {{ old('type_calcul') === 'PLAFOND' ? 'selected' : '' }}>Plafond spécifique par acte (ex: Optique 30 000 FCFA)</option>
                            <option value="EXCLUSION" {{ old('type_calcul') === 'EXCLUSION' ? 'selected' : '' }}>Exclusion de prise en charge (0% IPM)</option>
                            <option value="100_PERCENT_PARTICIPANT" {{ old('type_calcul') === '100_PERCENT_PARTICIPANT' ? 'selected' : '' }}>Participant 100% (0% IPM, 100% Participant)</option>
                            <option value="SOUS_COUVERT" {{ old('type_calcul') === 'SOUS_COUVERT' ? 'selected' : '' }}>Prise en charge Sous Couvert (100% IPM, 0% Participant)</option>
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
                            <input type="number" step="0.01" name="taux_prise_charge" id="taux_input" class="form-control bg-transparent border-0 shadow-none @error('taux_prise_charge') is-invalid @enderror" value="{{ old('taux_prise_charge', 80) }}" min="0" max="100">
                            <span class="input-group-text bg-transparent border-0 fw-bold text-muted">%</span>
                        </div>
                        <div class="form-text">Laissez vide si l'acte fonctionne exclusivement par montant référentiel, forfait ou tarif journalier.</div>
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
                            <input type="number" step="1" name="montant_referentiel" class="form-control bg-transparent border-0 shadow-none @error('montant_referentiel') is-invalid @enderror" value="{{ old('montant_referentiel') }}" min="0" placeholder="Ex: 15000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                        </div>
                        <div class="form-text">Base maximale prise en charge ou tarif de référence pour cet acte.</div>
                    </div>

                    <!-- Forfait Métier Spécifique -->
                    <div class="mb-4" id="group_forfait" style="display: none;">
                        <label class="form-label text-muted small fw-bold text-uppercase">Montant du Forfait Fixe (FCFA)</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="montant_forfait" class="form-control bg-transparent border-0 shadow-none @error('montant_forfait') is-invalid @enderror" value="{{ old('montant_forfait') }}" min="0" placeholder="Ex: 50000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                        </div>
                        <div class="form-text">Ex: Maternité normale forfaitaire 50 000 FCFA.</div>
                    </div>

                    <!-- Tarif Journalier -->
                    <div class="mb-4" id="group_tarif_journalier" style="display: none;">
                        <label class="form-label text-muted small fw-bold text-uppercase">Tarif Journalier de Référence (FCFA/jour)</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="tarif_journalier" class="form-control bg-transparent border-0 shadow-none @error('tarif_journalier') is-invalid @enderror" value="{{ old('tarif_journalier') }}" min="0" placeholder="Ex: 20000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA/jour</span>
                        </div>
                        <div class="form-text">Ex: Chambre d'hospitalisation 20 000 FCFA par jour.</div>
                    </div>

                    <!-- Plafond Spécifique -->
                    <div class="mb-4" id="group_montant_plafond" style="display: none;">
                        <label class="form-label text-muted small fw-bold text-uppercase">Plafond Spécifique de l'Acte (FCFA)</label>
                        <div class="input-group input-group-lg bg-light rounded-3">
                            <input type="number" step="1" name="montant_plafond" class="form-control bg-transparent border-0 shadow-none @error('montant_plafond') is-invalid @enderror" value="{{ old('montant_plafond') }}" min="0" placeholder="Ex: 30000">
                            <span class="input-group-text bg-transparent border-0 text-muted small">FCFA</span>
                        </div>
                        <div class="form-text">Ex: Monture optique 30 000 FCFA par paire.</div>
                    </div>

                    <!-- Conditions Particulières -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Conditions Particulières / Règles Métier</label>
                        <textarea name="conditions_particulieres" class="form-control bg-light border-0 shadow-none" rows="2" placeholder="Ex: Prise en charge sur prescription médicale, max 1 renouvellement par an...">{{ old('conditions_particulieres') }}</textarea>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm">
                            <i class="bi bi-check2 me-2"></i> Enregistrer la règle de couverture
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

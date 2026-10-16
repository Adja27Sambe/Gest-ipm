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
                <form action="{{ route('parametres-couverture.update', $parametres_couverture->id_parametre) }}" method="POST" id="paramEditForm">
                    @csrf
                    @method('PUT')
                    
                    <!-- 1. Acte Médical (Read-only) -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Acte Médical</label>
                        <input type="text" class="form-control form-control-lg bg-light border-0 shadow-none text-dark fw-bold" value="{{ $parametres_couverture->typePrestation->libelle ?? 'N/A' }}" disabled>
                        <div class="form-text">L'acte médical associé ne peut pas être modifié.</div>
                    </div>

                    <!-- 2. Type d'acte si nécessaire -->
                    @php
                        $typesActes = $parametres_couverture->typePrestation?->typesActes ?? collect();
                    @endphp
                    @if($typesActes->count() > 0)
                        <div class="mb-4">
                            <label class="form-label text-muted small fw-bold text-uppercase">
                                Type d'acte spécifique <small class="text-muted fw-normal">(Optionnel si applicable à un sous-acte précis)</small>
                            </label>
                            <select name="id_type_acte" id="select_type_acte" class="form-select form-select-lg bg-light border-0 shadow-none @error('id_type_acte') is-invalid @enderror">
                                <option value="">-- Tous les types d'actes (Règle générale de l'acte) --</option>
                                @foreach($typesActes as $ta)
                                    <option value="{{ $ta->id_type_acte }}" {{ old('id_type_acte', $parametres_couverture->id_type_acte) == $ta->id_type_acte ? 'selected' : '' }}>
                                        {{ $ta->libelle }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_type_acte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Laissez sur « Tous les types d'actes » si la règle s'applique à tous les types d'actes de cette catégorie.</div>
                        </div>
                    @endif

                    <!-- 3. Mode de Calcul -->
                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase">Mode de Calcul de la Couverture <span class="text-danger">*</span></label>
                        <select name="type_calcul" id="type_calcul_select" class="form-select form-select-lg bg-light border-0 shadow-none @error('type_calcul') is-invalid @enderror" required onchange="handleTypeCalculChange(this.value)">
                            @php $currentMode = old('type_calcul', $parametres_couverture->type_calcul ?? 'MONTANT_REFERENTIEL'); @endphp
                            <option value="MONTANT_REFERENTIEL" {{ $currentMode === 'MONTANT_REFERENTIEL' ? 'selected' : '' }}>
                                Montant Référentiel & Part IPM Fixe (Recommandé)
                            </option>
                            <option value="POURCENTAGE" {{ $currentMode === 'POURCENTAGE' ? 'selected' : '' }}>
                                Pourcentage standard (%)
                            </option>
                            <option value="FORFAIT" {{ $currentMode === 'FORFAIT' ? 'selected' : '' }}>
                                Forfait fixe (ex: Maternité 50 000 FCFA)
                            </option>
                            <option value="TARIF_JOURNALIER" {{ $currentMode === 'TARIF_JOURNALIER' ? 'selected' : '' }}>
                                Tarif journalier (ex: Chambre Hôpital 20 000 FCFA/jour)
                            </option>
                            <option value="PLAFOND" {{ $currentMode === 'PLAFOND' ? 'selected' : '' }}>
                                Plafond spécifique par acte (ex: Optique 30 000 FCFA)
                            </option>
                            <option value="EXCLUSION" {{ $currentMode === 'EXCLUSION' ? 'selected' : '' }}>
                                Exclusion de prise en charge (0% IPM)
                            </option>
                            <option value="100_PERCENT_PARTICIPANT" {{ $currentMode === '100_PERCENT_PARTICIPANT' ? 'selected' : '' }}>
                                Participant 100% (0% IPM, 100% Participant)
                            </option>
                            <option value="SOUS_COUVERT" {{ $currentMode === 'SOUS_COUVERT' ? 'selected' : '' }}>
                                Prise en charge Sous Couvert (100% IPM, 0% Participant)
                            </option>
                        </select>
                        @error('type_calcul')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- BLOC PRINCIPAL : MONTANT RÉFÉRENTIEL & CALCUL AUTOMATIQUE PART IPM -->
                    <div class="p-3 bg-light rounded-4 border mb-4" id="bloc_referentiel">
                        <div class="row g-3">
                            <!-- Montant Référentiel -->
                            <div class="col-md-6">
                                <label class="form-label text-muted small fw-bold text-uppercase">
                                    Montant Référentiel (FCFA) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg bg-white rounded-3 border">
                                    <input type="number" step="1" name="montant_referentiel" id="montant_referentiel_input" class="form-control border-0 shadow-none fw-bold @error('montant_referentiel') is-invalid @enderror" value="{{ old('montant_referentiel', $parametres_couverture->montant_referentiel ?? $parametres_couverture->ticket_moderateur) }}" min="0" placeholder="Ex: 10000" oninput="calculerPartIpmReference()">
                                    <span class="input-group-text bg-white border-0 text-muted fw-bold">FCFA</span>
                                </div>
                                <div class="form-text small">Tarif de référence fixé pour l'acte.</div>
                                @error('montant_referentiel')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Taux IPM -->
                            <div class="col-md-6" id="group_taux">
                                <label class="form-label text-muted small fw-bold text-uppercase">
                                    Taux IPM (%) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg bg-white rounded-3 border">
                                    <input type="number" step="0.01" name="taux_prise_charge" id="taux_input" class="form-control border-0 shadow-none fw-bold text-primary @error('taux_prise_charge') is-invalid @enderror" value="{{ old('taux_prise_charge', $parametres_couverture->taux_prise_charge ?? 70) }}" min="0" max="100" oninput="calculerPartIpmReference()">
                                    <span class="input-group-text bg-white border-0 fw-bold text-muted">%</span>
                                </div>
                                <div class="form-text small">Pourcentage pris en charge par l'IPM.</div>
                                @error('taux_prise_charge')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Part IPM de référence (Calculée Automatiquement) -->
                            <div class="col-12">
                                <div class="p-3 bg-white rounded-3 border border-primary border-opacity-25 shadow-xs">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <span class="text-muted small fw-bold text-uppercase d-block">
                                                <i class="bi bi-calculator text-primary me-1"></i> Part IPM de référence (Fixe) :
                                            </span>
                                            <small class="text-muted">Calculée automatiquement : <code>Montant référentiel × Taux IPM</code></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fs-4 fw-bold text-success" id="part_ipm_ref_display">0 FCFA</span>
                                            <input type="hidden" name="part_ipm_reference" id="part_ipm_reference_input" value="{{ old('part_ipm_reference', $parametres_couverture->part_ipm_reference) }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Boîte d'exemples et simulation en temps réel -->
                        <div class="mt-3 p-3 bg-white rounded-3 border small">
                            <span class="fw-bold text-dark d-block mb-1">
                                <i class="bi bi-info-circle-fill text-info me-1"></i> Principe de facturation en saisie de prestation :
                            </span>
                            <div class="text-muted mb-2">
                                La <strong>Part IPM reste FIXE</strong> (<span class="fw-bold text-success" id="simul_part_ipm">0 FCFA</span>). Tout le reste du montant facturé revient au participant.
                            </div>
                            <ul class="mb-0 ps-3 text-muted">
                                <li>Facture = <span id="simul_f1_total">10 000 FCFA</span> → Part IPM = <strong class="text-success" id="simul_f1_ipm">7 000 FCFA</strong> | Part participant = <strong class="text-danger" id="simul_f1_adh">3 000 FCFA</strong></li>
                                <li>Facture = <span id="simul_f2_total">15 000 FCFA</span> → Part IPM = <strong class="text-success" id="simul_f2_ipm">7 000 FCFA</strong> | Part participant = <strong class="text-danger" id="simul_f2_adh">8 000 FCFA</strong></li>
                                <li>Facture = <span id="simul_f3_total">25 000 FCFA</span> → Part IPM = <strong class="text-success" id="simul_f3_ipm">7 000 FCFA</strong> | Part participant = <strong class="text-danger" id="simul_f3_adh">18 000 FCFA</strong></li>
                                <li class="text-primary">Sécurité : si facture inférieure à la part IPM (<span id="simul_f4_total">5 000 FCFA</span>) → Part IPM = <strong class="text-success" id="simul_f4_ipm">5 000 FCFA</strong> | Part participant = <strong class="text-danger">0 FCFA</strong></li>
                            </ul>
                        </div>
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
                            <i class="bi bi-save me-2"></i> Mettre à jour la règle de couverture
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculerPartIpmReference() {
    const montantRef = parseFloat(document.getElementById('montant_referentiel_input')?.value) || 0;
    const taux = parseFloat(document.getElementById('taux_input')?.value) || 0;
    
    // Part IPM de référence = Montant référentiel × Taux IPM
    const partIpmRef = Math.round((montantRef * (taux / 100)));

    const inputHidden = document.getElementById('part_ipm_reference_input');
    if (inputHidden) inputHidden.value = partIpmRef;

    const formatted = new Intl.NumberFormat('fr-FR').format(partIpmRef) + ' FCFA';
    const display = document.getElementById('part_ipm_ref_display');
    if (display) display.textContent = formatted;

    const simulPartIpm = document.getElementById('simul_part_ipm');
    if (simulPartIpm) simulPartIpm.textContent = formatted;

    // Simulation dynamique des factures
    const f1 = montantRef > 0 ? montantRef : 10000;
    const f2 = Math.round(f1 * 1.5);
    const f3 = Math.round(f1 * 2.5);
    const f4 = Math.max(1000, Math.round(partIpmRef * 0.7));

    updateSimulRow('simul_f1', f1, partIpmRef);
    updateSimulRow('simul_f2', f2, partIpmRef);
    updateSimulRow('simul_f3', f3, partIpmRef);
    updateSimulRow('simul_f4', f4, partIpmRef);
}

function updateSimulRow(prefix, totalFacture, partIpmRef) {
    let partIpm = partIpmRef;
    let partParticipant = 0;

    if (totalFacture < partIpmRef) {
        partIpm = totalFacture;
        partParticipant = 0;
    } else {
        partIpm = partIpmRef;
        partParticipant = totalFacture - partIpm;
    }

    const elTotal = document.getElementById(prefix + '_total');
    const elIpm = document.getElementById(prefix + '_ipm');
    const elAdh = document.getElementById(prefix + '_adh');

    if (elTotal) elTotal.textContent = new Intl.NumberFormat('fr-FR').format(totalFacture) + ' FCFA';
    if (elIpm) elIpm.textContent = new Intl.NumberFormat('fr-FR').format(partIpm) + ' FCFA';
    if (elAdh) elAdh.textContent = new Intl.NumberFormat('fr-FR').format(partParticipant) + ' FCFA';
}

function handleTypeCalculChange(mode) {
    const blocRef = document.getElementById('bloc_referentiel');
    const groupForfait = document.getElementById('group_forfait');
    const groupTarif = document.getElementById('group_tarif_journalier');
    const groupPlafond = document.getElementById('group_montant_plafond');

    groupForfait.style.display = (mode === 'FORFAIT') ? 'block' : 'none';
    groupTarif.style.display = (mode === 'TARIF_JOURNALIER') ? 'block' : 'none';
    groupPlafond.style.display = (mode === 'PLAFOND') ? 'block' : 'none';
    blocRef.style.display = (mode === 'MONTANT_REFERENTIEL' || mode === 'POURCENTAGE') ? 'block' : 'none';

    calculerPartIpmReference();
}

document.addEventListener("DOMContentLoaded", function() {
    handleTypeCalculChange(document.getElementById('type_calcul_select')?.value);
    calculerPartIpmReference();
});
</script>
@endsection

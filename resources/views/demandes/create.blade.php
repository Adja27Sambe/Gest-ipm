@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('demandes.index') }}" class="text-decoration-none text-primary">Prises en charge</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Nouvelle demande</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-dark mb-0">
                <i class="bi bi-file-earmark-medical text-primary me-2"></i>Nouvelle Prise en Charge Médicale
            </h2>
            <p class="text-muted small mb-0 mt-1">Sélectionnez le type de document approprié pour configurer automatiquement les règles métier et prestataires éligibles.</p>
        </div>
        <a href="{{ route('demandes.index') }}" class="btn btn-outline-secondary px-3 rounded-3 shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
            <div class="fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i>Veuillez corriger les erreurs suivantes :</div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('demandes.store') }}" method="POST" id="demandeForm">
        @csrf

        <div class="row g-4">
            <!-- COLONNE GAUCHE : SÉLECTION DU TYPE & PARTICIPANT -->
            <div class="col-lg-7">
                
                <!-- 1. SÉLECTION VISUELLE DU TYPE DE DEMANDE -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold text-dark mb-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">Étape 1</span>
                                Précisez le type de prise en charge <span class="text-danger">*</span>
                            </h5>
                            <span class="badge bg-light text-muted fw-normal" id="selectedTypeBadge">Requis</span>
                        </div>
                        <p class="text-muted small mt-1 mb-0">Le choix du type détermine le prestataire requis, les champs obligatoires et le modèle de document généré.</p>
                    </div>
                    <div class="card-body p-4">
                        <!-- Radio Cards Container -->
                        <div class="row g-3">
                            @foreach($typesDemande as $type)
                                @php
                                    $lib = strtolower($type->libelle);
                                    $isBon = str_contains($lib, 'bon');
                                    $isFeuille = str_contains($lib, 'feuille');
                                    $isLettre = str_contains($lib, 'lettre');

                                    $cardId = $isBon ? 'type_bon' : ($isFeuille ? 'type_feuille' : 'type_lettre');
                                    $icon = $isBon ? 'bi-capsule' : ($isFeuille ? 'bi-stethoscope' : 'bi-hospital');
                                    $themeClass = $isBon ? 'success' : ($isFeuille ? 'primary' : 'info');
                                    $tag = $isBon ? 'Pharmacie & Optique' : ($isFeuille ? 'Consultation Cabinet / Clinique' : 'Prise en charge Hôpital / Actes');
                                    $ruleNote = $isBon ? 'Fournisseur : Pharmacie autorisée (Exclut les médecins)' : ($isFeuille ? 'Prestataire : Médecin généraliste ou spécialiste' : 'Prestataire : Établissement de santé agréé');
                                @endphp
                                <div class="col-12">
                                    <label class="type-card w-100 p-3 rounded-4 border position-relative d-block cursor-pointer transition-all" for="{{ $cardId }}">
                                        <input type="radio" 
                                               name="id_type_demande" 
                                               id="{{ $cardId }}" 
                                               value="{{ $type->id_type_demande }}" 
                                               class="position-absolute top-50 end-0 me-3 translate-middle-y form-check-input type-radio"
                                               data-libelle="{{ $lib }}"
                                               data-name="{{ $type->libelle }}"
                                               {{ old('id_type_demande', 1) == $type->id_type_demande ? 'checked' : '' }}
                                               required 
                                               onchange="handleTypeChange()">
                                        <div class="d-flex align-items-start pe-5">
                                            <div class="rounded-3 p-3 bg-{{ $themeClass }} bg-opacity-10 text-{{ $themeClass }} me-3 flex-shrink-0">
                                                <i class="bi {{ $icon }} fs-3"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                    <h6 class="fw-bold text-dark mb-0">{{ $type->libelle }}</h6>
                                                    <span class="badge bg-{{ $themeClass }} bg-opacity-15 text-{{ $themeClass }} rounded-pill small px-2 py-1">{{ $tag }}</span>
                                                </div>
                                                <p class="text-muted small mb-2">
                                                    @if($isBon)
                                                        Accès aux médicaments ou dispositifs optiques sur présentation d'une ordonnance valide (max 6 mois).
                                                    @elseif($isFeuille)
                                                        Consultation médicale standard pour soins ambulatoires et actes médicaux courants.
                                                    @else
                                                        Accord préalable pour actes lourds : hospitalisation, intervention chirurgicale, scanner, IRM ou analyses.
                                                    @endif
                                                </p>
                                                <div class="small text-{{ $themeClass }} fw-medium">
                                                    <i class="bi bi-shield-check me-1"></i>{{ $ruleNote }}
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- 2. IDENTIFICATION DU PARTICIPANT & BÉNÉFICIAIRE -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2">Étape 2</span>
                            Assuré & Bénéficiaire des soins
                        </h5>
                        <p class="text-muted small mt-1 mb-0">Sélectionnez le salarié cotisant, puis indiquez si la prise en charge concerne le salarié ou un ayant droit.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-7">
                                <label for="matricule_input" class="form-label fw-semibold text-dark small mb-1">
                                    <i class="bi bi-upc-scan text-primary me-1"></i>Matricule du Participant (Salarié) <span class="text-danger">*</span>
                                </label>
                                
                                <div class="position-relative">
                                    <div class="input-group input-group-lg shadow-xs">
                                        <span class="input-group-text bg-white border-end-0 text-muted">
                                            <i class="bi bi-search text-primary"></i>
                                        </span>
                                        <input type="text" 
                                               id="matricule_input" 
                                               class="form-control form-control-lg border-start-0 @error('id_salarie') is-invalid @enderror" 
                                               placeholder="Saisir le matricule, nom ou prénom..." 
                                               autocomplete="off"
                                               oninput="handleMatriculeSearch(this.value)"
                                               onfocus="if(this.value.trim().length >= 1) handleMatriculeSearch(this.value)"
                                               value="">
                                        <button class="btn btn-outline-secondary border-start-0" type="button" id="btn_clear_matricule" onclick="clearSelectedParticipant()" style="display: none;" title="Effacer la sélection">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="id_salarie" id="selected_id_salarie" value="{{ old('id_salarie') }}" required>

                                    <!-- Menu déroulant flottant d'autocomplétion -->
                                    <div id="autocomplete_dropdown" class="dropdown-menu shadow-lg border-0 rounded-4 w-100 p-2 mt-1 position-absolute" style="max-height: 280px; overflow-y: auto; z-index: 1060; display: none;">
                                        <!-- Rempli en direct : Matricule, Nom, Prénom -->
                                    </div>

                                    @error('id_salarie')
                                        <div class="text-danger small mt-1 fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Carte récapitulative du participant sélectionné -->
                                <div id="selected_participant_badge" class="p-3 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 d-flex align-items-center justify-content-between mt-2 shadow-xs" style="display: none;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                            <i class="bi bi-person-check-fill fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">
                                                <span class="badge bg-primary font-monospace fs-6 me-2" id="badge_matricule">MAT-000</span>
                                                <span id="badge_nom_prenom">NOM Prénom</span>
                                            </div>
                                            <div class="small text-muted mt-1">
                                                <i class="bi bi-building me-1"></i><span id="badge_entreprise">Entreprise</span>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="focusMatriculeInput()">
                                        <i class="bi bi-pencil-square me-1"></i>Modifier
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <label for="ayant_droit_select" class="form-label fw-semibold text-dark small mb-1">
                                    Bénéficiaire des soins <span class="text-danger">*</span>
                                </label>
                                <select name="id_ayant_droit" id="ayant_droit_select" class="form-select form-select-lg @error('id_ayant_droit') is-invalid @enderror" onchange="handleBeneficiaireChange()">
                                    <option value="">Le participant lui-même</option>
                                </select>
                                @error('id_ayant_droit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Fiche récapitulative du bénéficiaire -->
                            <div class="col-12" id="beneficiaire_info_box" style="display: none;">
                                <div class="p-3 bg-light rounded-3 border d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 text-primary">
                                        <i class="bi bi-person-badge fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold text-dark" id="beneficiaire_nom_display">-</div>
                                        <div class="small text-muted" id="beneficiaire_details_display">-</div>
                                    </div>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2" id="beneficiaire_statut_badge">
                                        <i class="bi bi-check-circle me-1"></i> Éligible
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- COLONNE DROITE : CHAMPS CONTEXTUELS ET ACTIONS -->
            <div class="col-lg-5">
                
                <!-- 3. CONFIGURATION SPÉCIFIQUE DU DOCUMENT -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h5 class="fw-bold text-dark mb-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">Étape 3</span>
                                Paramètres du document
                            </h5>
                        </div>
                        <p class="text-muted small mt-1 mb-0" id="section_description">Remplissez les informations obligatoires pour ce type de demande.</p>
                    </div>
                    <div class="card-body p-4">

                        <!-- SECTION BON DE COMMANDE : PHARMACIE + ORDONNANCE -->
                        <div id="section_bon_commande" class="context-section">
                            <div class="alert alert-success bg-success bg-opacity-10 border-0 rounded-3 small p-3 mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>Règle Pharmacie :</strong> Un Bon de Commande permet d'obtenir des produits pharmaceutiques. Vous devez obligatoirement choisir une <strong>pharmacie conventionnée</strong>.
                            </div>

                            <div class="mb-3">
                                <label for="pharmacie_select" class="form-label fw-semibold text-dark small mb-1">
                                    Pharmacie ou Opticien conventionné <span class="text-danger">*</span>
                                </label>
                                <select name="id_pharmacie" id="pharmacie_select" class="form-select @error('id_pharmacie') is-invalid @enderror">
                                    <option value="">-- Choisir une pharmacie conventionnée --</option>
                                    @foreach($pharmacies as $pharmacie)
                                        <option value="{{ $pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK }}" {{ old('id_pharmacie') == ($pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK) ? 'selected' : '' }}>
                                            💊 {{ $pharmacie->nom ?? $pharmacie->NOMPHARM }} {{ $pharmacie->adresse ? '('.$pharmacie->adresse.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_pharmacie')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-md-7">
                                    <label for="date_ordonnance" class="form-label fw-semibold text-dark small mb-1">
                                        Date de prescription <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" 
                                           name="date_ordonnance" 
                                           id="date_ordonnance" 
                                           class="form-control @error('date_ordonnance') is-invalid @enderror" 
                                           value="{{ old('date_ordonnance', date('Y-m-d')) }}" 
                                           max="{{ date('Y-m-d') }}"
                                           min="{{ now()->subMonths(6)->toDateString() }}">
                                    <div class="form-text small text-muted">Validité maximale : 6 mois</div>
                                    @error('date_ordonnance')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-5">
                                    <label for="nombre_articles" class="form-label fw-semibold text-dark small mb-1">
                                        Nombre d'articles <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           name="nombre_articles" 
                                           id="nombre_articles" 
                                           class="form-control @error('nombre_articles') is-invalid @enderror" 
                                           value="{{ old('nombre_articles', 1) }}" 
                                           min="1" 
                                           max="20">
                                    <div class="form-text small text-muted">Min : 1 article</div>
                                    @error('nombre_articles')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- SECTION FEUILLE DE MALADIE & LETTRE DE GARANTIE : PRATICIEN -->
                        <div id="section_praticien" class="context-section" style="display: none;">
                            <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-3 small p-3 mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                <strong>Règle Praticien :</strong> La consultation ou l'acte médical doit être réalisé auprès d'un <strong>médecin, spécialiste ou établissement agréé</strong>.
                            </div>

                            <div class="mb-3">
                                <label for="praticien_select" class="form-label fw-semibold text-dark small mb-1">
                                    Praticien ou Établissement médical <span class="text-danger">*</span>
                                </label>
                                <select name="id_praticien" id="praticien_select" class="form-select @error('id_praticien') is-invalid @enderror">
                                    <option value="">-- Choisir un praticien ou clinique --</option>
                                    @foreach($praticiens as $praticien)
                                        <option value="{{ $praticien->id_praticien ?? $praticien->PRCLEUNIK }}" {{ old('id_praticien') == ($praticien->id_praticien ?? $praticien->PRCLEUNIK) ? 'selected' : '' }}>
                                            🩺 {{ $praticien->nom ?? $praticien->NOMPRAT }} {{ $praticien->specialite ? '('.$praticien->specialite.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_praticien')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- SECTION LETTRE DE GARANTIE : CHOIX DE L'ACTE -->
                        <div id="section_lettre_garantie" class="context-section" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark small mb-2">
                                    Type d'acte pris en charge <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2">
                                    @php
                                        $actesDisponibles = [
                                            'Hospitalisation' => 'bi-hospital',
                                            'Radiologie' => 'bi-radioactive',
                                            'Analyses Médicales' => 'bi-droplet-half',
                                            'Spécialité Médicale' => 'bi-person-badge',
                                            'Médecine Générale' => 'bi-stethoscope',
                                            'Maternité / Accouchement' => 'bi-heart-pulse',
                                            'Optique Médicale' => 'bi-eyeglasses',
                                            'Consultation + Soins Dentaires' => 'bi-emoji-smile',
                                            'Consultation Ophtalmologie' => 'bi-eye',
                                        ];
                                    @endphp
                                    @foreach($actesDisponibles as $acte => $acteIcon)
                                        <div class="col-md-6 col-12">
                                            <input type="checkbox" 
                                                   class="btn-check" 
                                                   name="choix_acte[]" 
                                                   id="acte_{{ \Illuminate\Support\Str::slug($acte) }}" 
                                                   value="{{ $acte }}" 
                                                   autocomplete="off"
                                                   {{ in_array($acte, (array) old('choix_acte', [])) ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary w-100 p-2 text-start small d-flex align-items-center gap-2 rounded-3" for="acte_{{ \Illuminate\Support\Str::slug($acte) }}">
                                                <i class="bi {{ $acteIcon }}"></i>
                                                <span class="fw-semibold">{{ $acte }}</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('choix_acte')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- MOTIF / OBSERVATIONS GÉNÉRALES -->
                        <div class="mb-3">
                            <label for="motif" class="form-label fw-semibold text-dark small mb-1">Motif ou Objet de la demande</label>
                            <textarea name="motif" id="motif" class="form-control @error('motif') is-invalid @enderror" rows="2" placeholder="Ex: Affection respiratoire, renouvellement de lunettes, hospitalisation d'urgence...">{{ old('motif') }}</textarea>
                            @error('motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- OBSERVATIONS SPÉCIFIQUES POUR FEUILLE / LETTRE -->
                        <div id="section_observations" class="context-section" style="display: none;">
                            <div class="mb-3">
                                <label for="diagnostic" class="form-label fw-semibold text-dark small mb-1">Diagnostic prévisionnel (Feuille de maladie)</label>
                                <textarea name="diagnostic" id="diagnostic" class="form-control" rows="2" placeholder="Diagnostic médical ou précision clinique...">{{ old('diagnostic') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label for="observations" class="form-label fw-semibold text-dark small mb-1">Observations / Remarques complémentaires</label>
                                <textarea name="observations" id="observations" class="form-control" rows="2" placeholder="Indications complémentaires, devis, prise en charge conjointe...">{{ old('observations') }}</textarea>
                            </div>
                        </div>

                    </div>
                    
                    <!-- PIED DU FORMULAIRE : ACTIONS -->
                    <div class="card-footer bg-light p-4 rounded-bottom-4 border-0">
                        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="submitBtn">
                            <i class="bi bi-file-earmark-check-fill fs-5"></i>
                            <span>Générer la prise en charge</span>
                        </button>
                        <p class="text-muted text-center small mb-0 mt-2">
                            <i class="bi bi-lock-fill me-1"></i> La validation enregistrera automatiquement le document avec son numéro unique.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s ease-in-out; }
.type-card {
    background: #ffffff;
    border-color: #e5e7eb !important;
}
.type-card:hover {
    border-color: #0f4c81 !important;
    background: #f8fafc;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(15, 76, 129, 0.08);
}
.type-card.active {
    border-color: #0f4c81 !important;
    border-width: 2px !important;
    background: #f0f7ff;
    box-shadow: 0 6px 16px rgba(15, 76, 129, 0.12);
}
.type-radio {
    width: 20px;
    height: 20px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
    updateAyantsDroit();
});

function handleTypeChange() {
    const selectedRadio = document.querySelector('input[name="id_type_demande"]:checked');
    if (!selectedRadio) return;

    // Highlight visual active card
    document.querySelectorAll('.type-card').forEach(card => card.classList.remove('active'));
    selectedRadio.closest('.type-card').classList.add('active');

    const libelle = (selectedRadio.getAttribute('data-libelle') || '').toLowerCase();
    const isBon = libelle.includes('bon');
    const isFeuille = libelle.includes('feuille');
    const isLettre = libelle.includes('lettre');

    const badge = document.getElementById('selectedTypeBadge');
    badge.textContent = selectedRadio.getAttribute('data-name');
    badge.className = 'badge ' + (isBon ? 'bg-success' : (isFeuille ? 'bg-primary' : 'bg-info')) + ' text-white';

    const submitSpan = document.querySelector('#submitBtn span');
    submitSpan.textContent = 'Générer : ' + selectedRadio.getAttribute('data-name');

    // Sections
    const secBon = document.getElementById('section_bon_commande');
    const secPrat = document.getElementById('section_praticien');
    const secLettre = document.getElementById('section_lettre_garantie');
    const secObs = document.getElementById('section_observations');

    const pharmacieSelect = document.getElementById('pharmacie_select');
    const praticienSelect = document.getElementById('praticien_select');
    const dateOrdonnance = document.getElementById('date_ordonnance');
    const nombreArticles = document.getElementById('nombre_articles');

    if (isBon) {
        secBon.style.display = 'block';
        secPrat.style.display = 'none';
        secLettre.style.display = 'none';
        secObs.style.display = 'none';

        pharmacieSelect.setAttribute('required', 'required');
        dateOrdonnance.setAttribute('required', 'required');
        nombreArticles.setAttribute('required', 'required');

        praticienSelect.removeAttribute('required');
        praticienSelect.value = '';
    } else {
        secBon.style.display = 'none';
        pharmacieSelect.removeAttribute('required');
        pharmacieSelect.value = '';
        dateOrdonnance.removeAttribute('required');
        nombreArticles.removeAttribute('required');

        secPrat.style.display = 'block';
        praticienSelect.setAttribute('required', 'required');

        if (isFeuille) {
            secLettre.style.display = 'none';
            secObs.style.display = 'block';
        } else if (isLettre) {
            secLettre.style.display = 'block';
            secObs.style.display = 'block';
        }
    }
}

<script>
let currentParticipant = null;
let searchDebounceTimer = null;

document.addEventListener('DOMContentLoaded', function() {
    handleTypeChange();
    
    // Initialisation si un participant était déjà sélectionné (erreur de validation ou ancienne valeur)
    const initialSalarieId = document.getElementById('selected_id_salarie').value;
    if (initialSalarieId) {
        fetch(`{{ route('salaries.search-matricule') }}?id=${encodeURIComponent(initialSalarieId)}`)
            .then(res => res.json())
            .then(item => {
                if (item && item.id) {
                    selectParticipantItem(item);
                    @if(old('id_ayant_droit'))
                        setTimeout(() => {
                            document.getElementById('ayant_droit_select').value = "{{ old('id_ayant_droit') }}";
                            handleBeneficiaireChange();
                        }, 50);
                    @endif
                }
            })
            .catch(console.error);
    }
});

// Fermer le dropdown lors d'un clic en dehors
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('autocomplete_dropdown');
    const input = document.getElementById('matricule_input');
    if (dropdown && !dropdown.contains(e.target) && e.target !== input) {
        dropdown.style.display = 'none';
    }
});

function handleMatriculeSearch(query) {
    const dropdown = document.getElementById('autocomplete_dropdown');
    const term = query.trim();
    
    if (term.length < 1) {
        dropdown.style.display = 'none';
        return;
    }

    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(() => {
        dropdown.innerHTML = '<div class="p-3 text-center text-muted small"><span class="spinner-border spinner-border-sm me-2 text-primary"></span>Recherche du participant...</div>';
        dropdown.style.display = 'block';

        fetch(`{{ route('salaries.search-matricule') }}?q=${encodeURIComponent(term)}`)
            .then(res => res.json())
            .then(data => {
                if (!Array.isArray(data) || data.length === 0) {
                    dropdown.innerHTML = `
                        <div class="p-3 text-center text-muted small">
                            <i class="bi bi-person-x fs-5 d-block mb-1 text-secondary opacity-75"></i>
                            Aucun participant trouvé pour <strong>"${escapeHtml(term)}"</strong>
                        </div>`;
                    return;
                }

                let html = '';
                data.forEach(item => {
                    const statusBadge = item.statut === 'actif' 
                        ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 small">Actif</span>' 
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0 small">Radié</span>';
                    
                    const itemJson = JSON.stringify(item).replace(/'/g, "&#39;");
                    html += `
                        <a href="javascript:void(0)" class="dropdown-item p-2 rounded-3 mb-1 text-wrap d-flex justify-content-between align-items-center" onclick='selectParticipantItem(${itemJson})'>
                            <div>
                                <div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 font-monospace me-2">${escapeHtml(item.matricule)}</span>
                                    <strong class="text-dark">${escapeHtml(item.nom)} ${escapeHtml(item.prenom)}</strong>
                                </div>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-building me-1"></i>${escapeHtml(item.entreprise)}
                                    ${item.ayants_droit && item.ayants_droit.length > 0 ? `<span class="ms-2 text-primary fw-medium">• ${item.ayants_droit.length} ayant(s) droit</span>` : ''}
                                </div>
                            </div>
                            ${statusBadge}
                        </a>
                    `;
                });
                dropdown.innerHTML = html;
            })
            .catch(err => {
                dropdown.innerHTML = '<div class="p-3 text-center text-danger small"><i class="bi bi-exclamation-triangle me-1"></i>Erreur lors de la recherche</div>';
            });
    }, 200);
}

function selectParticipantItem(item) {
    currentParticipant = item;
    document.getElementById('selected_id_salarie').value = item.id;
    document.getElementById('matricule_input').value = item.matricule;
    document.getElementById('autocomplete_dropdown').style.display = 'none';

    // Remplissage de la carte récapitulative
    document.getElementById('badge_matricule').textContent = item.matricule;
    document.getElementById('badge_nom_prenom').textContent = `${item.nom} ${item.prenom}`;
    document.getElementById('badge_entreprise').textContent = item.entreprise ? `(${item.entreprise})` : '';
    document.getElementById('selected_participant_badge').style.display = 'flex';
    document.getElementById('btn_clear_matricule').style.display = 'block';

    // Remplissage dynamique des ayants droit
    const ayantDroitSelect = document.getElementById('ayant_droit_select');
    ayantDroitSelect.innerHTML = '<option value="">Le participant lui-même</option>';

    if (Array.isArray(item.ayants_droit) && item.ayants_droit.length > 0) {
        item.ayants_droit.forEach(ad => {
            const ageLabel = ad.age ? ` (${ad.age} ans)` : '';
            const opt = document.createElement('option');
            opt.value = ad.id_ayant_droit;
            opt.setAttribute('data-nom', `${ad.prenom} ${ad.nom}`);
            opt.setAttribute('data-lien', ad.lien_parente || 'Ayant-droit');
            opt.textContent = `${ad.prenom} ${ad.nom} — ${ad.lien_parente || 'Ayant droit'}${ageLabel}`;
            ayantDroitSelect.appendChild(opt);
        });
    }

    handleBeneficiaireChange();
}

function clearSelectedParticipant() {
    currentParticipant = null;
    document.getElementById('selected_id_salarie').value = '';
    document.getElementById('matricule_input').value = '';
    document.getElementById('selected_participant_badge').style.display = 'none';
    document.getElementById('btn_clear_matricule').style.display = 'none';
    document.getElementById('autocomplete_dropdown').style.display = 'none';

    const ayantDroitSelect = document.getElementById('ayant_droit_select');
    ayantDroitSelect.innerHTML = '<option value="">Le participant lui-même</option>';
    document.getElementById('beneficiaire_info_box').style.display = 'none';

    document.getElementById('matricule_input').focus();
}

function focusMatriculeInput() {
    const input = document.getElementById('matricule_input');
    input.focus();
    input.select();
    if (input.value.trim().length >= 1) {
        handleMatriculeSearch(input.value);
    }
}

function handleBeneficiaireChange() {
    const ayantDroitSelect = document.getElementById('ayant_droit_select');
    const infoBox = document.getElementById('beneficiaire_info_box');
    const nomDisplay = document.getElementById('beneficiaire_nom_display');
    const detailsDisplay = document.getElementById('beneficiaire_details_display');

    if (!currentParticipant) {
        infoBox.style.display = 'none';
        return;
    }

    const adOption = ayantDroitSelect.options[ayantDroitSelect.selectedIndex];
    infoBox.style.display = 'flex';

    if (adOption && adOption.value) {
        nomDisplay.textContent = adOption.getAttribute('data-nom');
        detailsDisplay.textContent = 'Ayant-droit (' + (adOption.getAttribute('data-lien') || 'Famille') + ') de ' + currentParticipant.nom_complet;
    } else {
        nomDisplay.textContent = currentParticipant.nom_complet;
        detailsDisplay.textContent = 'Participant titulaire — Matricule : ' + (currentParticipant.matricule || 'N/A');
    }
}

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}
</script>
@endsection

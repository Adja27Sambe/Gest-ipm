@extends('layouts.app')

@php
    $groupedTypes = $typesPrestation->groupBy(function($item) {
        $lib = mb_strtolower($item->libelle);
        if (str_contains($lib, 'dent') || str_contains($lib, 'kiné') || str_contains($lib, 'rééduc')) {
            return '🦷 Dentaire & Rééducation';
        }
        if (str_contains($lib, 'consultation') || str_contains($lib, 'visite') || str_contains($lib, 'infirmier') || str_contains($lib, 'spécialité')) {
            return '🩺 Consultations & Soins Médicaux';
        }
        if (str_contains($lib, 'pharmacie') || str_contains($lib, 'médicament') || str_contains($lib, 'optique') || str_contains($lib, 'verre')) {
            return '💊 Pharmacie & Optique';
        }
        if (str_contains($lib, 'analyse') || str_contains($lib, 'biologie') || str_contains($lib, 'radio') || str_contains($lib, 'écho') || str_contains($lib, 'irm') || str_contains($lib, 'scanner')) {
            return '🔬 Examens & Imagerie Médicale';
        }
        if (str_contains($lib, 'hospital') || str_contains($lib, 'chirurgie') || str_contains($lib, 'maternité') || str_contains($lib, 'accouchement')) {
            return '🏥 Hospitalisation & Interventions';
        }
        return '📋 Autres Actes & Soins';
    });
@endphp

@section('content')
<div class="container-fluid py-3">
    <!-- Header & Breadcrumbs -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('prestations.index') }}" class="text-decoration-none text-primary">Prestations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Édition #{{ $prestation->id_prestation }}</li>
                </ol>
            </nav>
            <h2 class="h3 fw-bold text-dark mb-0">
                <i class="bi bi-pencil-square text-primary me-2"></i>Édition de la Prestation #{{ $prestation->id_prestation }}
            </h2>
            <p class="text-muted small mb-0 mt-1">Ajustez les montants, le taux de couverture, le type d'acte ou le partenaire de santé avec recalcul automatique en temps réel.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('prestations.index') }}" class="btn btn-outline-secondary px-3 rounded-3 shadow-sm">
                <i class="bi bi-arrow-left me-1"></i> Annuler
            </a>
        </div>
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

    <form action="{{ route('prestations.update', $prestation) }}" method="POST" id="editPrestationForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <!-- COLONNE PRINCIPALE : FORMULAIRE D'ÉDITION -->
            <div class="col-lg-8">
                
                <!-- 1. INFORMATIONS DE L'ACTE & DEMANDE -->
                <div class="card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2">1</span>
                            Informations Générales de l'Acte
                        </h5>
                        <p class="text-muted small mt-1 mb-0">Rattachement à la prise en charge médicale et date de réalisation.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="date_prestation" class="form-label fw-semibold text-dark small mb-1">
                                    Date de l'acte médical <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="date_prestation" 
                                       id="date_prestation" 
                                       class="form-control form-control-lg @error('date_prestation') is-invalid @enderror" 
                                       value="{{ old('date_prestation', $prestation->date_prestation ? \Carbon\Carbon::parse($prestation->date_prestation)->format('Y-m-d') : date('Y-m-d')) }}" 
                                       required>
                                @error('date_prestation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-12 mt-3">
                                <div class="p-4 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 shadow-sm position-relative overflow-hidden">
                                    <div class="position-absolute top-0 end-0 p-3 opacity-25" style="transform: translate(10%, -10%);">
                                        <i class="bi bi-shield-check" style="font-size: 5rem; color: var(--secondary-green);"></i>
                                    </div>
                                    
                                    <label for="id_demande" class="form-label fw-bold text-success mb-2 d-flex align-items-center position-relative z-index-1">
                                        <i class="bi bi-link-45deg fs-4 me-2"></i> Prise en Charge Source Validée (Demande) <span class="text-danger ms-1">*</span>
                                    </label>
                                    
                                    <div class="position-relative z-index-1">
                                        <select name="id_demande" id="id_demande" class="form-select form-select-lg @error('id_demande') is-invalid @enderror" required onchange="handleDemandeChange()">
                                            <option value="">-- Sélectionner une prise en charge validée --</option>
                                            @foreach($demandes as $d)
                                                @php
                                                    $benef = $d->ayantDroit ? ($d->ayantDroit->nom . ' ' . $d->ayantDroit->prenom . ' (Ayant droit)') : ($d->salarie->nom . ' ' . $d->salarie->prenom . ' - Participant');
                                                    $docType = $d->typeDemande->libelle ?? 'Prise en charge';
                                                @endphp
                                                <option value="{{ $d->id_demande }}" 
                                                        data-beneficiaire="{{ $benef }}"
                                                        data-praticien-id="{{ $d->id_praticien }}"
                                                        data-pharmacie-id="{{ $d->id_pharmacie }}"
                                                        data-is-bon-commande="{{ $d->is_bon_commande ? '1' : '0' }}"
                                                        data-doc-type="{{ strtolower($docType) }}"
                                                        data-nombre-articles="{{ $d->nombre_articles }}"
                                                        {{ old('id_demande', $prestation->id_demande) == $d->id_demande ? 'selected' : '' }}>
                                                    Demande #{{ $d->id_demande }}{{ $d->numero_demande ? ' (' . $d->numero_demande . ')' : '' }} ({{ $docType }}) — {{ $benef }} — [{{ ucfirst($d->statut) }}] — {{ $d->date_demande ? \Carbon\Carbon::parse($d->date_demande)->format('d/m/Y') : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <div class="form-text small text-success mt-2 fw-semibold position-relative z-index-1">
                                        <i class="bi bi-info-circle-fill me-1"></i> Seules les prises en charge validées / approuvées sont éligibles pour l'imputation d'une prestation.
                                    </div>
                                    @error('id_demande') <div class="invalid-feedback d-block position-relative z-index-1">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PARTENAIRE DE SANTÉ -->
                <div class="card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h5 class="fw-bold text-dark mb-0">
                            <span class="badge bg-primary bg-opacity-10 text-primary me-2">2</span>
                            Partenaire de Santé Conventionné
                        </h5>
                        <p class="text-muted small mt-1 mb-0">Sélectionnez le médecin traitant, la clinique ou l'officine pharmaceutique prestataire.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="id_praticien" class="form-label fw-semibold text-dark small mb-1">
                                    🩺 Médecin / Clinique / Centre d'imagerie
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 ms-1 fw-normal" id="badge_prat" style="display: none; font-size: 0.72rem;">
                                        Auto-rempli
                                    </span>
                                </label>
                                <select name="id_praticien" id="id_praticien" class="form-select @error('id_praticien') is-invalid @enderror">
                                    <option value="">-- Aucun praticien --</option>
                                    @foreach($praticiens as $praticien)
                                        <option value="{{ $praticien->id_praticien ?? $praticien->PRCLEUNIK }}" 
                                                {{ old('id_praticien', $prestation->id_praticien) == ($praticien->id_praticien ?? $praticien->PRCLEUNIK) ? 'selected' : '' }}>
                                            {{ $praticien->nom ?? $praticien->NOMPRAT }} {{ $praticien->specialite ? '('.$praticien->specialite.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_praticien') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="id_pharmacie" class="form-label fw-semibold text-dark small mb-1">
                                    💊 Pharmacie / Opticien conventionné
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 ms-1 fw-normal" id="badge_pharm" style="display: none; font-size: 0.72rem;">
                                        Auto-remplie
                                    </span>
                                </label>
                                <select name="id_pharmacie" id="id_pharmacie" class="form-select @error('id_pharmacie') is-invalid @enderror">
                                    <option value="">-- Aucune pharmacie --</option>
                                    @foreach($pharmacies as $pharmacie)
                                        <option value="{{ $pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK }}" 
                                                {{ old('id_pharmacie', $prestation->id_pharmacie) == ($pharmacie->id_pharmacie ?? $pharmacie->PHCLEUNIK) ? 'selected' : '' }}>
                                            {{ $pharmacie->nom ?? $pharmacie->NOMPHARM }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_pharmacie') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. DÉTAIL DES ACTES / ARTICLES -->
                <div class="card border-0 rounded-4 mb-4" id="articles_card">
                    <div class="card-header bg-white border-0 pt-4 pb-2 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">3</span>
                                <i class="bi bi-list-task text-primary me-1"></i>
                                Détail des Actes / Articles
                            </h5>
                            <p class="text-muted small mt-1 mb-0">Renseignez le nom, la quantité et le prix unitaire pour chaque acte ou article.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill fw-semibold" id="articles_quota_badge">
                                <i class="bi bi-file-earmark-medical me-1"></i>
                                1 acte(s) / article(s)
                            </span>
                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm fw-medium" onclick="addArticleRow()">
                                <i class="bi bi-plus-lg"></i> Ajouter ligne
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4 pt-2">
                        <div class="table-responsive rounded-3 border bg-white">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th class="ps-3" style="width: 40px;">#</th>
                                        <th style="min-width: 300px;">
                                            <i class="bi bi-ui-checks text-primary me-1"></i>Acte / Médicament <span class="text-danger">*</span>
                                        </th>
                                        <th style="width: 130px;" class="text-center bg-primary bg-opacity-10 text-primary fw-bold border-start border-end col-quantite">
                                            <i class="bi bi-stack me-1"></i>Quantité <span class="text-danger">*</span>
                                        </th>
                                        <th style="width: 220px;">Prix Unitaire (FCFA) <span class="text-danger">*</span></th>
                                        <th class="text-end pe-3" style="width: 140px;">Total Ligne</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="articles_tbody">
                                </tbody>
                                <tfoot class="table-light border-top">
                                    <tr>
                                        <th colspan="4" id="articles_total_label" class="text-end py-3 fw-bold text-dark">
                                            Total cumulé des articles :
                                        </th>
                                        <th class="text-end pe-3 py-3 text-primary fw-bold fs-5" id="articles_sum_display">
                                            0 FCFA
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-sm" onclick="addArticleRow()">
                                <i class="bi bi-plus-lg me-1"></i> Ajouter une ligne d'acte / article
                            </button>
                            <div class="small text-muted">
                                <i class="bi bi-arrow-down-up text-primary me-1"></i> Le montant facturé est automatiquement synchronisé.
                            </div>
                        </div>

                    </div>
                </div>

                <!-- 4. MONTANTS ET TAUX DE PRISE EN CHARGE -->
                <div class="card border-0 rounded-4 mb-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold text-dark mb-0">
                                <span class="badge bg-primary bg-opacity-10 text-primary me-2">4</span>
                                Tarification & Prise en Charge
                            </h5>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold" id="montant_sync_badge" style="display: none;">
                                <i class="bi bi-link-45deg me-1"></i> Synchronisé avec les articles
                            </span>
                        </div>
                        <p class="text-muted small mt-1 mb-0">Saisissez le montant facturé et le taux pour recalculer immédiatement la quote-part IPM et le reste à charge.</p>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="montant" class="form-label fw-semibold text-dark small mb-1">
                                    Montant Total Facturé (FCFA) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="number" 
                                           step="1" 
                                           name="montant" 
                                           id="montant_input" 
                                           class="form-control fw-bold text-dark @error('montant') is-invalid @enderror" 
                                           value="{{ old('montant', round($prestation->montant)) }}" 
                                           required 
                                           oninput="recalculerFinances()">
                                    <span class="input-group-text bg-light text-muted fw-bold">FCFA</span>
                                </div>
                                @error('montant') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="taux_prise_charge" class="form-label fw-semibold text-dark small mb-1">
                                    Taux de Prise en Charge (%) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-lg">
                                    <input type="number" 
                                           step="0.1" 
                                           max="100" 
                                           min="0" 
                                           name="taux_prise_charge" 
                                           id="taux_input" 
                                           class="form-control fw-bold text-primary @error('taux_prise_charge') is-invalid @enderror" 
                                           value="{{ old('taux_prise_charge', (float)$prestation->taux_prise_charge) }}" 
                                           required 
                                           oninput="recalculerFinances()">
                                    <span class="input-group-text bg-light text-muted fw-bold">%</span>
                                </div>
                                <div class="form-text small text-muted">Auto-rempli selon l'acte sélectionné ci-dessus ou modifiable manuellement.</div>
                                @error('taux_prise_charge') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- COLONNE DROITE : CALCULATEUR EN DIRECT & ACTIONS -->
            <div class="col-lg-4">
                
                <!-- RÉCAPITULATIF FINANCIER DYNAMIQUE -->
                <div class="card border-0 shadow-lg rounded-4 mb-4 text-white" style="background: linear-gradient(135deg, var(--primary-blue) 0%, #00d2ff 100%);">
                    <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4">
                        <h6 class="fw-bold text-white text-uppercase small mb-0" style="letter-spacing: 0.5px;">
                            <i class="bi bi-calculator text-white-50 me-2"></i>Récapitulatif Financier
                        </h6>
                    </div>
                    <div class="card-body p-4">
                        
                        <div class="p-3 bg-white bg-opacity-10 rounded-3 border border-white border-opacity-25 mb-3 shadow-sm">
                            <div class="text-white-50 small mb-1">Montant Total Facturé</div>
                            <div class="h4 fw-bold text-white mb-0" id="calc_total_display">0 FCFA</div>
                        </div>

                        <div class="p-3 bg-white bg-opacity-25 rounded-3 border border-white border-opacity-50 mb-3 shadow-sm position-relative overflow-hidden">
                            <div class="position-absolute top-0 end-0 p-2 opacity-25">
                                <i class="bi bi-shield-check" style="font-size: 3rem;"></i>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1 position-relative z-index-1">
                                <span class="text-white small fw-semibold">Part Prise en Charge (IPM)</span>
                                <span class="badge bg-white text-primary rounded-pill px-2 py-0 small shadow-sm" id="calc_taux_badge">80%</span>
                            </div>
                            <div class="h3 fw-bold text-white mb-0 position-relative z-index-1" id="calc_pec_display">0 FCFA</div>
                        </div>

                        <div class="p-3 bg-dark bg-opacity-25 rounded-3 border border-dark border-opacity-25 mb-4 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-white-50 small fw-semibold">Reste à Charge (Patient)</span>
                                <span class="badge bg-danger text-white rounded-pill px-2 py-0 small shadow-sm" id="calc_reste_badge">20%</span>
                            </div>
                            <div class="h3 fw-bold text-white mb-0" id="calc_reste_display">0 FCFA</div>
                        </div>

                        <!-- ACTIONS -->
                        <button type="submit" class="btn btn-light btn-lg w-100 py-3 fw-bold text-primary rounded-3 shadow-lg mb-3 d-flex align-items-center justify-content-center gap-2 hover-lift">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span>Enregistrer les modifications</span>
                        </button>

                        <a href="{{ route('prestations.index') }}" class="btn btn-outline-light w-100 py-2 rounded-3 fw-semibold hover-lift">
                            Annuler et revenir
                        </a>

                    </div>
                </div>

                <!-- FICHE RECAP DE LA PRESTATION ORIGINALE -->
                <div class="card border-0 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                        <h6 class="fw-bold text-muted text-uppercase small mb-0">Historique Prestation</h6>
                    </div>
                    <div class="card-body p-4 small text-muted">
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>ID Prestation :</span>
                            <span class="fw-semibold text-dark">#{{ $prestation->id_prestation }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>Demande source :</span>
                            <span class="fw-semibold text-dark">
                                Demande #{{ $prestation->id_demande }}
                                @if($prestation->demande?->is_bon_commande)
                                    <span class="badge bg-info bg-opacity-10 text-info ms-1">Bon de Commande</span>
                                @endif
                            </span>
                        </div>
                        <div class="d-flex justify-content-between py-2 border-bottom">
                            <span>Date d'enregistrement :</span>
                            <span class="fw-semibold text-dark">{{ $prestation->created_at ? $prestation->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2">
                            <span>Dernière mise à jour :</span>
                            <span class="fw-semibold text-dark">{{ $prestation->updated_at ? $prestation->updated_at->format('d/m/Y H:i') : 'N/A' }}</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
// Catalogue des actes disponibles avec leurs taux et tarifs de référence
const AVAILABLE_ACTES = {!! json_encode($typesPrestation->map(function($t) {
    return [
        'id' => $t->id_type_prestation,
        'libelle' => $t->libelle,
        'taux' => (float)($t->parametreCouverture->taux_prise_charge ?? 75),
        'pu' => (float)($t->parametreCouverture->plafond_par_acte ?? 0),
    ];
})) !!};

// État des articles et initialisation
const initialArticles = @json(old('details_articles', $prestation->details_articles ?? []));

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function formatFCFA(val) {
    return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
}

function isQuantiteVisible() {
    const demandeSelect = document.getElementById('id_demande');
    if (!demandeSelect || !demandeSelect.value) return false;
    const selected = demandeSelect.options[demandeSelect.selectedIndex];
    if (!selected) return false;
    const isBon = selected.getAttribute('data-is-bon-commande') === '1';
    const docType = (selected.getAttribute('data-doc-type') || '').toLowerCase();
    
    // Si feuille de maladie ou lettre de garantie -> masquer quantité
    if (docType.includes('feuille') || docType.includes('malad') || docType.includes('garantie')) {
        return false;
    }
    return isBon || docType.includes('bon');
}

function updateArticlesVisibility() {
    const showQte = isQuantiteVisible();
    document.querySelectorAll('#articles_card .col-quantite').forEach(el => {
        el.style.display = showQte ? '' : 'none';
    });
    const totalLabel = document.getElementById('articles_total_label');
    if (totalLabel) {
        totalLabel.setAttribute('colspan', showQte ? 4 : 3);
    }
    // Si la quantité est masquée, fixer à 1
    if (!showQte) {
        document.querySelectorAll('#articles_tbody .article-qte').forEach(input => {
            input.value = 1;
        });
    }
}

function setupArticlesSection() {
    updateArticlesVisibility();

    if (Array.isArray(initialArticles) && initialArticles.length > 0) {
        initialArticles.forEach((art) => {
            addArticleRow({
                nom: art.nom || '',
                quantite: art.quantite || 1,
                montant: art.montant || 0,
                id_type_prestation: art.id_type_prestation || undefined
            });
        });
    } else {
        addArticleRow({ nom: '', quantite: 1, montant: 0 });
    }
    updateArticleCalculations();
}

function handleDemandeChange() {
    const demandeSelect = document.getElementById('id_demande');
    if (!demandeSelect) return;

    const val = demandeSelect.value;
    let selected = demandeSelect.querySelector(`option[value="${val}"]`);
    if (!selected && demandeSelect.selectedIndex >= 0) {
        selected = demandeSelect.options[demandeSelect.selectedIndex];
    }
    if (!selected || !selected.value) {
        const pratSelect = document.getElementById('id_praticien');
        if (pratSelect) pratSelect.value = '';
        const pharmSelect = document.getElementById('id_pharmacie');
        if (pharmSelect) pharmSelect.value = '';
        const badgePrat = document.getElementById('badge_prat');
        if (badgePrat) badgePrat.style.display = 'none';
        const badgePharm = document.getElementById('badge_pharm');
        if (badgePharm) badgePharm.style.display = 'none';
        updateArticlesVisibility();
        return;
    }

    const pratId = selected.getAttribute('data-praticien-id') || '';
    const pharmId = selected.getAttribute('data-pharmacie-id') || '';

    // Auto-remplissage Praticien / Clinique
    const pratSelect = document.getElementById('id_praticien');
    const badgePrat = document.getElementById('badge_prat');
    if (pratSelect) {
        pratSelect.value = pratId;
        if (pratId && pratSelect.value == pratId) {
            if (badgePrat) badgePrat.style.display = 'inline-block';
            pratSelect.style.transition = 'all 0.35s ease';
            pratSelect.style.backgroundColor = '#ecfdf5';
            pratSelect.style.borderColor = '#10b981';
            setTimeout(() => {
                pratSelect.style.backgroundColor = '';
                pratSelect.style.borderColor = '';
            }, 600);
        } else {
            if (badgePrat) badgePrat.style.display = 'none';
        }
    }

    // Auto-remplissage Pharmacie conventionnée
    const pharmSelect = document.getElementById('id_pharmacie');
    const badgePharm = document.getElementById('badge_pharm');
    if (pharmSelect) {
        pharmSelect.value = pharmId;
        if (pharmId && pharmSelect.value == pharmId) {
            if (badgePharm) badgePharm.style.display = 'inline-block';
            pharmSelect.style.transition = 'all 0.35s ease';
            pharmSelect.style.backgroundColor = '#ecfdf5';
            pharmSelect.style.borderColor = '#10b981';
            setTimeout(() => {
                pharmSelect.style.backgroundColor = '';
                pharmSelect.style.borderColor = '';
            }, 600);
        } else {
            if (badgePharm) badgePharm.style.display = 'none';
        }
    }

    updateArticlesVisibility();

    const currentRows = document.querySelectorAll('#articles_tbody tr').length;
    if (currentRows === 0) {
        addArticleRow({ nom: '', quantite: 1, montant: 0 });
    }
    updateArticleCalculations();
}

/**
 * Gère le choix d'un acte dans le sélecteur d'une ligne d'édition
 * et auto-remplit immédiatement le Taux (%) principal de la prestation.
 */
function handleArticleActeSelect(selectEl) {
    const tr = selectEl.closest('tr');
    if (!tr) return;

    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;

    const acteLibelle = selectedOpt.value;
    const acteTaux = parseFloat(selectedOpt.getAttribute('data-taux'));
    const acteId = selectedOpt.getAttribute('data-id');

    // Auto-remplissage du champ préciser l'acte à chaque changement du select
    const nomInput = tr.querySelector('.article-nom');
    if (nomInput) {
        nomInput.value = acteLibelle;
        nomInput.style.transition = 'background-color 0.3s ease';
        nomInput.style.backgroundColor = '#f0fdf4';
        setTimeout(() => {
            nomInput.style.backgroundColor = '';
        }, 400);
    }

    // Auto-remplir le champ Taux (%) principal de la prestation
    const tauxInput = document.getElementById('taux_input');
    if (tauxInput && !isNaN(acteTaux)) {
        tauxInput.value = acteTaux;
        // Animation de pulsation
        tauxInput.style.transition = 'all 0.35s ease';
        tauxInput.style.backgroundColor = '#dbeafe';
        tauxInput.style.color = '#1e40af';
        tauxInput.style.transform = 'scale(1.05)';
        setTimeout(() => {
            tauxInput.style.backgroundColor = '';
            tauxInput.style.color = '';
            tauxInput.style.transform = '';
        }, 500);
    }

    // Mettre à jour l'ID du type de prestation
    const idTypeInput = tr.querySelector('.article-id-type');
    if (idTypeInput && acteId) {
        idTypeInput.value = acteId;
    }

    updateArticleCalculations();
}

function addArticleRow(data = {}) {
    const tbody = document.getElementById('articles_tbody');
    const rowIndex = tbody.querySelectorAll('tr').length;
    const showQte = isQuantiteVisible();
    const qteVal = (!showQte || data.quantite === undefined || data.quantite === null || data.quantite === '') ? 1 : data.quantite;

    // Déterminer l'acte correspondant si fourni
    let matchedActe = null;
    if (data.id_type_prestation) {
        matchedActe = AVAILABLE_ACTES.find(a => a.id === parseInt(data.id_type_prestation));
    }
    if (!matchedActe && data.nom) {
        const lowerNom = data.nom.toLowerCase().trim();
        matchedActe = AVAILABLE_ACTES.find(a => 
            a.libelle.toLowerCase().trim() === lowerNom ||
            lowerNom.includes(a.libelle.toLowerCase()) ||
            a.libelle.toLowerCase().includes(lowerNom)
        );
    }

    if (matchedActe && matchedActe.taux) {
        const mainTaux = document.getElementById('taux_input');
        if (mainTaux && (!mainTaux.value || mainTaux.value == '75')) {
            mainTaux.value = matchedActe.taux;
        }
    }

    const montantVal = (data.montant !== undefined && data.montant !== null && data.montant !== '')
        ? data.montant
        : 0;

    const idTypeVal = data.id_type_prestation || (matchedActe ? matchedActe.id : '');
    const tr = document.createElement('tr');

    const optionsHtml = AVAILABLE_ACTES.map(a => {
        const isSelected = matchedActe && matchedActe.id === a.id;
        return `<option value="${escapeHtml(a.libelle)}" data-taux="${a.taux}" data-id="${a.id}" ${isSelected ? 'selected' : ''}>${escapeHtml(a.libelle)}</option>`;
    }).join('');

    tr.className = 'article-row';
    tr.innerHTML = `
        <td class="ps-3 fw-bold text-muted row-index">${rowIndex + 1}</td>
        <td>
            <div class="input-group input-group-sm">
                <select class="form-select form-select-sm article-acte-select" 
                        style="max-width: 180px; font-weight: 500; background-color: #f8fafc;" 
                        onchange="handleArticleActeSelect(this)" 
                        title="Sélectionner l'acte médical pour auto-remplir le champ Taux (%)">
                    <option value="">-- Choisir un acte --</option>
                    ${optionsHtml}
                </select>
                <input type="text" 
                       name="details_articles[${rowIndex}][nom]" 
                       class="form-control form-control-sm article-nom" 
                       placeholder="Préciser l'acte ou médicament..." 
                       value="${escapeHtml(data.nom || '')}" 
                       required>
                <input type="hidden" 
                       name="details_articles[${rowIndex}][id_type_prestation]" 
                       class="article-id-type" 
                       value="${idTypeVal}">
            </div>
        </td>
        <td class="text-center col-quantite" style="width: 130px; ${showQte ? '' : 'display: none;'}">
            <input type="number" 
                   step="1" 
                   min="1" 
                   name="details_articles[${rowIndex}][quantite]" 
                   class="form-control form-control-sm text-center fw-bold fs-6 article-qte shadow-sm" 
                   style="background-color: #eff6ff; color: #1e40af; border: 2px solid #3b82f6; width: 85px; margin: 0 auto; display: block;" 
                   value="${qteVal}" 
                   required 
                   oninput="updateArticleCalculations()">
        </td>
        <td style="width: 220px;">
            <div class="input-group input-group-sm">
                <input type="number" 
                       step="1" 
                       min="0" 
                       name="details_articles[${rowIndex}][montant]" 
                       class="form-control form-control-sm article-pu fw-bold text-end fs-6" 
                       placeholder="0" 
                       value="${montantVal}" 
                       required 
                       oninput="updateArticleCalculations()">
                <span class="input-group-text bg-light text-muted fw-semibold">FCFA</span>
            </div>
        </td>
        <td class="text-end pe-3 fw-bold text-dark article-line-total">
            0 FCFA
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 rounded-circle" onclick="removeArticleRow(this)" title="Supprimer cet article">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;

    tbody.appendChild(tr);
    reindexArticles();
    updateArticleCalculations();
}

function removeArticleRow(btn) {
    const tr = btn.closest('tr');
    tr.remove();
    reindexArticles();
    updateArticleCalculations();
}

function reindexArticles() {
    const rows = document.querySelectorAll('#articles_tbody tr');
    rows.forEach((tr, index) => {
        const indexCell = tr.querySelector('.row-index');
        if (indexCell) indexCell.textContent = index + 1;

        const nomInput = tr.querySelector('.article-nom');
        if (nomInput) nomInput.name = `details_articles[${index}][nom]`;

        const idTypeInput = tr.querySelector('.article-id-type');
        if (idTypeInput) idTypeInput.name = `details_articles[${index}][id_type_prestation]`;

        const qteInput = tr.querySelector('.article-qte');
        if (qteInput) qteInput.name = `details_articles[${index}][quantite]`;

        const puInput = tr.querySelector('.article-pu');
        if (puInput) puInput.name = `details_articles[${index}][montant]`;
    });
}

function updateArticleCalculations() {
    let totalSum = 0;
    const showQte = isQuantiteVisible();
    const rows = document.querySelectorAll('#articles_tbody tr');

    rows.forEach(tr => {
        const qte = showQte ? (parseFloat(tr.querySelector('.article-qte')?.value) || 1) : 1;
        const pu = parseFloat(tr.querySelector('.article-pu')?.value) || 0;
        const lineTotal = Math.round(qte * pu);

        totalSum += lineTotal;

        const lineDisplay = tr.querySelector('.article-line-total');
        if (lineDisplay) {
            lineDisplay.textContent = formatFCFA(lineTotal);
        }
    });

    const sumDisplay = document.getElementById('articles_sum_display');
    if (sumDisplay) sumDisplay.textContent = formatFCFA(totalSum);

    if (totalSum > 0) {
        document.getElementById('montant_input').value = totalSum;
    }

    recalculerFinances();
}

function recalculerFinances() {
    const montant = parseFloat(document.getElementById('montant_input').value) || 0;
    const taux = parseFloat(document.getElementById('taux_input').value) || 0;

    const partIpm = Math.round((montant * taux) / 100);
    const resteCharge = Math.max(0, montant - partIpm);

    document.getElementById('calc_total_display').textContent = formatFCFA(montant);
    document.getElementById('calc_pec_display').textContent = formatFCFA(partIpm);
    document.getElementById('calc_reste_display').textContent = formatFCFA(resteCharge);

    document.getElementById('calc_taux_badge').textContent = taux + '%';
    document.getElementById('calc_reste_badge').textContent = (100 - taux) + '%';
}

document.addEventListener("DOMContentLoaded", function() {
    const selectEl = document.getElementById('id_demande');
    if (selectEl) {
        const ts = new TomSelect('#id_demande', {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
        ts.on('change', function() {
            handleDemandeChange();
        });
    }
    setupArticlesSection();
    recalculerFinances();
});
</script>
@endsection

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
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="h3 fw-bold text-dark mb-0">
            <i class="bi bi-receipt text-primary me-2"></i>Facturation des Prestations Médicales
        </h2>
        <p class="text-muted small mb-0 mt-1">Enregistrement, édition et contrôle des plafonds de prise en charge par acte et par bénéficiaire.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success shadow-sm rounded-3" data-bs-toggle="modal" data-bs-target="#exportExcelModal">
            <i class="bi bi-file-earmark-excel me-1"></i> Exporter XLSX
        </button>
        @canedit
        <button class="btn btn-primary shadow-sm rounded-3 fw-bold" data-bs-toggle="modal" data-bs-target="#createPrestationModal">
            <i class="bi bi-plus-circle me-1"></i> Saisir une prestation
        </button>
        @endcanedit
    </div>
</div>

<!-- KPIs Dashboard Prestations -->
@if(isset($stats))
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary me-3">
                    <i class="bi bi-receipt fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Actes</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_prestations'] }}</h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-dark bg-opacity-10 p-3 text-dark me-3">
                    <i class="bi bi-cash-stack fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Total Facturé</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['montant_total'], 0, ',', ' ') }} <small class="fs-6 text-muted">FCFA</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success me-3">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Prise en Charge IPM</span>
                    <h4 class="fw-bold mb-0 text-success">{{ number_format($stats['part_ipm'], 0, ',', ' ') }} <small class="fs-6 text-muted">FCFA</small></h4>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger me-3">
                    <i class="bi bi-person-fill-exclamation fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">Reste à Charge Patient</span>
                    <h4 class="fw-bold mb-0 text-danger">{{ number_format($stats['reste_a_charge'], 0, ',', ' ') }} <small class="fs-6 text-muted">FCFA</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Filtres et Recherche -->
<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('prestations.index') }}" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique (bénéficiaire, matricule, type d'acte, partenaire)..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-4">
                <select name="id_type_prestation" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">Tous les types d'actes ({{ $typesPrestation->count() }})</option>
                    @foreach($groupedTypes as $groupLabel => $items)
                        <optgroup label="{{ $groupLabel }}">
                            @foreach($items as $tp)
                                <option value="{{ $tp->id_type_prestation }}" {{ request('id_type_prestation') == $tp->id_type_prestation ? 'selected' : '' }}>
                                    {{ $tp->libelle }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-center justify-content-end">
                <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                <select name="per_page" id="per_page" class="form-select bg-light border-0" style="width: 100px;" onchange="this.form.submit()">
                    <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5 / page</option>
                    <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50 / page</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Table des Prestations -->
<div class="card shadow-sm border-0 rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Réf / Date</th>
                        <th>Type d'Acte</th>
                        <th>Bénéficiaire des soins</th>
                        <th>Partenaire de Santé</th>
                        <th class="text-end">Montant Total</th>
                        <th class="text-end text-success">Prise en Charge (IPM)</th>
                        <th class="text-end text-danger">Reste à Charge</th>
                        <th class="text-center">Statut Facturation</th>
                        <th class="text-center pe-4" style="width: 130px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestations as $p)
                    @php
                        $beneficiaireLabel = '-';
                        if($p->demande) {
                            if($p->demande->ayantDroit) {
                                $beneficiaireLabel = '<span class="fw-semibold text-dark">' . $p->demande->ayantDroit->prenom . ' ' . $p->demande->ayantDroit->nom . '</span> <span class="badge bg-info bg-opacity-10 text-info ms-1 small">Ayant-droit</span>';
                            } elseif($p->demande->salarie) {
                                $beneficiaireLabel = '<span class="fw-semibold text-dark">' . $p->demande->salarie->prenom . ' ' . $p->demande->salarie->nom . '</span> <span class="badge bg-primary bg-opacity-10 text-primary ms-1 small">Participant</span>';
                            }
                        }
                        $partenaireNom = $p->praticien ? ('🩺 ' . ($p->praticien->nom ?? $p->praticien->NOMPRAT)) : ($p->pharmacie ? ('💊 ' . ($p->pharmacie->nom ?? $p->pharmacie->NOMPHARM)) : '<span class="text-muted">Non assigné</span>');
                        $pec = $p->montant - $p->reste_a_charge;
                    @endphp
                    <tr>
                        <td class="ps-4 text-nowrap">
                            <div class="fw-bold text-dark">#{{ $p->id_prestation }}</div>
                            <small class="text-muted">
                                <i class="bi bi-calendar-event me-1"></i>{{ $p->date_prestation ? $p->date_prestation->format('d/m/Y') : '-' }}
                            </small>
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 rounded-pill">
                                {{ $p->typePrestation->libelle ?? 'Non défini' }}
                            </span>
                            @if(!empty($p->details_articles) && is_array($p->details_articles) && count($p->details_articles) > 0)
                                <div class="mt-1">
                                    <span class="badge bg-light text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 small" 
                                          title="@foreach($p->details_articles as $art) {{ $art['nom'] ?? '' }} ({{ $art['quantite'] ?? 1 }}x {{ number_format($art['montant'] ?? 0, 0, ',', ' ') }} F)&#10;@endforeach">
                                        <i class="bi bi-capsule me-1"></i>{{ count($p->details_articles) }} article(s)
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>{!! $beneficiaireLabel !!}</td>
                        <td>{!! $partenaireNom !!}</td>
                        <td class="text-end fw-bold text-dark">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="text-end text-success fw-bold">
                            {{ number_format($pec, 0, ',', ' ') }} FCFA 
                            <span class="badge bg-success bg-opacity-10 text-success ms-1 small">{{ (float)$p->taux_prise_charge }}%</span>
                        </td>
                        <td class="text-end text-danger fw-bold">
                            {{ number_format($p->reste_a_charge, 0, ',', ' ') }} FCFA
                        </td>
                        <td class="text-center text-nowrap">
                            @php $facture = $p->factures->first(); @endphp
                            @if($facture)
                                @if($facture->statut_paiement == 'en_attente')
                                    <a href="{{ route('factures.show', $facture->id_facture) }}" class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle text-decoration-none px-2 py-1 rounded-pill" title="Voir la facture #{{ $facture->numero_facture }}">
                                        <i class="bi bi-clock-history"></i> En attente
                                    </a>
                                @elseif($facture->statut_paiement == 'partiellement_payee')
                                    <a href="{{ route('factures.show', $facture->id_facture) }}" class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle text-decoration-none px-2 py-1 rounded-pill" title="Voir la facture #{{ $facture->numero_facture }}">
                                        <i class="bi bi-pie-chart"></i> Partiel
                                    </a>
                                @else
                                    <a href="{{ route('factures.show', $facture->id_facture) }}" class="badge bg-success bg-opacity-10 text-success border border-success-subtle text-decoration-none px-2 py-1 rounded-pill" title="Voir la facture #{{ $facture->numero_facture }}">
                                        <i class="bi bi-check-circle"></i> Soldée
                                    </a>
                                @endif
                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">Fact. {{ $facture->numero_facture }}</div>
                            @else
                                <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">
                                    <i class="bi bi-hourglass"></i> Non facturée
                                </span>
                            @endif
                        </td>
                        <td class="text-center pe-4 text-nowrap">
                            @canedit
                            <!-- BOUTON ÉDITION OPTIMISÉ -->
                            <a href="{{ route('prestations.edit', $p) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 me-1" title="Modifier la prestation">
                                <i class="bi bi-pencil-square"></i> Modifier
                            </a>
                            <!-- BOUTON SUPPRESSION -->
                            <form action="{{ route('prestations.destroy', $p) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Confirmez-vous la suppression définitive de la prestation #{{ $p->id_prestation }} ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-muted small">-</span>
                            @endcanedit
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-file-medical fs-1 d-block mb-3 opacity-50"></i>
                            Aucune prestation enregistrée pour les filtres sélectionnés.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap justify-content-between align-items-center mt-4 gap-2">
    <div class="text-muted small">
        Affichage de {{ $prestations->firstItem() ?? 0 }} à {{ $prestations->lastItem() ?? 0 }} sur {{ $prestations->total() }} prestations
    </div>
    <div>
        {{ $prestations->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('modals')
@canedit
<!-- Modal Création Prestation avec Calculateur en Temps Réel -->
<div class="modal fade" id="createPrestationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold text-dark mb-1">
                        <i class="bi bi-plus-circle-fill text-primary me-2"></i>Nouvelle Prestation Médicale
                    </h5>
                    <p class="text-muted small mb-0">Saisie des actes et articles avec calcul financier en direct.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('prestations.store') }}" method="POST" id="createPrestationForm">
                @csrf
                <div class="modal-body px-4 py-3">
                    
                    @if(session('error'))
                    <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-0 rounded-3 mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    </div>
                    @endif

                    <div class="row g-3">
                        <!-- Prise en charge validée source (Demande) -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="bi bi-shield-check text-success me-1"></i>Prise en charge source validée (Demande) *
                            </label>
                            <select name="id_demande" id="modal_id_demande" class="form-select form-select-lg" required onchange="handleModalDemandeChange()">
                                <option value="">-- Sélectionner une prise en charge validée --</option>
                                @foreach($demandes as $d)
                                    @php
                                        $benef = $d->ayantDroit ? ($d->ayantDroit->nom . ' ' . $d->ayantDroit->prenom . ' (Ayant-droit)') : ($d->salarie->nom . ' ' . $d->salarie->prenom . ' - Participant');
                                        $docType = $d->typeDemande->libelle ?? 'Prise en charge';
                                        $choixActe = $d->lettreGarantie->choix_acte ?? '';
                                    @endphp
                                    <option value="{{ $d->id_demande }}" 
                                            data-prat="{{ $d->id_praticien }}" 
                                            data-pharm="{{ $d->id_pharmacie }}"
                                            data-is-bon="{{ $d->is_bon_commande ? '1' : '0' }}"
                                            data-doc-type="{{ strtolower($docType) }}"
                                            data-choix-acte="{{ $choixActe }}"
                                            data-articles-count="{{ $d->nombre_articles }}"
                                            {{ old('id_demande') == $d->id_demande ? 'selected' : '' }}>
                                        Demande #{{ $d->id_demande }}{{ $d->numero_demande ? ' (' . $d->numero_demande . ')' : '' }} ({{ $docType }}) — {{ $benef }}{{ $choixActe ? ' [' . $choixActe . ']' : '' }} — [{{ ucfirst($d->statut) }}]
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Seules les prises en charge validées / approuvées sont éligibles pour l'imputation d'une prestation.</div>
                        </div>

                        <!-- Date de l'acte -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">Date de l'acte *</label>
                            <input type="date" name="date_prestation" class="form-control" value="{{ old('date_prestation', date('Y-m-d')) }}" required>
                        </div>

                        <!-- Prestataires -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                🩺 Praticien / Clinique
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 ms-1 fw-normal" id="modal_badge_prat" style="display: none; font-size: 0.72rem;">
                                    Auto-rempli
                                </span>
                            </label>
                            <select name="id_praticien" id="modal_id_praticien" class="form-select">
                                <option value="">-- Aucun médecin --</option>
                                @foreach($praticiens as $prat)
                                    <option value="{{ $prat->id_praticien ?? $prat->PRCLEUNIK }}" {{ old('id_praticien') == ($prat->id_praticien ?? $prat->PRCLEUNIK) ? 'selected' : '' }}>
                                        🩺 {{ $prat->nom ?? $prat->NOMPRAT }} {{ $prat->specialite ? '('.$prat->specialite.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                💊 Pharmacie conventionnée
                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 ms-1 fw-normal" id="modal_badge_pharm" style="display: none; font-size: 0.72rem;">
                                    Auto-remplie
                                </span>
                            </label>
                            <select name="id_pharmacie" id="modal_id_pharmacie" class="form-select">
                                <option value="">-- Aucune pharmacie --</option>
                                @foreach($pharmacies as $pharm)
                                    <option value="{{ $pharm->id_pharmacie ?? $pharm->PHCLEUNIK }}" {{ old('id_pharmacie') == ($pharm->id_pharmacie ?? $pharm->PHCLEUNIK) ? 'selected' : '' }}>
                                        💊 {{ $pharm->nom ?? $pharm->NOMPHARM }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Section Actes / Articles dans le modal -->
                        <div class="col-12" id="modal_articles_container">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <span class="fw-bold text-dark small">
                                            <i class="bi bi-list-task text-primary me-1"></i> Détail des Actes / Articles
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info ms-2" id="modal_articles_quota_badge">1 acte(s) / article(s)</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2" onclick="addModalArticleRow()">
                                        <i class="bi bi-plus"></i> Ajouter ligne
                                    </button>
                                </div>
                                <div class="table-responsive rounded-2 border bg-white mb-2">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light text-muted small">
                                            <tr>
                                                <th class="ps-2" style="width: 25px;">#</th>
                                                <th style="min-width: 290px;">
                                                    <i class="bi bi-ui-checks text-primary me-1"></i>Acte / Médicament <span class="text-danger">*</span>
                                                </th>
                                                <th style="width: 120px;" class="text-center bg-primary bg-opacity-10 text-primary fw-bold col-quantite">
                                                    <i class="bi bi-stack me-1"></i>Quantité <span class="text-danger">*</span>
                                                </th>
                                                <th style="width: 210px;">P.U (FCFA) <span class="text-danger">*</span></th>
                                                <th class="text-end pe-2" style="width: 135px;">Total Ligne</th>
                                                <th style="width: 35px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="modal_articles_tbody">
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center text-muted small">
                                    <span>Montant facturé synchronisé en direct</span>
                                    <span class="fw-bold text-primary fs-6" id="modal_articles_total_display">0 FCFA</span>
                                </div>
                            </div>
                        </div>

                        <!-- Montants & Taux -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Montant Facturé (FCFA) *</label>
                            <div class="input-group">
                                <input type="number" step="1" min="0" name="montant" id="modal_montant" class="form-control fw-bold" value="{{ old('montant') }}" required oninput="recalculerModalFinances()">
                                <span class="input-group-text bg-light text-muted fw-bold">FCFA</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Taux Prise en charge (%) *</label>
                            <div class="input-group">
                                <input type="number" step="0.1" min="0" max="100" name="taux_prise_charge" id="modal_taux" class="form-control fw-bold text-primary" value="{{ old('taux_prise_charge', 75) }}" required oninput="recalculerModalFinances()">
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                            <div class="form-text small text-muted">Auto-rempli selon l'acte sélectionné ci-dessus ou modifiable manuellement.</div>
                        </div>

                        <!-- Aperçu Financier Direct -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <span class="text-muted small d-block">Prise en Charge IPM :</span>
                                    <span class="fw-bold text-success fs-5" id="modal_calc_pec">0 FCFA</span>
                                </div>
                                <div class="border-start ps-3">
                                    <span class="text-muted small d-block">Reste à Charge Adhérent :</span>
                                    <span class="fw-bold text-danger fs-5" id="modal_calc_reste">0 FCFA</span>
                                </div>
                                <div class="border-start ps-3">
                                    <span class="text-muted small d-block">Statut Prise en Charge :</span>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill" id="modal_calc_status">
                                        <i class="bi bi-shield-check me-1"></i>Prise en charge active
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Enregistrer la prestation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcanedit

<!-- Modal Export Excel -->
<div class="modal fade" id="exportExcelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-success">
                    <i class="bi bi-file-earmark-excel me-2"></i>Export Comptable Prestations
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('prestations.export') }}" method="GET">
                <div class="modal-body px-4 py-3">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-medium text-muted small mb-1">Date de début</label>
                            <input type="date" name="date_debut" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-medium text-muted small mb-1">Date de fin</label>
                            <input type="date" name="date_fin" class="form-control">
                        </div>
                    </div>
                    <div class="form-text text-muted small">Laissez les dates vides pour exporter l'intégralité des prestations enregistrées.</div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 rounded-3 fw-bold">
                        <i class="bi bi-download me-1"></i> Télécharger XLSX
                    </button>
                </div>
            </form>
        </div>
    </div>
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

let modalArticlesQuota = 1;

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function isQuantiteVisible() {
    const select = document.getElementById('modal_id_demande');
    if (!select || !select.value) return false;
    const opt = select.options[select.selectedIndex];
    if (!opt) return false;
    const isBon = opt.getAttribute('data-is-bon') === '1';
    const docType = (opt.getAttribute('data-doc-type') || '').toLowerCase();
    
    // Si feuille de maladie ou lettre de garantie -> masquer quantité
    if (docType.includes('feuille') || docType.includes('malad') || docType.includes('garantie')) {
        return false;
    }
    return isBon || docType.includes('bon');
}

function updateQuantiteVisibility() {
    const showQte = isQuantiteVisible();
    document.querySelectorAll('#modal_articles_container .col-quantite').forEach(el => {
        el.style.display = showQte ? '' : 'none';
    });
    // Si la quantité est masquée (feuille de maladie / lettre de garantie), fixer la valeur à 1
    if (!showQte) {
        document.querySelectorAll('#modal_articles_tbody .modal-art-qte').forEach(input => {
            input.value = 1;
        });
    }
}

function handleModalDemandeChange() {
    const select = document.getElementById('modal_id_demande');
    if (!select) return;

    const val = select.value;
    let opt = select.querySelector(`option[value="${val}"]`);
    if (!opt && select.selectedIndex >= 0) {
        opt = select.options[select.selectedIndex];
    }
    if (!opt || !opt.value) {
        const pratSelect = document.getElementById('modal_id_praticien');
        if (pratSelect) pratSelect.value = '';
        const pharmSelect = document.getElementById('modal_id_pharmacie');
        if (pharmSelect) pharmSelect.value = '';
        const badgePrat = document.getElementById('modal_badge_prat');
        if (badgePrat) badgePrat.style.display = 'none';
        const badgePharm = document.getElementById('modal_badge_pharm');
        if (badgePharm) badgePharm.style.display = 'none';
        updateQuantiteVisibility();
        return;
    }

    const pratId = opt.getAttribute('data-prat') || '';
    const pharmId = opt.getAttribute('data-pharm') || '';
    const isBon = opt.getAttribute('data-is-bon') === '1';
    const docType = (opt.getAttribute('data-doc-type') || '').toLowerCase();
    const choixActe = (opt.getAttribute('data-choix-acte') || '').trim();

    // Auto-remplissage Praticien / Clinique
    const pratSelect = document.getElementById('modal_id_praticien');
    const badgePrat = document.getElementById('modal_badge_prat');
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
    const pharmSelect = document.getElementById('modal_id_pharmacie');
    const badgePharm = document.getElementById('modal_badge_pharm');
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

    modalArticlesQuota = parseInt(opt.getAttribute('data-articles-count')) || 1;
    const badge = document.getElementById('modal_articles_quota_badge');

    if (badge) {
        if (isBon) {
            badge.textContent = `${modalArticlesQuota} article(s) prescrit(s)`;
        } else if (docType.includes('feuille')) {
            badge.textContent = 'Feuille de maladie (Consultation / Soins)';
        } else if (docType.includes('garantie')) {
            badge.textContent = choixActe ? `Lettre de garantie (${choixActe})` : 'Lettre de garantie';
        } else {
            badge.textContent = 'Détail des actes';
        }
    }

    // Basculer la visibilité de la colonne Quantité selon le type de demande
    updateQuantiteVisibility();

    const tbody = document.getElementById('modal_articles_tbody');
    if (tbody.querySelectorAll('tr').length === 0) {
        let rowsToCreate = isBon ? modalArticlesQuota : 1;
        for (let i = 0; i < rowsToCreate; i++) {
            let initialNom = '';
            let initialTaux = 75;
            let initialIdType = '';
            let initialMontant = 0;

            if (!isBon && choixActe && i === 0) {
                const matched = AVAILABLE_ACTES.find(a => 
                    a.libelle.toLowerCase().trim() === choixActe.toLowerCase().trim() ||
                    choixActe.toLowerCase().includes(a.libelle.toLowerCase()) ||
                    a.libelle.toLowerCase().includes(choixActe.toLowerCase())
                );
                if (matched) {
                    initialNom = matched.libelle;
                    initialTaux = matched.taux;
                    initialIdType = matched.id;
                    initialMontant = 0;
                } else {
                    initialNom = choixActe;
                }
            } else if (docType.includes('feuille') && i === 0) {
                const medGen = AVAILABLE_ACTES.find(a => a.libelle.toLowerCase().includes('générale') || a.libelle.toLowerCase().includes('generale'));
                if (medGen) {
                    initialNom = medGen.libelle;
                    initialTaux = medGen.taux;
                    initialIdType = medGen.id;
                } else {
                    initialNom = 'Médecine Générale';
                    initialTaux = 75;
                }
            } else if (isBon) {
                const pharma = AVAILABLE_ACTES.find(a => a.libelle.toLowerCase().includes('pharmacie'));
                if (pharma) {
                    initialTaux = pharma.taux;
                    initialIdType = pharma.id;
                }
            }
            addModalArticleRow({ 
                nom: initialNom, 
                quantite: 1, 
                montant: initialMontant, 
                taux: initialTaux,
                id_type_prestation: initialIdType 
            });
        }
    } else {
        recalculerModalArticles();
    }
}

/**
 * Gère le choix d'un acte dans le sélecteur d'une ligne
 * et auto-remplit immédiatement le champ Taux (%) de la prestation.
 */
function handleModalActeSelect(selectEl) {
    const tr = selectEl.closest('tr');
    if (!tr) return;

    const selectedOpt = selectEl.options[selectEl.selectedIndex];
    if (!selectedOpt || !selectedOpt.value) return;

    const acteLibelle = selectedOpt.value;
    const acteTaux = parseFloat(selectedOpt.getAttribute('data-taux'));
    const acteId = selectedOpt.getAttribute('data-id');

    // Auto-remplissage du champ préciser l'acte à chaque changement du select
    const nomInput = tr.querySelector('.modal-art-nom');
    if (nomInput) {
        nomInput.value = acteLibelle;
        nomInput.style.transition = 'background-color 0.3s ease';
        nomInput.style.backgroundColor = '#f0fdf4';
        setTimeout(() => {
            nomInput.style.backgroundColor = '';
        }, 400);
    }

    // Auto-remplir le champ Taux (%) de la prestation
    const modalTauxInput = document.getElementById('modal_taux');
    if (modalTauxInput && !isNaN(acteTaux)) {
        modalTauxInput.value = acteTaux;
        // Animation de pulsation pour mettre en évidence le taux auto-rempli
        modalTauxInput.style.transition = 'all 0.35s ease';
        modalTauxInput.style.backgroundColor = '#dbeafe';
        modalTauxInput.style.color = '#1e40af';
        modalTauxInput.style.transform = 'scale(1.05)';
        setTimeout(() => {
            modalTauxInput.style.backgroundColor = '';
            modalTauxInput.style.color = '';
            modalTauxInput.style.transform = '';
        }, 500);
    }

    // Mettre à jour l'ID du type de prestation
    const idTypeInput = tr.querySelector('.modal-art-id-type');
    if (idTypeInput && acteId) {
        idTypeInput.value = acteId;
    }

    recalculerModalArticles();
}

function addModalArticleRow(data = {}) {
    const tbody = document.getElementById('modal_articles_tbody');
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
        const modalTaux = document.getElementById('modal_taux');
        if (modalTaux && (!modalTaux.value || modalTaux.value == '75')) {
            modalTaux.value = matchedActe.taux;
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

    tr.innerHTML = `
        <td class="ps-2 fw-semibold text-muted modal-row-idx">${rowIndex + 1}</td>
        <td>
            <div class="input-group input-group-sm">
                <select class="form-select form-select-sm modal-art-acte-select" 
                        style="max-width: 180px; font-weight: 500; background-color: #f8fafc;" 
                        onchange="handleModalActeSelect(this)" 
                        title="Sélectionner l'acte médical pour auto-remplir le champ Taux (%)">
                    <option value="">-- Choisir un acte --</option>
                    ${optionsHtml}
                </select>
                <input type="text" 
                       name="details_articles[${rowIndex}][nom]" 
                       class="form-control form-control-sm modal-art-nom" 
                       placeholder="Préciser l'acte ou médicament..." 
                       value="${escapeHtml(data.nom || '')}" 
                       required>
                <input type="hidden" 
                       name="details_articles[${rowIndex}][id_type_prestation]" 
                       class="modal-art-id-type" 
                       value="${idTypeVal}">
            </div>
        </td>
        <td class="text-center col-quantite" style="width: 120px; ${showQte ? '' : 'display: none;'}">
            <input type="number" 
                   min="1" 
                   step="1" 
                   name="details_articles[${rowIndex}][quantite]" 
                   class="form-control form-control-sm text-center fw-bold fs-6 modal-art-qte shadow-sm" 
                   style="background-color: #eff6ff; color: #1e40af; border: 2px solid #3b82f6; width: 80px; margin: 0 auto; display: block;" 
                   value="${qteVal}" 
                   required 
                   oninput="recalculerModalArticles()">
        </td>
        <td style="width: 210px;">
            <div class="input-group input-group-sm">
                <input type="number" min="0" step="1" name="details_articles[${rowIndex}][montant]" class="form-control form-control-sm text-end modal-art-pu fw-bold fs-6" placeholder="0" value="${montantVal}" required oninput="recalculerModalArticles()">
                <span class="input-group-text bg-light text-muted fw-semibold">FCFA</span>
            </div>
        </td>
        <td class="text-end pe-2 fw-bold text-dark modal-art-total">0 FCFA</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0 text-decoration-none" onclick="removeModalArticleRow(this)" title="Supprimer">
                <i class="bi bi-x-circle"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    reindexModalArticles();
    recalculerModalArticles();
}

function removeModalArticleRow(btn) {
    btn.closest('tr').remove();
    reindexModalArticles();
    recalculerModalArticles();
}

function reindexModalArticles() {
    const rows = document.querySelectorAll('#modal_articles_tbody tr');
    rows.forEach((tr, idx) => {
        const idxCell = tr.querySelector('.modal-row-idx');
        if (idxCell) idxCell.textContent = idx + 1;

        const nom = tr.querySelector('.modal-art-nom');
        if (nom) nom.name = `details_articles[${idx}][nom]`;

        const idType = tr.querySelector('.modal-art-id-type');
        if (idType) idType.name = `details_articles[${idx}][id_type_prestation]`;

        const qte = tr.querySelector('.modal-art-qte');
        if (qte) qte.name = `details_articles[${idx}][quantite]`;

        const pu = tr.querySelector('.modal-art-pu');
        if (pu) pu.name = `details_articles[${idx}][montant]`;
    });
}

function recalculerModalArticles() {
    let sumTotal = 0;
    const showQte = isQuantiteVisible();
    const rows = document.querySelectorAll('#modal_articles_tbody tr');
    
    rows.forEach(tr => {
        const qte = showQte ? (parseFloat(tr.querySelector('.modal-art-qte')?.value) || 1) : 1;
        const pu = parseFloat(tr.querySelector('.modal-art-pu')?.value) || 0;
        
        const totalLigne = Math.round(qte * pu);
        sumTotal += totalLigne;
        
        const disp = tr.querySelector('.modal-art-total');
        if (disp) disp.textContent = new Intl.NumberFormat('fr-FR').format(totalLigne) + ' F';
    });

    const display = document.getElementById('modal_articles_total_display');
    if (display) display.textContent = new Intl.NumberFormat('fr-FR').format(sumTotal) + ' FCFA';

    if (sumTotal > 0) {
        document.getElementById('modal_montant').value = sumTotal;
    }
    recalculerModalFinances();
}

function recalculerModalFinances() {
    const montant = parseFloat(document.getElementById('modal_montant').value) || 0;
    const taux = parseFloat(document.getElementById('modal_taux').value) || 0;

    const partIpm = Math.round((montant * taux) / 100);
    const reste = Math.max(0, montant - partIpm);

    document.getElementById('modal_calc_pec').textContent = new Intl.NumberFormat('fr-FR').format(partIpm) + ' FCFA';
    document.getElementById('modal_calc_reste').textContent = new Intl.NumberFormat('fr-FR').format(reste) + ' FCFA';
}

document.addEventListener("DOMContentLoaded", function() {
    const selectEl = document.getElementById('modal_id_demande');
    if (selectEl) {
        const ts = new TomSelect('#modal_id_demande', {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });
        ts.on('change', function() {
            handleModalDemandeChange();
        });
    }
    // Si une demande est déjà sélectionnée au chargement
    handleModalDemandeChange();
});
</script>

@if(session('error') || request('create') == 1)
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById('createPrestationModal'));
        myModal.show();
    });
</script>
@endif
@endsection

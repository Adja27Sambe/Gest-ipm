@extends('layouts.app')

@section('title', 'Facturation — Pharmacie')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- En-tête -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Tableau de bord</a></li>
                    <li class="breadcrumb-item">Facturation</li>
                    <li class="breadcrumb-item active" aria-current="page">Pharmacie</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-capsule text-success"></i> Facturation Pharmacie
            </h1>
            <p class="text-muted small mb-0">Gestion et enregistrement des dispensations pharmaceutiques sur bons de commande.</p>
        </div>

        @canedit
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-success d-flex align-items-center gap-2 px-3 py-2 fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createPharmacieModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Saisie Dispensation Pharmacie</span>
            </button>
        </div>
        @endcanedit
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Cartes Statistiques Pharmacie -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Dispensations</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($stats['total_dispensations'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="rounded-4 bg-success bg-opacity-10 p-3 text-success">
                        <i class="bi bi-prescription fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Facturé</span>
                        <h3 class="fw-bold text-primary mt-1 mb-0">{{ number_format($stats['montant_total'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></h3>
                    </div>
                    <div class="rounded-4 bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="bi bi-receipt-cutoff fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Part IPM (Prise en charge)</span>
                        <h3 class="fw-bold text-success mt-1 mb-0">{{ number_format($stats['part_ipm'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></h3>
                    </div>
                    <div class="rounded-4 bg-success bg-opacity-10 p-3 text-success">
                        <i class="bi bi-shield-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Reste à charge</span>
                        <h3 class="fw-bold text-danger mt-1 mb-0">{{ number_format($stats['reste_a_charge'], 0, ',', ' ') }} <small class="fs-6 text-muted">F</small></h3>
                    </div>
                    <div class="rounded-4 bg-danger bg-opacity-10 p-3 text-danger">
                        <i class="bi bi-person-x fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('facturation.pharmacie') }}" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Rechercher participant, matricule, pharmacie..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="id_pharmacie" class="form-select">
                        <option value="">Toutes les pharmacies</option>
                        @foreach($pharmacies as $ph)
                            <option value="{{ $ph->id_pharmacie ?? $ph->PHCLEUNIK }}" {{ request('id_pharmacie') == ($ph->id_pharmacie ?? $ph->PHCLEUNIK) ? 'selected' : '' }}>
                                💊 {{ $ph->nom ?? $ph->NOMPHARM }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}" placeholder="Date début">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}" placeholder="Date fin">
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel"></i></button>
                    @if(request()->hasAny(['search', 'id_pharmacie', 'date_debut', 'date_fin']))
                        <a href="{{ route('facturation.pharmacie') }}" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des Dispensations -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Réf & Date</th>
                        <th>Bénéficiaire</th>
                        <th>Pharmacie</th>
                        <th>Détail / Articles</th>
                        <th class="text-end">Montant Total</th>
                        <th class="text-end">Part IPM</th>
                        <th class="text-end">Reste Participant</th>
                        <th class="text-center">Statut Facture</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestations as $p)
                        @php
                            $salarie = $p->demande?->salarie;
                            $ayantDroit = $p->demande?->ayantDroit;
                            $benefLabel = $ayantDroit ? ($ayantDroit->nom . ' ' . $ayantDroit->prenom . ' (Ayant-droit)') : ($salarie ? ($salarie->nom . ' ' . $salarie->prenom) : 'Participant');
                            $pec = max(0, $p->montant - $p->reste_a_charge);
                        @endphp
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <div class="fw-bold text-dark">#{{ $p->id_prestation }}</div>
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $p->date_prestation ? $p->date_prestation->format('d/m/Y') : '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $benefLabel }}</div>
                                @if($salarie)
                                    <small class="text-muted">Matr: <code>{{ $salarie->matricule }}</code></small>
                                @endif
                            </td>
                            <td>
                                <span class="fw-medium text-dark">
                                    💊 {{ $p->pharmacie->nom ?? $p->pharmacie->NOMPHARM ?? 'Pharmacie non spécifiée' }}
                                </span>
                            </td>
                            <td>
                                @if(!empty($p->details_articles) && is_array($p->details_articles))
                                    <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small" title="@foreach($p->details_articles as $art) {{ $art['nom'] ?? 'Produits' }} ({{ $art['quantite'] ?? 1 }}x {{ number_format($art['montant'] ?? 0, 0, ',', ' ') }} F)&#10;@endforeach">
                                        <i class="bi bi-capsule me-1 text-success"></i>{{ count($p->details_articles) }} article(s)
                                    </span>
                                @else
                                    <span class="text-muted small">Produits pharmaceutiques</span>
                                @endif
                                @if(!empty($p->sous_couvert) || !empty($p->demande?->sous_couvert))
                                    <div class="mt-1">
                                        <span class="badge bg-info bg-opacity-15 text-primary border border-info border-opacity-50 rounded-pill px-2 py-0 small fw-bold">
                                            <i class="bi bi-shield-fill-check me-1"></i>Sous couvert (100%)
                                        </span>
                                    </div>
                                @endif
                            </td>
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
                                    <a href="{{ route('factures.show', $facture->id_facture) }}" class="badge bg-success bg-opacity-10 text-success border border-success-subtle text-decoration-none px-2 py-1 rounded-pill">
                                        Facturé #{{ $facture->numero_facture }}
                                    </a>
                                @else
                                    <span class="badge bg-light text-secondary border px-2 py-1 rounded-pill">En attente facture</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-capsule fs-1 text-muted d-block mb-2"></i>
                                Aucune dispensation pharmaceutique enregistrée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($prestations->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $prestations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Saisie Dispensation Pharmacie -->
@canedit
<div class="modal fade" id="createPharmacieModal" tabindex="-1" aria-labelledby="createPharmacieModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-success d-flex align-items-center gap-2" id="createPharmacieModalLabel">
                    <i class="bi bi-capsule"></i> Enregistrer une Dispensation Pharmacie
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('facturation.pharmacie.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Bon de commande source -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                <i class="bi bi-file-earmark-medical text-success me-1"></i> Bon de commande validé *
                            </label>
                            <select name="id_demande" id="ph_demande_select" class="form-select form-select-lg" required onchange="handlePhDemandeChange()">
                                <option value="">-- Sélectionner un bon de commande validé --</option>
                                @foreach($bonsCommande as $b)
                                    @php
                                        $benef = $b->ayantDroit ? ($b->ayantDroit->nom . ' ' . $b->ayantDroit->prenom . ' - Ayant droit') : ($b->salarie->nom . ' ' . $b->salarie->prenom . ' - Participant');
                                    @endphp
                                    <option value="{{ $b->id_demande }}" 
                                            data-pharm="{{ $b->id_pharmacie }}"
                                            data-articles-count="{{ $b->nombre_articles }}"
                                            data-sous-couvert="{{ !empty($b->sous_couvert) ? '1' : '0' }}">
                                        Bon #{{ $b->bonCommande->numero_bon ?? $b->id_demande }} — {{ $benef }} — [{{ $b->pharmacie->nom ?? $b->pharmacie->NOMPHARM ?? 'Pharmacie conventionnée' }}]{{ !empty($b->sous_couvert) ? ' 🛡️ [SOUS COUVERT 100%]' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">Seuls les bons de commande de pharmacie approuvés sont éligibles.</div>
                        </div>

                        <!-- Date et Pharmacie -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Date de dispensation *</label>
                            <input type="date" name="date_prestation" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Pharmacie exécutante</label>
                            <select name="id_pharmacie" id="ph_pharmacie_select" class="form-select">
                                <option value="">-- Pharmacie conventionnée --</option>
                                @foreach($pharmacies as $ph)
                                    <option value="{{ $ph->id_pharmacie ?? $ph->PHCLEUNIK }}">
                                        💊 {{ $ph->nom ?? $ph->NOMPHARM }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Lignes de dispensation (désignation facultative) -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark small">
                                        <i class="bi bi-list-check text-success me-1"></i> Médicaments / Articles délivrés
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1 fw-normal">Désignation facultative</span>
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill py-0 px-2" onclick="addPhRow()">
                                        <i class="bi bi-plus"></i> Ajouter ligne
                                    </button>
                                </div>
                                <div class="table-responsive rounded-2 border bg-white mb-2">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light text-muted small">
                                            <tr>
                                                <th class="ps-2" style="width: 25px;">#</th>
                                                <th>Désignation (Optionnelle)</th>
                                                <th style="width: 100px;" class="text-center">Quantité</th>
                                                <th style="width: 180px;">Prix unitaire (FCFA) *</th>
                                                <th class="text-end pe-2" style="width: 140px;">Total</th>
                                                <th style="width: 35px;"></th>
                                            </tr>
                                        </thead>
                                        <tbody id="ph_articles_tbody">
                                            <!-- Rempli en JS -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="4" class="text-end fw-bold small">Total Dispensation :</th>
                                                <th class="text-end pe-2 fw-bold text-success" id="ph_total_display">0 FCFA</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Montant et Taux cachés / liés -->
                        <input type="hidden" name="montant" id="ph_montant_input" value="0">
                        <input type="hidden" name="taux_prise_charge" id="ph_taux_input" value="75">

                        <!-- Option Sous Couvert -->
                        <div class="col-12">
                            <div class="p-3 bg-white rounded-3 border d-flex align-items-center justify-content-between">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0 d-block cursor-pointer" for="ph_sous_couvert">
                                        <i class="bi bi-shield-fill-check text-primary me-1"></i> Option « Sous couvert » (100% IPM)
                                    </label>
                                    <small class="text-muted">Prise en charge intégrale à 100% par l'IPM (Part participant = 0 FCFA).</small>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input fs-4 cursor-pointer" type="checkbox" name="sous_couvert" value="1" id="ph_sous_couvert" onchange="recalculerPhFinances()">
                                </div>
                            </div>
                        </div>

                        <!-- Aperçu Financier Direct -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <span class="text-muted small d-block">Prise en Charge IPM :</span>
                                    <span class="fw-bold text-success fs-5" id="ph_calc_pec">0 FCFA</span>
                                </div>
                                <div class="border-start ps-3">
                                    <span class="text-muted small d-block">Reste à Charge Adhérent :</span>
                                    <span class="fw-bold text-danger fs-5" id="ph_calc_reste">0 FCFA</span>
                                </div>
                                <div class="border-start ps-3">
                                    <span class="text-muted small d-block">Régime de couverture :</span>
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill" id="ph_status_badge">
                                        <i class="bi bi-shield-check me-1"></i>Taux standard (75%)
                                    </span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold rounded-3 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Valider la dispensation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let phRowIdx = 0;

function addPhRow(nom = '', qte = 1, montant = 0) {
    const tbody = document.getElementById('ph_articles_tbody');
    const tr = document.createElement('tr');
    tr.id = `ph_row_${phRowIdx}`;
    tr.innerHTML = `
        <td class="ps-2 fw-semibold text-muted ph-idx">${phRowIdx + 1}</td>
        <td>
            <input type="text" name="details_articles[${phRowIdx}][nom]" class="form-control form-control-sm" placeholder="Nom du médicament (optionnel)..." value="${nom}">
        </td>
        <td class="text-center">
            <input type="number" min="1" step="1" name="details_articles[${phRowIdx}][quantite]" class="form-control form-control-sm text-center fw-bold ph-qte" value="${qte}" oninput="recalculerPhArticles()">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" min="0" step="1" name="details_articles[${phRowIdx}][montant]" class="form-control form-control-sm text-end fw-bold ph-pu" placeholder="0" value="${montant}" required oninput="recalculerPhArticles()">
                <span class="input-group-text bg-light text-muted">F</span>
            </div>
        </td>
        <td class="text-end pe-2 fw-bold text-dark ph-row-total">0 F</td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger p-0" onclick="removePhRow(this)" title="Supprimer">
                <i class="bi bi-x-circle"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    phRowIdx++;
    recalculerPhArticles();
}

function removePhRow(btn) {
    const tr = btn.closest('tr');
    if (tr) {
        tr.remove();
        recalculerPhArticles();
    }
}

function recalculerPhArticles() {
    let total = 0;
    const rows = document.querySelectorAll('#ph_articles_tbody tr');
    rows.forEach((tr, index) => {
        const idxCell = tr.querySelector('.ph-idx');
        if (idxCell) idxCell.textContent = index + 1;
        const qte = parseFloat(tr.querySelector('.ph-qte')?.value) || 1;
        const pu = parseFloat(tr.querySelector('.ph-pu')?.value) || 0;
        const lineTotal = Math.round(qte * pu);
        total += lineTotal;
        const totalDisp = tr.querySelector('.ph-row-total');
        if (totalDisp) totalDisp.textContent = new Intl.NumberFormat('fr-FR').format(lineTotal) + ' F';
    });

    document.getElementById('ph_total_display').textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
    document.getElementById('ph_montant_input').value = total;
    recalculerPhFinances();
}

function recalculerPhFinances() {
    const montant = parseFloat(document.getElementById('ph_montant_input').value) || 0;
    const isSousCouvert = document.getElementById('ph_sous_couvert')?.checked;
    const badge = document.getElementById('ph_status_badge');

    let partIpm = 0;
    let reste = 0;

    if (isSousCouvert) {
        partIpm = montant;
        reste = 0;
        if (badge) {
            badge.className = 'badge bg-primary bg-opacity-15 text-primary fw-bold px-3 py-2 rounded-pill';
            badge.innerHTML = '<i class="bi bi-shield-fill-check me-1"></i>SOUS COUVERT (100% IPM)';
        }
    } else {
        const taux = parseFloat(document.getElementById('ph_taux_input').value) || 75;
        partIpm = Math.round((montant * taux) / 100);
        reste = Math.max(0, montant - partIpm);
        if (badge) {
            badge.className = 'badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill';
            badge.innerHTML = `<i class="bi bi-shield-check me-1"></i>Taux standard (${taux}%)`;
        }
    }

    document.getElementById('ph_calc_pec').textContent = new Intl.NumberFormat('fr-FR').format(partIpm) + ' FCFA';
    document.getElementById('ph_calc_reste').textContent = new Intl.NumberFormat('fr-FR').format(reste) + ' FCFA';
}

function handlePhDemandeChange() {
    const sel = document.getElementById('ph_demande_select');
    if (!sel || sel.selectedIndex < 0) return;
    const opt = sel.options[sel.selectedIndex];
    if (!opt || !opt.value) return;

    const pharmId = opt.getAttribute('data-pharm');
    if (pharmId) {
        const pharmSel = document.getElementById('ph_pharmacie_select');
        if (pharmSel) pharmSel.value = pharmId;
    }

    const isSousCouvert = opt.getAttribute('data-sous-couvert') === '1';
    const sc = document.getElementById('ph_sous_couvert');
    if (sc) sc.checked = isSousCouvert;

    const nbArticles = parseInt(opt.getAttribute('data-articles-count')) || 1;
    const tbody = document.getElementById('ph_articles_tbody');
    tbody.innerHTML = '';
    phRowIdx = 0;
    for (let i = 0; i < nbArticles; i++) {
        addPhRow('', 1, 0);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    // Si des lignes n'existent pas encore, initialiser avec une première ligne
    const tbody = document.getElementById('ph_articles_tbody');
    if (tbody && tbody.children.length === 0) {
        addPhRow('', 1, 0);
    }
});
</script>
@endcanedit
@endsection

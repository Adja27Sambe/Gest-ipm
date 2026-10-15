@extends('layouts.app')

@section('title', 'Facturation — Régularisation')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- En-tête -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Tableau de bord</a></li>
                    <li class="breadcrumb-item">Facturation</li>
                    <li class="breadcrumb-item active" aria-current="page">Régularisation</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-arrow-repeat text-warning"></i> Régularisations
            </h1>
            <p class="text-muted small mb-0">Gestion des dossiers et prises en charge à régulariser avec traçabilité complète.</p>
        </div>

        @canedit
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-warning text-dark d-flex align-items-center gap-2 px-3 py-2 fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#createRegularisationModal">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nouvelle Régularisation</span>
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

    <!-- Cartes Statistiques Régularisations -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Total Dossiers</span>
                        <h3 class="fw-bold text-dark mt-1 mb-0">{{ number_format($stats['total'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="rounded-4 bg-primary bg-opacity-10 p-3 text-primary">
                        <i class="bi bi-folder-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">À régulariser</span>
                        <h3 class="fw-bold text-warning mt-1 mb-0">{{ number_format($stats['a_regulariser'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="rounded-4 bg-warning bg-opacity-10 p-3 text-warning">
                        <i class="bi bi-hourglass-split fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Régularisés</span>
                        <h3 class="fw-bold text-success mt-1 mb-0">{{ number_format($stats['regularisees'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="rounded-4 bg-success bg-opacity-10 p-3 text-success">
                        <i class="bi bi-check2-all fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card border-0 rounded-4 shadow-sm h-100 p-3 bg-white">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Sous Couvert</span>
                        <h3 class="fw-bold text-info mt-1 mb-0">{{ number_format($stats['sous_couvert'], 0, ',', ' ') }}</h3>
                    </div>
                    <div class="rounded-4 bg-info bg-opacity-10 p-3 text-info">
                        <i class="bi bi-shield-fill-check fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres et Recherche -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('regularisations.index') }}" class="row g-2 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Rechercher participant, matricule, motif, situation..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="statut" class="form-select">
                        <option value="">Tous les statuts</option>
                        <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                        <option value="valide" {{ request('statut') == 'valide' ? 'selected' : '' }}>Régularisé / Validé</option>
                        <option value="rejete" {{ request('statut') == 'rejete' ? 'selected' : '' }}>Rejeté</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filtrer</button>
                    @if(request()->hasAny(['search', 'statut']))
                        <a href="{{ route('regularisations.index') }}" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Tableau des Régularisations -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-4">Dossier & Date</th>
                        <th>Participant / Bénéficiaire</th>
                        <th>Type & Prestataire</th>
                        <th>Situation Initiale</th>
                        <th>Motif Régularisation</th>
                        <th class="text-center">Régime</th>
                        <th class="text-center">Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regularisations as $r)
                        @php
                            $salarie = $r->salarie;
                            $ayantDroit = $r->ayantDroit;
                            $benefLabel = $ayantDroit ? ($ayantDroit->nom . ' ' . $ayantDroit->prenom . ' (Ayant-droit)') : ($salarie ? ($salarie->nom . ' ' . $salarie->prenom) : 'Participant');
                            $prestataire = $r->praticien ? ('🩺 ' . ($r->praticien->nom ?? $r->praticien->NOMPRAT)) : ($r->pharmacie ? ('💊 ' . ($r->pharmacie->nom ?? $r->pharmacie->NOMPHARM)) : 'Non spécifié');
                        @endphp
                        <tr>
                            <td class="ps-4 text-nowrap">
                                <div class="fw-bold text-dark">#{{ $r->id_demande }}{{ $r->numero_demande ? ' (' . $r->numero_demande . ')' : '' }}</div>
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>{{ $r->date_demande ? $r->date_demande->format('d/m/Y') : '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $benefLabel }}</div>
                                @if($salarie)
                                    <small class="text-muted">Matricule: <code>{{ $salarie->matricule }}</code></small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill small d-inline-block mb-1">
                                    {{ $r->typeDemande->libelle ?? 'Prise en charge' }}
                                </span>
                                <div class="small text-muted">{{ $prestataire }}</div>
                            </td>
                            <td style="max-width: 240px;">
                                <div class="text-truncate small text-secondary" title="{{ $r->situation_initiale }}">
                                    {{ $r->situation_initiale ?? 'Non renseignée' }}
                                </div>
                            </td>
                            <td style="max-width: 240px;">
                                <div class="text-truncate small text-dark fw-medium" title="{{ $r->motif_regularisation }}">
                                    {{ $r->motif_regularisation ?? $r->motif }}
                                </div>
                            </td>
                            <td class="text-center">
                                @if(!empty($r->sous_couvert))
                                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-bold small">
                                        <i class="bi bi-shield-fill-check me-1"></i>Sous couvert (100%)
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border px-2 py-1 rounded-pill small">Standard</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($r->statut === 'valide')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle me-1"></i>Régularisé
                                    </span>
                                @elseif($r->statut === 'en_attente')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1 rounded-pill">
                                        <i class="bi bi-hourglass-split me-1"></i>À régulariser
                                    </span>
                                @else
                                    <span class="badge bg-secondary px-2 py-1 rounded-pill">{{ ucfirst($r->statut) }}</span>
                                @endif
                            </td>
                            <td class="text-end pe-4 text-nowrap">
                                <a href="{{ route('regularisations.show', $r) }}" class="btn btn-sm btn-outline-primary rounded-3" title="Consulter la traçabilité complète">
                                    <i class="bi bi-eye me-1"></i> Traçabilité
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-arrow-repeat fs-1 text-muted d-block mb-2"></i>
                                Aucun dossier de régularisation enregistré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($regularisations->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $regularisations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Nouvelle Régularisation -->
@canedit
<div class="modal fade" id="createRegularisationModal" tabindex="-1" aria-labelledby="createRegularisationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="createRegularisationModalLabel">
                    <i class="bi bi-arrow-repeat text-warning"></i> Enregistrer une Régularisation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('regularisations.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning bg-warning bg-opacity-10 border-0 rounded-3 small p-3 mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i>
                        <strong>Traçabilité stricte :</strong> La régularisation crée une prise en charge rétroactive ou rectificative tout en consignant l'identité de l'opérateur, la date et l'heure, la situation initiale et le motif.
                    </div>

                    <div class="row g-3">
                        <!-- Recherche / Sélection Participant -->
                        <div class="col-md-7">
                            <label class="form-label fw-semibold text-dark small mb-1">Participant (Salarié) *</label>
                            <select name="id_salarie" id="reg_salarie_select" class="form-select" required onchange="handleRegSalarieChange(this)">
                                <option value="">-- Choisir un participant --</option>
                                @foreach($salaries as $s)
                                    <option value="{{ $s->id_salarie ?? $s->IDPARTICIPANT }}" data-ayants='@json($s->ayantsDroit)'>
                                        {{ $s->nom ?? $s->NOM }} {{ $s->prenom ?? $s->PRENOM }} (Matricule: {{ $s->matricule ?? $s->MATRICULE }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Bénéficiaire Ayant Droit -->
                        <div class="col-md-5">
                            <label class="form-label fw-semibold text-dark small mb-1">Bénéficiaire</label>
                            <select name="id_ayant_droit" id="reg_ayant_droit_select" class="form-select">
                                <option value="">Participant lui-même</option>
                            </select>
                        </div>

                        <!-- Type de Demande -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Type de prise en charge *</label>
                            <select name="id_type_demande" id="reg_type_demande" class="form-select" required onchange="handleRegTypeChange(this)">
                                @foreach($typesDemande as $td)
                                    <option value="{{ $td->id_type_demande }}">
                                        {{ $td->libelle }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small mb-1">Date de la prise en charge *</label>
                            <input type="date" name="date_demande" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <!-- Prestataires -->
                        <div class="col-md-6" id="reg_prat_wrapper">
                            <label class="form-label fw-semibold text-dark small mb-1">Praticien / Établissement</label>
                            <select name="id_praticien" class="form-select">
                                <option value="">-- Aucun médecin --</option>
                                @foreach($praticiens as $prat)
                                    <option value="{{ $prat->id_praticien ?? $prat->PRCLEUNIK }}">
                                        🩺 {{ $prat->nom ?? $prat->NOMPRAT }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6" id="reg_pharm_wrapper">
                            <label class="form-label fw-semibold text-dark small mb-1">Pharmacie conventionnée</label>
                            <select name="id_pharmacie" class="form-select">
                                <option value="">-- Aucune pharmacie --</option>
                                @foreach($pharmacies as $ph)
                                    <option value="{{ $ph->id_pharmacie ?? $ph->PHCLEUNIK }}">
                                        💊 {{ $ph->nom ?? $ph->NOMPHARM }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ordonnateur éventuel -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">Nom de l'ordonnateur (prescripteur)</label>
                            <input type="text" name="nom_ordonnateur" class="form-control" placeholder="Ex: Dr. Moussa Diop">
                        </div>

                        <!-- Situation Initiale -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Situation initiale constatée <span class="text-danger">*</span>
                            </label>
                            <textarea name="situation_initiale" class="form-control" rows="2" placeholder="Ex: Soins d'urgence reçus le 10/10 à l'Hôpital Principal sans bon préalable, montant 45 000 FCFA avec facture acquittée..." required>{{ old('situation_initiale') }}</textarea>
                            <div class="form-text small text-muted">Décrivez l'état de fait ayant motivé la régularisation a posteriori.</div>
                        </div>

                        <!-- Motif de la régularisation -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-dark small mb-1">
                                Motif de la régularisation <span class="text-danger">*</span>
                            </label>
                            <textarea name="motif_regularisation" class="form-control" rows="2" placeholder="Ex: Accord exceptionnel de la Direction Médicale de l'IPM pour prise en charge rétroactive suite à hospitalisation inopinée..." required>{{ old('motif_regularisation') }}</textarea>
                        </div>

                        <!-- Option Sous Couvert -->
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                <div>
                                    <label class="form-check-label fw-bold text-dark mb-0 d-block cursor-pointer" for="reg_sous_couvert">
                                        <i class="bi bi-shield-fill-check text-primary me-1"></i> Option « Sous couvert » (100% IPM)
                                    </label>
                                    <small class="text-muted">Régularisation prise en charge à 100% par l'IPM (Part participant = 0 FCFA).</small>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input fs-4 cursor-pointer" type="checkbox" name="sous_couvert" value="1" id="reg_sous_couvert">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold rounded-3 shadow-sm">
                        <i class="bi bi-check2-all me-1"></i> Valider et enregistrer la régularisation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function handleRegSalarieChange(select) {
    const opt = select.options[select.selectedIndex];
    const ayantSelect = document.getElementById('reg_ayant_droit_select');
    ayantSelect.innerHTML = '<option value="">Participant lui-même</option>';
    if (!opt || !opt.value) return;

    try {
        const ayants = JSON.parse(opt.getAttribute('data-ayants') || '[]');
        ayants.forEach(a => {
            const el = document.createElement('option');
            el.value = a.id_ayant_droit;
            el.textContent = `${a.nom} ${a.prenom} (${a.lien_parente || 'Ayant droit'})`;
            ayantSelect.appendChild(el);
        });
    } catch (e) {}
}

function handleRegTypeChange(select) {
    const opt = select.options[select.selectedIndex];
    const text = (opt.textContent || '').toLowerCase();
    const pratWrapper = document.getElementById('reg_prat_wrapper');
    const pharmWrapper = document.getElementById('reg_pharm_wrapper');

    if (text.includes('bon')) {
        pharmWrapper.style.display = 'block';
        pratWrapper.style.display = 'none';
    } else {
        pharmWrapper.style.display = 'none';
        pratWrapper.style.display = 'block';
    }
}
</script>
@endcanedit
@endsection

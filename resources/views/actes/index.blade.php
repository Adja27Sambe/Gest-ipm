@extends('layouts.app')

@section('title', 'Gestion des Actes & Types d’Actes')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Tableau de bord</a></li>
                    <li class="breadcrumb-item">Paramétrage</li>
                    <li class="breadcrumb-item active" aria-current="page">Actes & Types d’Actes</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-ui-checks-grid text-primary"></i> Actes & Types d’Actes Médicaux
            </h1>
            <p class="text-muted small mb-0">Paramétrage des catégories générales d’actes et de leurs déclinaisons détaillées.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('parametres-couverture.index') }}" class="btn btn-outline-primary rounded-3">
                <i class="bi bi-shield-check me-1"></i> Paramètres de Couverture
            </a>
            @canedit
            <button type="button" class="btn btn-primary rounded-3 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#createActeModal">
                <i class="bi bi-plus-lg me-1"></i> Nouvel Acte Général
            </button>
            @endcanedit
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert">
            <i class="bi bi-check-circle-fill fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Recherche -->
    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('actes.index') }}" class="row g-2 align-items-center">
                <div class="col-md-10">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0" placeholder="Rechercher un acte général ou un type d'acte (ex: Radiographie, Scanner, Consultation...)" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filtrer</button>
                    @if(request()->filled('search'))
                        <a href="{{ route('actes.index') }}" class="btn btn-outline-secondary" title="Réinitialiser"><i class="bi bi-x-lg"></i></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Grille des Actes et de leurs Types d'actes -->
    <div class="row g-4">
        @forelse($actes as $acte)
            @php
                $couverture = $acte->parametreCouverture;
                $typesActes = $acte->typesActes ?? collect();
            @endphp
            <div class="col-xl-6 col-12">
                <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden bg-white">
                    <div class="card-header bg-white border-0 pt-4 px-4 pb-2">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-4 d-flex justify-content-center align-items-center fw-bold" style="width: 48px; height: 48px;">
                                    <i class="bi bi-hospital fs-4"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                        {{ $acte->libelle }}
                                        @if($acte->statut === 'actif')
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0 small fs-7 border border-success border-opacity-25">Actif</span>
                                        @else
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-0 small fs-7 border">Inactif</span>
                                        @endif
                                    </h5>
                                    @if($acte->code)
                                        <small class="text-muted">Code : <code>{{ $acte->code }}</code></small>
                                    @endif
                                </div>
                            </div>
                            @canedit
                            <div class="dropdown">
                                <button class="btn btn-light btn-sm rounded-circle shadow-none" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                    <li>
                                        <button class="dropdown-item small" type="button" onclick="editActeModal({{ $acte->id_type_prestation }}, '{{ addslashes($acte->libelle) }}', '{{ addslashes($acte->code) }}', '{{ addslashes($acte->description) }}', '{{ $acte->statut }}')">
                                            <i class="bi bi-pencil me-2 text-warning"></i>Modifier l'acte
                                        </button>
                                    </li>
                                    @if($couverture)
                                        <li>
                                            <a class="dropdown-item small" href="{{ route('parametres-couverture.edit', $couverture->id_parametre) }}">
                                                <i class="bi bi-shield-check me-2 text-primary"></i>Modifier la couverture
                                            </a>
                                        </li>
                                    @else
                                        <li>
                                            <a class="dropdown-item small" href="{{ route('parametres-couverture.create') }}">
                                                <i class="bi bi-plus-circle me-2 text-success"></i>Configurer la couverture
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            @endcanedit
                        </div>
                        @if($acte->description)
                            <p class="text-muted small mt-2 mb-0">{{ $acte->description }}</p>
                        @endif
                    </div>

                    <div class="card-body px-4 py-3">
                        <!-- Couverture résumée -->
                        <div class="p-3 bg-light rounded-3 border mb-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="text-muted small d-block">Couverture IPM :</span>
                                @if($couverture)
                                    <strong class="text-success fs-6">
                                        @if($couverture->type_calcul === 'SOUS_COUVERT')
                                            100% (Sous couvert)
                                        @elseif($couverture->type_calcul === 'EXCLUSION')
                                            0% (Exclusion)
                                        @elseif($couverture->taux_prise_charge !== null)
                                            {{ (float)$couverture->taux_prise_charge }} %
                                        @else
                                            Règle spécifique
                                        @endif
                                    </strong>
                                    @if($couverture->montant_referentiel)
                                        <span class="text-muted small ms-1">(Réf: {{ number_format($couverture->montant_referentiel, 0, ',', ' ') }} F)</span>
                                    @elseif($couverture->montant_forfait)
                                        <span class="text-muted small ms-1">(Forfait: {{ number_format($couverture->montant_forfait, 0, ',', ' ') }} F)</span>
                                    @elseif($couverture->tarif_journalier)
                                        <span class="text-muted small ms-1">(Chambre: {{ number_format($couverture->tarif_journalier, 0, ',', ' ') }} F/j)</span>
                                    @endif
                                @else
                                    <span class="badge bg-warning bg-opacity-15 text-dark">Non configurée</span>
                                @endif
                            </div>
                            @if($couverture)
                                <a href="{{ route('parametres-couverture.edit', $couverture->id_parametre) }}" class="btn btn-sm btn-link text-decoration-none p-0 text-primary">
                                    Détails règle <i class="bi bi-arrow-right"></i>
                                </a>
                            @else
                                <a href="{{ route('parametres-couverture.create') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2">
                                    Configurer
                                </a>
                            @endif
                        </div>

                        <!-- Types d'actes associés -->
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.5px;">
                                    Types d'actes (Précisions) :
                                    <span class="badge bg-primary bg-opacity-10 text-primary ms-1 fw-normal">{{ $typesActes->count() }}</span>
                                </span>
                                @canedit
                                <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold" onclick="openAddTypeActeModal({{ $acte->id_type_prestation }}, '{{ addslashes($acte->libelle) }}')">
                                    <i class="bi bi-plus-circle me-1"></i>Ajouter type
                                </button>
                                @endcanedit
                            </div>

                            <div class="d-flex flex-wrap gap-2" id="types_container_{{ $acte->id_type_prestation }}">
                                @forelse($typesActes as $ta)
                                    <span class="badge bg-white text-dark border px-2 py-1 rounded-pill fw-medium shadow-xs" title="{{ $ta->description ?? $ta->libelle }}">
                                        {{ $ta->libelle }}
                                    </span>
                                @empty
                                    <span class="text-muted small fst-italic">Aucun type spécifique rattaché pour le moment.</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="bi bi-hospital fs-1 text-muted d-block mb-2"></i>
                <h5 class="text-muted">Aucun acte médical configuré.</h5>
            </div>
        @endforelse
    </div>
</div>

<!-- Modal Création Acte Général -->
@canedit
<div class="modal fade" id="createActeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark">Nouvel Acte Général</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('actes.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Nom de l'acte (Catégorie générale) <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" class="form-control" placeholder="Ex: Radiologie, Chirurgie, Soins Dentaires..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Code identifiant (Optionnel)</label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="Ex: RAD, CHIR, DENT">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Précisions sur cette catégorie médicale..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm">Créer l'acte</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Édition Acte Général -->
<div class="modal fade" id="editActeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark">Modifier l'Acte Général</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editActeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Nom de l'acte <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="edit_acte_libelle" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Code identifiant</label>
                        <input type="text" name="code" id="edit_acte_code" class="form-control text-uppercase">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Description</label>
                        <textarea name="description" id="edit_acte_description" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Statut</label>
                        <select name="statut" id="edit_acte_statut" class="form-select">
                            <option value="actif">Actif</option>
                            <option value="inactif">Inactif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-warning text-dark px-4 fw-bold rounded-3 shadow-sm">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Ajout Rapide Type d'Acte -->
<div class="modal fade" id="addTypeActeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark">
                    Ajouter un Type d'Acte à <span id="add_type_parent_name" class="text-primary"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addTypeActeForm" onsubmit="submitAddTypeActe(event)">
                @csrf
                <input type="hidden" name="id_type_prestation" id="add_type_parent_id">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Précision / Nom du type d'acte <span class="text-danger">*</span></label>
                        <input type="text" name="libelle" id="add_type_libelle" class="form-control" placeholder="Ex: Radiographie pulmonaire, Scanner abdominal, IRM..." required>
                        <div class="form-text small text-muted">Contrôle anti-doublon automatique (insensible à la casse et aux espaces).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-dark small fw-semibold">Description (Optionnel)</label>
                        <textarea name="description" id="add_type_description" class="form-control" rows="2" placeholder="Précisions cliniques..."></textarea>
                    </div>
                    <div id="add_type_feedback" class="small"></div>
                </div>
                <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm" id="add_type_submit_btn">
                        <i class="bi bi-plus-circle me-1"></i> Enregistrer le type d'acte
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editActeModal(id, libelle, code, description, statut) {
    document.getElementById('editActeForm').action = `/actes/${id}`;
    document.getElementById('edit_acte_libelle').value = libelle;
    document.getElementById('edit_acte_code').value = code || '';
    document.getElementById('edit_acte_description').value = description || '';
    document.getElementById('edit_acte_statut').value = statut || 'actif';
    new bootstrap.Modal(document.getElementById('editActeModal')).show();
}

function openAddTypeActeModal(parentId, parentName) {
    document.getElementById('add_type_parent_id').value = parentId;
    document.getElementById('add_type_parent_name').textContent = parentName;
    document.getElementById('add_type_libelle').value = '';
    document.getElementById('add_type_description').value = '';
    document.getElementById('add_type_feedback').innerHTML = '';
    new bootstrap.Modal(document.getElementById('addTypeActeModal')).show();
}

async function submitAddTypeActe(e) {
    e.preventDefault();
    const btn = document.getElementById('add_type_submit_btn');
    const feedback = document.getElementById('add_type_feedback');
    const parentId = document.getElementById('add_type_parent_id').value;
    const libelle = document.getElementById('add_type_libelle').value;
    const desc = document.getElementById('add_type_description').value;

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...';
    feedback.innerHTML = '';

    try {
        const response = await fetch("{{ route('api.types-actes.store') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({
                id_type_prestation: parentId,
                libelle: libelle,
                description: desc
            })
        });

        const data = await response.json();
        if (response.ok && data.success) {
            feedback.innerHTML = `<span class="text-success"><i class="bi bi-check-circle me-1"></i>${data.message}</span>`;
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addTypeActeModal'));
                if (modal) modal.hide();
                window.location.reload();
            }, 500);
        } else {
            feedback.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>${data.error || 'Erreur lors de l’enregistrement.'}</span>`;
        }
    } catch (err) {
        feedback.innerHTML = `<span class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Erreur réseau : ${err.message}</span>`;
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Enregistrer le type d\'acte';
    }
}
</script>
@endcanedit
@endsection

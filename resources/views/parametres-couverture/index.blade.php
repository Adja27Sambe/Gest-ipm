@extends('layouts.app')

@section('title', 'Paramètres de Couverture des Actes')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('actes.index') }}" class="text-decoration-none">Actes</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Paramètres de Couverture</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-primary"></i> Paramètres de Couverture des Actes
            </h1>
            <p class="text-muted small mb-0">Définition des modes de prise en charge (taux, forfaits, montants référentiels, tarifs journaliers).</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('actes.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-ui-checks-grid me-1"></i> Gérer les Actes & Types d'actes
            </a>
            @canedit
            <a href="{{ route('parametres-couverture.create') }}" class="btn btn-primary rounded-3 shadow-sm px-3">
                <i class="bi bi-plus-lg me-1"></i> Nouveau Paramètre d'Acte
            </a>
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

    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('parametres-couverture.index') }}" class="row g-2 align-items-center">
                <div class="col-md-9">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0" placeholder="Rechercher un acte général..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-center justify-content-end gap-2">
                    <label for="per_page" class="text-muted small fw-medium text-nowrap">Afficher :</label>
                    <select name="per_page" id="per_page" class="form-select bg-light border-0" style="width: 110px;" onchange="this.form.submit()">
                        <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 / page</option>
                        <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25 / page</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="py-3 px-4">Acte Médical</th>
                        <th class="py-3 px-3">Mode de Calcul</th>
                        <th class="py-3 px-3 text-center">Taux Couverture</th>
                        <th class="py-3 px-3 text-center">Montant Référentiel</th>
                        <th class="py-3 px-3 text-center">Règle Métier Spécifique</th>
                        <th class="py-3 px-4 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($parametres as $param)
                        @php
                            $calc = $param->type_calcul ?? 'POURCENTAGE';
                            $nbTypes = $param->typePrestation?->typesActes?->count() ?? 0;
                        @endphp
                        <tr>
                            <td class="px-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 42px; height: 42px;">
                                        <i class="bi bi-hospital fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $param->typePrestation->libelle ?? 'Acte non défini' }}</h6>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-secondary border px-2 py-0 rounded-pill">{{ $nbTypes }} type(s) d'acte associé(s)</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3">
                                @if($calc === 'MONTANT_REFERENTIEL')
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 rounded-pill">Montant Référentiel</span>
                                @elseif($calc === 'FORFAIT')
                                    <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 px-2 py-1 rounded-pill">Forfait fixe</span>
                                @elseif($calc === 'TARIF_JOURNALIER')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill">Tarif journalier</span>
                                @elseif($calc === 'EXCLUSION' || $param->est_exclusion)
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill">Exclusion (0%)</span>
                                @elseif($calc === 'SOUS_COUVERT')
                                    <span class="badge bg-primary bg-opacity-15 text-primary border border-primary border-opacity-50 px-2 py-1 rounded-pill fw-bold">Sous Couvert (100%)</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">Pourcentage</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($param->taux_prise_charge !== null)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fs-6 border border-success border-opacity-25">
                                        {{ (float)$param->taux_prise_charge }} %
                                    </span>
                                @else
                                    <span class="text-muted small fst-italic">Sans taux (Règle spécifique)</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($param->montant_referentiel)
                                    <div class="fw-bold text-dark">{{ number_format($param->montant_referentiel, 0, ',', ' ') }} <small class="text-muted">FCFA</small></div>
                                @elseif($param->ticket_moderateur)
                                    <div class="fw-bold text-dark">{{ number_format($param->ticket_moderateur, 0, ',', ' ') }} <small class="text-muted">FCFA</small></div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 text-center">
                                @if($param->montant_forfait)
                                    <span class="badge bg-warning bg-opacity-15 text-dark px-2 py-1 rounded-pill">
                                        Forfait: {{ number_format($param->montant_forfait, 0, ',', ' ') }} F
                                    </span>
                                @elseif($param->tarif_journalier)
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill">
                                        Chambre: {{ number_format($param->tarif_journalier, 0, ',', ' ') }} F/jour
                                    </span>
                                @elseif($param->montant_plafond)
                                    <span class="badge bg-info bg-opacity-10 text-info px-2 py-1 rounded-pill">
                                        Plafond: {{ number_format($param->montant_plafond, 0, ',', ' ') }} F
                                    </span>
                                @else
                                    <span class="text-muted small">Standard</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-end text-nowrap">
                                @canedit
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('parametres-couverture.edit', $param->id_parametre) }}" class="btn btn-outline-warning" title="Modifier la règle">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('parametres-couverture.destroy', $param->id_parametre) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette règle de couverture ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                @endcanedit
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-shield-x fs-1 text-muted d-block mb-2"></i>
                                Aucun paramètre de couverture configuré.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($parametres->hasPages())
            <div class="card-footer bg-white border-0 py-3 px-4">
                {{ $parametres->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

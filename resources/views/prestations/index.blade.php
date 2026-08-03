@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Suivi des Prestations Médicales</h2>
    <div class="d-flex gap-2">
        <button class="btn btn-outline-success shadow-sm" data-bs-toggle="modal" data-bs-target="#exportExcelModal">
            <i class="bi bi-file-earmark-excel"></i> Exporter
        </button>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createPrestationModal">
            <i class="bi bi-plus-circle"></i> Saisir une prestation
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('prestations.index') }}" class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique prestations (bénéficiaire, acte, prestataire)..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-4 d-flex align-items-center justify-content-end">
                <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                <select name="per_page" id="per_page" class="form-select bg-light border-0" style="width: 100px;" onchange="this.form.submit()">
                    <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 / page</option>
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Type d'Acte</th>
                        <th>Bénéficiaire (Participant/Ayant-droit)</th>
                        <th>Partenaire de Santé (Prestataire)</th>
                        <th class="text-end">Montant Total</th>
                        <th class="text-end text-success">Prise en Charge</th>
                        <th class="text-end text-danger pe-4">Reste à Charge</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestations as $p)
                    @php
                        $beneficiaireLabel = '-';
                        if($p->demande) {
                            if($p->demande->ayantDroit) {
                                $beneficiaireLabel = $p->demande->ayantDroit->prenom . ' ' . $p->demande->ayantDroit->nom . ' <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Ayant-droit</span>';
                            } elseif($p->demande->salarie) {
                                $beneficiaireLabel = $p->demande->salarie->prenom . ' ' . $p->demande->salarie->nom;
                            }
                        }
                        $pec = $p->montant - $p->reste_a_charge;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-medium text-nowrap">
                            <i class="bi bi-calendar-event me-1 text-muted"></i>
                            {{ $p->date_prestation ? $p->date_prestation->format('d/m/Y') : '-' }}
                        </td>
                        <td>
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1">
                                {{ $p->typePrestation->libelle ?? 'Inconnu' }}
                            </span>
                        </td>
                        <td>{!! $beneficiaireLabel !!}</td>
                        <td>{{ $p->prestataire->nom ?? 'Inconnu' }}</td>
                        <td class="text-end fw-bold">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                        <td class="text-end text-success fw-medium">{{ number_format($pec, 0, ',', ' ') }} FCFA <small class="text-muted">({{ rtrim(rtrim($p->taux_prise_charge, '0'), '.') }}%)</small></td>
                        <td class="text-end text-danger fw-bold pe-4">{{ number_format($p->reste_a_charge, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-file-medical fs-1 d-block mb-3 opacity-50"></i>
                            Aucune prestation enregistrée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">
        Affichage de {{ $prestations->firstItem() ?? 0 }} à {{ $prestations->lastItem() ?? 0 }} sur {{ $prestations->total() }} prestations
    </div>
    <div>
        {{ $prestations->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Création Prestation -->
<div class="modal fade" id="createPrestationModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Saisir une Prestation Médicale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('prestations.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    
                    @if(session('error'))
                    <div class="alert alert-danger bg-danger bg-opacity-10 text-danger border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Date de l'acte *</label>
                            <input type="date" name="date_prestation" class="form-control" value="{{ old('date_prestation', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Type d'Acte *</label>
                            <select name="id_type_prestation" class="form-select" required>
                                <option value="">Sélectionner</option>
                                @foreach($typesPrestation as $tp)
                                    <option value="{{ $tp->id_type_prestation }}" {{ old('id_type_prestation') == $tp->id_type_prestation ? 'selected' : '' }}>{{ $tp->libelle }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Demande Prise en charge Approuvée *</label>
                            <select name="id_demande" class="form-select" required>
                                <option value="">Sélectionner la demande source</option>
                                @foreach($demandes as $d)
                                    @php
                                        $label = "Demande #{$d->id_demande} - " . $d->salarie->nom;
                                        if($d->ayantDroit) $label .= " (Ayant-droit: {$d->ayantDroit->nom})";
                                    @endphp
                                    <option value="{{ $d->id_demande }}" {{ old('id_demande') == $d->id_demande ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <div class="form-text small text-muted">L'imputation des plafonds sera calculée sur le bénéficiaire de cette demande.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Prestataire *</label>
                            <select name="id_prestataire" class="form-select" required>
                                <option value="">Sélectionner</option>
                                @foreach($prestataires as $p)
                                    <option value="{{ $p->id_prestataire }}" {{ old('id_prestataire') == $p->id_prestataire ? 'selected' : '' }}>{{ $p->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Montant Facturé (FCFA) *</label>
                            <input type="number" step="0.01" name="montant" class="form-control" value="{{ old('montant') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Taux Prise en charge (%) *</label>
                            <input type="number" step="0.01" max="100" name="taux_prise_charge" class="form-control" value="{{ old('taux_prise_charge', 80) }}" required>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-primary bg-primary bg-opacity-10 text-primary border-0 d-flex align-items-center mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                <small>Le Reste à Charge sera calculé et sauvegardé automatiquement par le système lors de la création.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer la prestation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Export Excel -->
<div class="modal fade" id="exportExcelModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-success"><i class="bi bi-file-earmark-excel me-2"></i>Export Comptable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('prestations.export') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-muted small mb-1">Filtrer par Prestataire</label>
                        <select name="id_prestataire" class="form-select">
                            <option value="">Tous les prestataires</option>
                            @foreach($prestataires as $p)
                                <option value="{{ $p->id_prestataire }}">{{ $p->nom }}</option>
                            @endforeach
                        </select>
                    </div>
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
                    <div class="form-text text-muted small">Laissez les dates vides pour exporter l'intégralité de la base de données.</div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-success">Télécharger XLSX</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var myModal = new bootstrap.Modal(document.getElementById('createPrestationModal'));
        myModal.show();
    });
</script>
@endif

@endsection

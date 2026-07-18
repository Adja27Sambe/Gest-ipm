@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Gestion des Devis</h2>
</div>

<!-- Widgets de Statistiques -->
<div class="row mb-4 g-3">
    @foreach(['soumis' => 'primary', 'en_revue' => 'warning', 'valide' => 'success', 'rejete' => 'danger'] as $statutKey => $color)
        @php
            $statData = $stats->firstWhere('statut.value', $statutKey);
            $montant = $statData ? $statData->total_montant : 0;
            $count = $statData ? $statData->total_devis : 0;
            $label = ucfirst(str_replace('_', ' ', $statutKey));
        @endphp
        <div class="col-md-3">
            <div class="card bg-{{ $color }} bg-opacity-10 border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="text-{{ $color }} text-uppercase fw-bold mb-1">{{ $label }}</h6>
                    <h3 class="fw-bold mb-0">{{ number_format($montant, 0, ',', ' ') }} <small class="fs-6 text-muted">FCFA</small></h3>
                    <div class="small text-muted mt-2"><i class="bi bi-file-earmark me-1"></i>{{ $count }} devis</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Bénéficiaire</th>
                        <th>Prestataire</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devis as $d)
                    @php
                        $beneficiaireLabel = '-';
                        if($d->beneficiaire) {
                            $beneficiaireLabel = $d->beneficiaire->prenom . ' ' . $d->beneficiaire->nom;
                            if($d->beneficiaire_type === \App\Models\AyantDroit::class) {
                                $beneficiaireLabel .= ' <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Ayant-droit</span>';
                            }
                        }
                    @endphp
                    <tr>
                        <td class="ps-4 fw-medium">{{ $d->date_devis ? $d->date_devis->format('d/m/Y') : '-' }}</td>
                        <td>{!! $beneficiaireLabel !!}</td>
                        <td>{{ $d->prestataire->nom ?? 'Inconnu' }}</td>
                        <td class="fw-bold">{{ number_format($d->montant, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @if($d->statut->value === 'valide')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">Validé</span>
                            @elseif($d->statut->value === 'en_revue')
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">En revue</span>
                            @elseif($d->statut->value === 'rejete')
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">Rejeté</span>
                            @else
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">Soumis</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <button class="btn btn-sm btn-light text-primary" 
                                onclick="openTransitionModal({{ $d->id_devis }}, '{{ $d->statut->value }}', {{ json_encode($d->validations) }})"
                                title="Gérer le statut">
                                <i class="bi bi-shield-check"></i> Valider
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-file-earmark-medical fs-1 d-block mb-3 opacity-50"></i>
                            Aucun devis trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $devis->links('pagination::bootstrap-5') }}
</div>
@endsection

@section('modals')
<!-- Modal Transition Statut -->
<div class="modal fade" id="transitionModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Statut du Devis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="transitionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-medium text-muted small mb-1">Nouveau Statut *</label>
                        <select name="statut" id="edit_statut" class="form-select" required>
                            <option value="soumis">Soumis</option>
                            <option value="en_revue">En revue</option>
                            <option value="valide">Validé</option>
                            <option value="rejete">Rejeté</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium text-muted small mb-1">Commentaire (Optionnel)</label>
                        <textarea name="commentaire" class="form-control" rows="2" placeholder="Ex: Montant trop élevé..."></textarea>
                    </div>
                    
                    <hr>
                    <h6 class="fw-bold small text-muted text-uppercase mt-4 mb-2">Historique des décisions</h6>
                    <div id="validationsHistory" class="small list-group list-group-flush border rounded-3">
                        <!-- Rempli en JS -->
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openTransitionModal(idDevis, currentStatut, validations) {
        document.getElementById('transitionForm').action = `/devis/${idDevis}/transition`;
        document.getElementById('edit_statut').value = currentStatut;
        
        const historyContainer = document.getElementById('validationsHistory');
        historyContainer.innerHTML = '';
        
        if (validations && validations.length > 0) {
            validations.forEach(val => {
                const date = new Date(val.date_validation).toLocaleDateString('fr-FR');
                let badge = '';
                if(val.decision === 'valide') badge = '<span class="badge bg-success">Validé</span>';
                else if(val.decision === 'rejete') badge = '<span class="badge bg-danger">Rejeté</span>';
                else badge = `<span class="badge bg-secondary">${val.decision}</span>`;
                
                const user = val.utilisateur ? val.utilisateur.login : 'Système';
                
                historyContainer.innerHTML += `
                    <div class="list-group-item px-3 py-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            ${badge}
                            <small class="text-muted">${date} par ${user}</small>
                        </div>
                        ${val.commentaire ? `<div class="text-muted fst-italic">"${val.commentaire}"</div>` : ''}
                    </div>
                `;
            });
        } else {
            historyContainer.innerHTML = '<div class="list-group-item px-3 py-2 text-center text-muted">Aucun historique</div>';
        }

        new bootstrap.Modal(document.getElementById('transitionModal')).show();
    }
</script>
@endsection

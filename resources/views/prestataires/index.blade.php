@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Gestion des Prestataires</h2>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createPrestataireModal">
        <i class="bi bi-plus-lg"></i> Nouveau Prestataire
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Nom</th>
                        <th>Spécialité</th>
                        <th>Type</th>
                        <th>Contact</th>
                        <th>Conventions</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($prestataires as $prestataire)
                    <tr>
                        <td class="ps-4 fw-medium">{{ $prestataire->nom }}</td>
                        <td>{{ $prestataire->specialite ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle">
                                {{ $prestataire->type->libelle ?? 'Non défini' }}
                            </span>
                        </td>
                        <td>
                            @if($prestataire->telephone) <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $prestataire->telephone }}</div> @endif
                            @if($prestataire->email) <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $prestataire->email }}</div> @endif
                        </td>
                        <td>
                            @php
                                $activeConventions = $prestataire->conventions->filter(function($c) {
                                    $now = \Carbon\Carbon::now()->startOfDay();
                                    return $c->statut === 'active' && 
                                           ($c->date_debut ? \Carbon\Carbon::parse($c->date_debut)->lte($now) : true) && 
                                           ($c->date_fin ? \Carbon\Carbon::parse($c->date_fin)->gte($now) : true);
                                })->count();
                            @endphp
                            @if($activeConventions > 0)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle"><i class="bi bi-check-circle me-1"></i>{{ $activeConventions }} Active(s)</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle">Aucune active</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <!-- Bouton Conventions -->
                            <button class="btn btn-sm btn-light text-primary me-1" 
                                onclick="openConventionsModal({{ $prestataire->id_prestataire }}, '{{ addslashes($prestataire->nom) }}', {{ json_encode($prestataire->conventions) }})"
                                title="Gérer les conventions">
                                <i class="bi bi-file-earmark-text"></i>
                            </button>
                            <!-- Bouton Éditer -->
                            <button class="btn btn-sm btn-light text-secondary me-1" 
                                onclick="editPrestataire({{ $prestataire }})"
                                title="Modifier le prestataire">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <!-- Bouton Supprimer -->
                            <form action="{{ route('prestataires.destroy', $prestataire->id_prestataire) }}" method="POST" class="d-inline" onsubmit="return confirm('Voulez-vous vraiment supprimer ce prestataire ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-light text-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-building fs-1 d-block mb-3 opacity-50"></i>
                            Aucun prestataire trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Création Prestataire -->
<div class="modal fade" id="createPrestataireModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nouveau Prestataire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('prestataires.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium text-muted small mb-1">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted small mb-1">Type *</label>
                            <select name="id_type" class="form-select" required>
                                <option value="">Sélectionner</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id_type }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Spécialité</label>
                            <input type="text" name="specialite" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Téléphone</label>
                            <input type="text" name="telephone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Email</label>
                            <input type="email" name="email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Adresse</label>
                            <textarea name="adresse" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Latitude (GPS)</label>
                            <input type="number" step="any" name="latitude" class="form-control" placeholder="ex: 14.6928">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Longitude (GPS)</label>
                            <input type="number" step="any" name="longitude" class="form-control" placeholder="ex: -17.4467">
                        </div>
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

<!-- Modal Édition Prestataire -->
<div class="modal fade" id="editPrestataireModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Modifier Prestataire</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPrestataireForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-medium text-muted small mb-1">Nom *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted small mb-1">Type *</label>
                            <select name="id_type" id="edit_id_type" class="form-select" required>
                                @foreach($types as $type)
                                    <option value="{{ $type->id_type }}">{{ $type->libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Spécialité</label>
                            <input type="text" name="specialite" id="edit_specialite" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Téléphone</label>
                            <input type="text" name="telephone" id="edit_telephone" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Adresse</label>
                            <textarea name="adresse" id="edit_adresse" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Latitude (GPS)</label>
                            <input type="number" step="any" name="latitude" id="edit_latitude" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Longitude (GPS)</label>
                            <input type="number" step="any" name="longitude" id="edit_longitude" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gestion Conventions -->
<div class="modal fade" id="conventionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-3">
                <h5 class="modal-title fw-bold">Conventions : <span id="convPrestataireNom" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light pt-0">
                
                <!-- Formulaire ajout convention -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Ajouter une convention</h6>
                        <form action="{{ route('conventions.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id_prestataire" id="conv_id_prestataire">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Date début</label>
                                    <input type="date" name="date_debut" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Date fin</label>
                                    <input type="date" name="date_fin" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small text-muted mb-1">Statut</label>
                                    <select name="statut" class="form-select form-select-sm">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="suspendue">Suspendue</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Ajouter</button>
                                </div>
                                <div class="col-12 mt-2">
                                    <input type="text" name="observations" class="form-control form-control-sm" placeholder="Observations (optionnel)">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Liste des conventions -->
                <h6 class="fw-bold mb-3">Historique des conventions</h6>
                <div class="list-group list-group-flush rounded-3 shadow-sm" id="conventionsList">
                    <!-- Rempli en JS -->
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    function editPrestataire(prestataire) {
        document.getElementById('edit_nom').value = prestataire.nom;
        document.getElementById('edit_id_type').value = prestataire.id_type;
        document.getElementById('edit_specialite').value = prestataire.specialite || '';
        document.getElementById('edit_telephone').value = prestataire.telephone || '';
        document.getElementById('edit_email').value = prestataire.email || '';
        document.getElementById('edit_adresse').value = prestataire.adresse || '';
        document.getElementById('edit_latitude').value = prestataire.latitude || '';
        document.getElementById('edit_longitude').value = prestataire.longitude || '';
        
        document.getElementById('editPrestataireForm').action = `/prestataires/${prestataire.id_prestataire}`;
        
        new bootstrap.Modal(document.getElementById('editPrestataireModal')).show();
    }

    function openConventionsModal(idPrestataire, nomPrestataire, conventions) {
        document.getElementById('convPrestataireNom').textContent = nomPrestataire;
        document.getElementById('conv_id_prestataire').value = idPrestataire;
        
        const listContainer = document.getElementById('conventionsList');
        listContainer.innerHTML = '';

        if(conventions.length === 0) {
            listContainer.innerHTML = '<div class="list-group-item text-center text-muted py-4">Aucune convention pour ce prestataire.</div>';
        } else {
            // Trier par date_fin décroissante
            conventions.sort((a, b) => new Date(b.date_fin) - new Date(a.date_fin));
            
            conventions.forEach(conv => {
                const isActive = conv.statut === 'active';
                const badgeClass = isActive ? 'bg-success text-success border-success-subtle' : 'bg-secondary text-secondary border-secondary-subtle';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                const html = `
                    <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                        <div>
                            <span class="badge ${badgeClass} bg-opacity-10 border me-2">${conv.statut ? conv.statut.toUpperCase() : 'INCONNU'}</span>
                            <strong>${formatDate(conv.date_debut)} <i class="bi bi-arrow-right mx-1 text-muted"></i> ${formatDate(conv.date_fin)}</strong>
                            ${conv.observations ? `<div class="small text-muted mt-1"><i class="bi bi-chat-left-text me-1"></i>${conv.observations}</div>` : ''}
                        </div>
                        <div>
                            <form action="/conventions/${conv.id_convention}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette convention ?');">
                                <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-light text-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                `;
                listContainer.insertAdjacentHTML('beforeend', html);
            });
        }
        
        new bootstrap.Modal(document.getElementById('conventionsModal')).show();
    }

    function formatDate(dateStr) {
        if(!dateStr) return '-';
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR');
    }
</script>
@endsection

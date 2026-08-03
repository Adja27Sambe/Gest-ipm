@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Gestion des Pharmacies</h2>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createPharmacieModal">
        <i class="bi bi-plus-lg"></i> Nouvelle Pharmacie
    </button>
</div>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('pharmacies.index') }}" class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique pharmacies (nom, titulaire, contact)..." value="{{ request('search') }}" autocomplete="off">
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
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Partenaire de Santé</th>
                        <th>Pharmacien Titulaire</th>
                        <th>Contact</th>
                        <th>Conventions</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pharmacies as $pharmacie)
                    <tr class="clickable-row" onclick="if(!event.target.closest('button') && !event.target.closest('a')) editPharmacie({{ htmlspecialchars(json_encode($pharmacie)) }})">
                        <td class="ps-4 fw-medium">{{ $pharmacie->nom }}</td>
                        <td>{{ $pharmacie->nom_pharmacien ?? '-' }}</td>

                        <td>
                            @if($pharmacie->telephone) <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $pharmacie->telephone }}</div> @endif
                            @if($pharmacie->email) <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $pharmacie->email }}</div> @endif
                        </td>
                        <td>
                            @php
                                $activeConventions = $pharmacie->conventions->filter(function($c) {
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
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions pharmacie">
                                <!-- Bouton Conventions -->
                                <button class="btn btn-outline-primary" 
                                    onclick="openConventionsModal({{ $pharmacie->id_pharmacie }}, '{{ addslashes($pharmacie->nom) }}', {{ json_encode($pharmacie->conventions) }})"
                                    title="Gérer les conventions">
                                    <i class="bi bi-file-earmark-text"></i>
                                </button>
                                <!-- Bouton Éditer -->
                                <button class="btn btn-outline-warning border-start-0" 
                                    onclick="editPharmacie({{ $pharmacie }})"
                                    title="Modifier le pharmacie">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Bouton Supprimer -->
                                <form action="{{ route('pharmacies.destroy', $pharmacie->id_pharmacie) }}" method="POST" class="d-inline" onsubmit="return confirm('Voulez-vous vraiment supprimer cette pharmacie ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger border-start-0" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-building fs-1 d-block mb-3 opacity-50"></i>
                            Aucune pharmacie trouvée.
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
        Affichage de {{ $pharmacies->firstItem() ?? 0 }} à {{ $pharmacies->lastItem() ?? 0 }} sur {{ $pharmacies->total() }} pharmacies
    </div>
    <div>
        {{ $pharmacies->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Création Pharmacie -->
<div class="modal fade" id="createPharmacieModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nouveau Pharmacie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('pharmacies.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Pharmacien Titulaire</label>
                            <input type="text" name="nom_pharmacien" class="form-control">
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
                            <input type="text" name="latitude" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Longitude (GPS)</label>
                            <input type="text" name="longitude" class="form-control">
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

<!-- Modal Édition Pharmacie -->
<div class="modal fade" id="editPharmacieModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Modifier Pharmacie</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPharmacieForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Nom *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Pharmacien Titulaire</label>
                            <input type="text" name="nom_pharmacien" id="edit_nom_pharmacien" class="form-control">
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
                            <input type="text" name="latitude" id="edit_latitude" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Longitude (GPS)</label>
                            <input type="text" name="longitude" id="edit_longitude" class="form-control">
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
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Conventions - <span id="convPharmacieNom" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Add Convention Form -->
                <form action="{{ route('conventions.store') }}" method="POST" class="mb-4 bg-light p-3 rounded">
                    @csrf
                    <input type="hidden" name="id_pharmacie" id="conv_id_pharmacie">
                    <input type="hidden" name="type_partenaire" value="pharmacie">
                    <h6 class="fw-bold mb-3 small text-uppercase text-muted">Nouvelle convention</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Date début</label>
                            <input type="date" name="date_debut" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1">Date fin</label>
                            <input type="date" name="date_fin" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1">Statut</label>
                            <select name="statut" class="form-select form-select-sm">
                                <option value="active">Active</option>
                                <option value="suspendue">Suspendue</option>
                                <option value="resiliee">Résiliée</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1">Observations</label>
                            <div class="d-flex">
                                <input type="text" name="observations" class="form-control form-control-sm me-2">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i></button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Conventions List -->
                <h6 class="fw-bold mb-3 small text-uppercase text-muted">Historique des conventions</h6>
                <div class="list-group list-group-flush border-top" id="conventionsList">
                    <!-- Populated by JS -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function editPharmacie(pharmacie) {
        document.getElementById('edit_nom').value = pharmacie.nom;

        document.getElementById('edit_nom_pharmacien').value = pharmacie.nom_pharmacien || '';
        document.getElementById('edit_telephone').value = pharmacie.telephone || '';
        document.getElementById('edit_email').value = pharmacie.email || '';
        document.getElementById('edit_adresse').value = pharmacie.adresse || '';
        
        document.getElementById('editPharmacieForm').action = `/pharmacies/${pharmacie.id_pharmacie}`;
        
        new bootstrap.Modal(document.getElementById('editPharmacieModal')).show();
    }

    function openConventionsModal(idPharmacie, nomPharmacie, conventions) {
        document.getElementById('convPharmacieNom').textContent = nomPharmacie;
        document.getElementById('conv_id_pharmacie').value = idPharmacie;
        
        const listContainer = document.getElementById('conventionsList');
        listContainer.innerHTML = '';

        if(conventions.length === 0) {
            listContainer.innerHTML = '<div class="list-group-item text-center text-muted py-4">Aucune convention pour ce pharmacie.</div>';
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

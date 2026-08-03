@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 fw-bold text-dark mb-0">Gestion des Praticiens</h2>
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createPraticienModal">
        <i class="bi bi-plus-lg"></i> Nouveau Praticien
    </button>
</div>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('praticiens.index') }}" class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique praticiens (nom, spécialité, contact)..." value="{{ request('search') }}" autocomplete="off">
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
                        <th>Spécialité</th>
                        <th>Contact</th>
                        <th>Conventions</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($praticiens as $praticien)
                    <tr class="clickable-row" onclick="if(!event.target.closest('button') && !event.target.closest('a')) editPraticien({{ htmlspecialchars(json_encode($praticien)) }})">
                        <td class="ps-4 fw-medium">{{ $praticien->nom }}</td>
                        <td>{{ $praticien->specialite ?? '-' }}</td>

                        <td>
                            @if($praticien->telephone) <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $praticien->telephone }}</div> @endif
                            @if($praticien->email) <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $praticien->email }}</div> @endif
                        </td>
                        <td>
                            @php
                                $activeConventions = $praticien->conventions->filter(function($c) {
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
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions praticien">
                                <!-- Bouton Conventions -->
                                <button class="btn btn-outline-primary" 
                                    onclick="openConventionsModal({{ $praticien->id_praticien }}, '{{ addslashes($praticien->nom) }}', {{ json_encode($praticien->conventions) }})"
                                    title="Gérer les conventions">
                                    <i class="bi bi-file-earmark-text"></i>
                                </button>
                                <!-- Bouton Éditer -->
                                <button class="btn btn-outline-warning border-start-0" 
                                    onclick="editPraticien({{ $praticien }})"
                                    title="Modifier le praticien">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Bouton Supprimer -->
                                <form action="{{ route('praticiens.destroy', $praticien->id_praticien) }}" method="POST" class="d-inline" onsubmit="return confirm('Voulez-vous vraiment supprimer ce praticien ?');">
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
                            Aucun praticien trouvé.
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
        Affichage de {{ $praticiens->firstItem() ?? 0 }} à {{ $praticiens->lastItem() ?? 0 }} sur {{ $praticiens->total() }} praticiens
    </div>
    <div>
        {{ $praticiens->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Création Praticien -->
<div class="modal fade" id="createPraticienModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Nouveau Praticien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('praticiens.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Nom *</label>
                            <input type="text" name="nom" class="form-control" required>
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

<!-- Modal Édition Praticien -->
<div class="modal fade" id="editPraticienModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Modifier Praticien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPraticienForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-medium text-muted small mb-1">Nom *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
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
                <h5 class="modal-title fw-bold">Conventions - <span id="convPraticienNom" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Add Convention Form -->
                <form action="{{ route('conventions.store') }}" method="POST" class="mb-4 bg-light p-3 rounded">
                    @csrf
                    <input type="hidden" name="id_praticien" id="conv_id_praticien">
                    <input type="hidden" name="type_partenaire" value="praticien">
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
    function editPraticien(praticien) {
        document.getElementById('edit_nom').value = praticien.nom;

        document.getElementById('edit_specialite').value = praticien.specialite || '';
        document.getElementById('edit_telephone').value = praticien.telephone || '';
        document.getElementById('edit_email').value = praticien.email || '';
        document.getElementById('edit_adresse').value = praticien.adresse || '';
        
        document.getElementById('editPraticienForm').action = `/praticiens/${praticien.id_praticien}`;
        
        new bootstrap.Modal(document.getElementById('editPraticienModal')).show();
    }

    function openConventionsModal(idPraticien, nomPraticien, conventions) {
        document.getElementById('convPraticienNom').textContent = nomPraticien;
        document.getElementById('conv_id_praticien').value = idPraticien;
        
        const listContainer = document.getElementById('conventionsList');
        listContainer.innerHTML = '';

        if(conventions.length === 0) {
            listContainer.innerHTML = '<div class="list-group-item text-center text-muted py-4">Aucune convention pour ce praticien.</div>';
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

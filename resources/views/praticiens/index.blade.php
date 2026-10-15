@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h3 fw-bold text-dark mb-1">Gestion des Praticiens</h2>
        <p class="text-muted small mb-0">Partenaires médicaux, codes comptables, conventions et pièces jointes.</p>
    </div>
    @canedit
    <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#createPraticienModal">
        <i class="bi bi-plus-lg me-1"></i> Nouveau Praticien
    </button>
    @endcanedit
</div>

<div class="card border-0 shadow-sm mb-4 rounded-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('praticiens.index') }}" class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche par nom, code, spécialité, compte..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-3">
                <select name="convention" class="form-select bg-light border-0" onchange="this.form.submit()">
                    <option value="">-- Statut convention --</option>
                    <option value="1" {{ request('convention') === '1' ? 'selected' : '' }}>Conventionnés</option>
                    <option value="0" {{ request('convention') === '0' ? 'selected' : '' }}>Non conventionnés</option>
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

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Code</th>
                        <th>Partenaire de Santé</th>
                        <th>Code Comptable</th>
                        <th>Convention</th>
                        <th>Date Début</th>
                        <th>Contact</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($praticiens as $praticien)
                    <tr class="clickable-row" onclick="if(!event.target.closest('button') && !event.target.closest('a')) editPraticien({{ htmlspecialchars(json_encode($praticien)) }})">
                        <td class="ps-4">
                            <span class="badge bg-light text-primary border font-monospace px-2 py-1">{{ $praticien->code_praticien ?: 'PRAT-'.$praticien->id_praticien }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $praticien->nom }}</div>
                            <div class="small text-muted">{{ $praticien->specialite ?? 'Médecin' }}</div>
                        </td>
                        <td>
                            @if($praticien->code_comptable || $praticien->compte_comptable)
                                <span class="font-monospace text-dark fw-medium">{{ $praticien->code_comptable ?: $praticien->compte_comptable }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $hasActiveConv = $praticien->conventions->where('statut', 'active')->count() > 0 || (bool)$praticien->a_convention;
                            @endphp
                            @if($hasActiveConv)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle">
                                    <i class="bi bi-shield-check me-1"></i>Conventionné
                                </span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                                    Non conventionné
                                </span>
                            @endif
                            @if($praticien->conventions->count() > 0)
                                <span class="badge bg-light text-muted border ms-1">{{ $praticien->conventions->count() }} contrat(s)</span>
                            @endif
                        </td>
                        <td>
                            <span class="small text-muted">
                                {{ $praticien->date_enregistrement ? \Carbon\Carbon::parse($praticien->date_enregistrement)->format('d/m/Y') : '-' }}
                            </span>
                        </td>
                        <td>
                            @if($praticien->telephone) <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $praticien->telephone }}</div> @endif
                            @if($praticien->email) <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $praticien->email }}</div> @endif
                        </td>
                        <td class="text-end pe-4">
                            @canedit
                            <div class="btn-group btn-group-sm" role="group" aria-label="Actions praticien">
                                <!-- Bouton Conventions -->
                                <button class="btn btn-outline-primary" 
                                    onclick="openConventionsModal({{ $praticien->id_praticien }}, '{{ addslashes($praticien->nom) }}', {{ json_encode($praticien->conventions) }})"
                                    title="Gérer les conventions et documents">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </button>
                                <!-- Bouton Éditer -->
                                <button class="btn btn-outline-warning border-start-0" 
                                    onclick="editPraticien({{ json_encode($praticien) }})"
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
                            @else
                            <span class="text-muted small">-</span>
                            @endcanedit
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-hospital fs-1 d-block mb-3 opacity-50"></i>
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
@canedit
<!-- Modal Création Praticien -->
<div class="modal fade" id="createPraticienModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-hospital text-primary me-2"></i>Nouveau Praticien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('praticiens.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Code Praticien <span class="badge bg-light text-primary border ms-1">Auto</span></label>
                            <input type="text" name="code_praticien" class="form-control" placeholder="Laisser vide pour auto-génération ({{ $nextAutoCode ?? 'Auto' }})">
                            <div class="form-text small">Généré automatiquement si non renseigné.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Code Comptable Praticien</label>
                            <input type="text" name="compte_comptable" class="form-control font-monospace" placeholder="Ex: 40110025">
                            <div class="form-text small">Compte tiers pour la comptabilité.</div>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-medium text-muted small mb-1">Nom complet du Praticien *</label>
                            <input type="text" name="nom" class="form-control" required placeholder="Ex: Dr. Moussa SOW">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted small mb-1">Date d'enregistrement (début)</label>
                            <input type="date" name="date_enregistrement" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Spécialité</label>
                            <input type="text" name="specialite" class="form-control" placeholder="Ex: Cardiologie, Généraliste, Pédiatrie...">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Disposer d'une convention ?</label>
                            <select name="a_convention" class="form-select">
                                <option value="1">Oui - Praticien Conventionné</option>
                                <option value="0" selected>Non - Sans convention</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" placeholder="+221 77 ...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="docteur@exemple.com">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Adresse du cabinet / clinique</label>
                            <textarea name="adresse" class="form-control" rows="2" placeholder="Adresse complète..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Édition Praticien -->
<div class="modal fade" id="editPraticienModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-warning me-2"></i>Modifier Praticien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editPraticienForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Code Praticien</label>
                            <input type="text" name="code_praticien" id="edit_code_praticien" class="form-control font-monospace">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Code Comptable Praticien</label>
                            <input type="text" name="compte_comptable" id="edit_compte_comptable" class="form-control font-monospace">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-medium text-muted small mb-1">Nom complet *</label>
                            <input type="text" name="nom" id="edit_nom" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted small mb-1">Date d'enregistrement</label>
                            <input type="date" name="date_enregistrement" id="edit_date_enregistrement" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Spécialité</label>
                            <input type="text" name="specialite" id="edit_specialite" class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Praticien conventionné</label>
                            <select name="a_convention" id="edit_a_convention" class="form-select">
                                <option value="1">Oui - Conventionné</option>
                                <option value="0">Non - Non conventionné</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Téléphone</label>
                            <input type="text" name="telephone" id="edit_telephone" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium text-muted small mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-medium text-muted small mb-1">Adresse</label>
                            <textarea name="adresse" id="edit_adresse" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Gestion Conventions et Pièces Jointes -->
<div class="modal fade" id="conventionsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold"><i class="bi bi-file-earmark-check text-primary me-2"></i>Conventions & Documents</h5>
                    <div class="text-muted small">Praticien : <span id="convPraticienNom" class="fw-bold text-dark"></span></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Add Convention Form with File Upload -->
                <form action="{{ route('conventions.store') }}" method="POST" enctype="multipart/form-data" class="mb-4 bg-light p-3 rounded-4 border">
                    @csrf
                    <input type="hidden" name="id_praticien" id="conv_id_praticien">
                    <input type="hidden" name="type_partenaire" value="praticien">
                    <h6 class="fw-bold mb-3 small text-uppercase text-primary"><i class="bi bi-plus-circle me-1"></i>Enregistrer une nouvelle convention</h6>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small mb-1 fw-medium">Date début *</label>
                            <input type="date" name="date_debut" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-1 fw-medium">Date fin</label>
                            <input type="date" name="date_fin" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-1 fw-medium">Statut</label>
                            <select name="statut" class="form-select form-select-sm">
                                <option value="active" selected>Active</option>
                                <option value="suspendue">Suspendue</option>
                                <option value="resiliee">Résiliée</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1 fw-medium">Document / Contrat (PDF, Image)</label>
                            <input type="file" name="document" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx">
                        </div>
                        <div class="col-12 mt-2 d-flex justify-content-between align-items-center">
                            <input type="text" name="observations" class="form-control form-control-sm me-2" placeholder="Observations ou références de la convention...">
                            <button type="submit" class="btn btn-primary btn-sm px-3 text-nowrap"><i class="bi bi-save me-1"></i>Ajouter</button>
                        </div>
                    </div>
                </form>

                <!-- Conventions List -->
                <h6 class="fw-bold mb-2 small text-uppercase text-muted"><i class="bi bi-clock-history me-1"></i>Historique des conventions & documents associés</h6>
                <div class="list-group list-group-flush border rounded-3 overflow-hidden" id="conventionsList">
                    <!-- Populated by JS -->
                </div>
            </div>
        </div>
    </div>
</div>
@endcanedit
@endsection

@push('scripts')
<script>
    function editPraticien(praticien) {
        document.getElementById('edit_nom').value = praticien.nom || praticien.NOMPRAT || '';
        document.getElementById('edit_code_praticien').value = praticien.code_praticien || praticien.CODEPRAT || '';
        document.getElementById('edit_compte_comptable').value = praticien.code_comptable || praticien.compte_comptable || praticien.COMPTE_COMPTABLE || '';
        document.getElementById('edit_date_enregistrement').value = praticien.date_enregistrement || praticien.DATE_ENREGISTREMENT || '';
        document.getElementById('edit_a_convention').value = (praticien.a_convention || praticien.A_CONVENTION) ? '1' : '0';
        document.getElementById('edit_specialite').value = praticien.specialite || praticien.SPECIALITE || '';
        document.getElementById('edit_telephone').value = praticien.telephone || praticien.TELPRAT || '';
        document.getElementById('edit_email').value = praticien.email || praticien.EMAILPRAT || '';
        document.getElementById('edit_adresse').value = praticien.adresse || praticien.ADRPRAT || '';
        
        const idPrat = praticien.id_praticien || praticien.PRCLEUNIK;
        document.getElementById('editPraticienForm').action = `/praticiens/${idPrat}`;
        
        new bootstrap.Modal(document.getElementById('editPraticienModal')).show();
    }

    function openConventionsModal(idPraticien, nomPraticien, conventions) {
        document.getElementById('convPraticienNom').textContent = nomPraticien;
        document.getElementById('conv_id_praticien').value = idPraticien;
        
        const listContainer = document.getElementById('conventionsList');
        listContainer.innerHTML = '';

        if(!conventions || conventions.length === 0) {
            listContainer.innerHTML = '<div class="list-group-item text-center text-muted py-4">Aucune convention enregistrée pour ce praticien.</div>';
        } else {
            conventions.sort((a, b) => new Date(b.date_debut || 0) - new Date(a.date_debut || 0));
            
            conventions.forEach(conv => {
                const isActive = conv.statut === 'active';
                const badgeClass = isActive ? 'bg-success text-success border-success-subtle' : 'bg-secondary text-secondary border-secondary-subtle';
                const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
                
                // Pièces jointes rattachées
                let piecesHtml = '';
                if (conv.pieces_jointes && conv.pieces_jointes.length > 0) {
                    piecesHtml = '<div class="mt-2 d-flex flex-wrap gap-2">';
                    conv.pieces_jointes.forEach(pj => {
                        piecesHtml += `
                            <a href="/pieces-jointes/${pj.id_piece}/download" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2" title="Télécharger document">
                                <i class="bi bi-paperclip me-1"></i>${pj.nom_fichier}
                            </a>
                            <a href="/pieces-jointes/${pj.id_piece}/view" target="_blank" class="btn btn-xs btn-light btn-sm py-0 px-2 text-muted" title="Consulter">
                                <i class="bi bi-eye"></i>
                            </a>
                        `;
                    });
                    piecesHtml += '</div>';
                } else {
                    piecesHtml = '<div class="small text-muted mt-1 fst-italic"><i class="bi bi-file-earmark-x me-1"></i>Aucun document numérisé</div>';
                }

                const html = `
                    <div class="list-group-item p-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <span class="badge ${badgeClass} bg-opacity-10 border me-2">${conv.statut ? conv.statut.toUpperCase() : 'ACTIVE'}</span>
                                <strong>Du ${formatDate(conv.date_debut)} au ${formatDate(conv.date_fin)}</strong>
                                ${conv.observations ? `<div class="small text-muted mt-1"><i class="bi bi-chat-left-text me-1"></i>${conv.observations}</div>` : ''}
                                ${piecesHtml}
                            </div>
                            <div class="d-flex gap-1 align-items-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="toggleReplaceDoc(${conv.id_convention})" title="Remplacer / joindre un document">
                                    <i class="bi bi-upload"></i>
                                </button>
                                <form action="/conventions/${conv.id_convention}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette convention ?');">
                                    <input type="hidden" name="_token" value="${csrf}">
                                    <input type="hidden" name="_method" value="DELETE">
                                    <button type="submit" class="btn btn-sm btn-light text-danger" title="Supprimer convention"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </div>

                        <!-- Formulaire de remplacement de document -->
                        <div id="replace_doc_${conv.id_convention}" class="mt-3 p-2 bg-white rounded border d-none">
                            <form action="/conventions/${conv.id_convention}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="_token" value="${csrf}">
                                <input type="hidden" name="_method" value="PUT">
                                <input type="hidden" name="statut" value="${conv.statut || 'active'}">
                                <input type="file" name="document" class="form-control form-control-sm" required>
                                <button type="submit" class="btn btn-sm btn-success text-nowrap"><i class="bi bi-check-lg me-1"></i>Remplacer</button>
                            </form>
                        </div>
                    </div>
                `;
                listContainer.insertAdjacentHTML('beforeend', html);
            });
        }
        
        new bootstrap.Modal(document.getElementById('conventionsModal')).show();
    }

    function toggleReplaceDoc(idConv) {
        const box = document.getElementById(`replace_doc_${idConv}`);
        if(box) {
            box.classList.toggle('d-none');
        }
    }

    function formatDate(dateStr) {
        if(!dateStr) return 'indéterminée';
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR');
    }
</script>
@endpush

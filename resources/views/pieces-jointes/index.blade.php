@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- En-tête principal -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 text-gray-800"><i class="fas fa-folder-open text-primary me-2"></i>Gestion Documentaire</h1>
            <p class="text-muted small mb-0">Documents d'adhérents, participants, prises en charge et factures avec sauvegarde Cloud Drive sécurisée.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('pieces-jointes.sync-all-cloud') }}" method="POST" class="d-inline" onsubmit="return confirm('Lancer la sauvegarde de tous les documents non synchronisés vers le Cloud Drive ?');">
                @csrf
                <button type="submit" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Sauvegarder tout sur Drive
                </button>
            </form>
            <a href="{{ route('pieces-jointes.export', request()->query()) }}" class="btn btn-success shadow-sm rounded-pill px-3">
                <i class="fas fa-file-excel me-2"></i>Exporter Excel
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show rounded-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show rounded-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Cartes d'indicateurs de synchronisation Cloud -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-files fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Documents</small>
                        <h4 class="mb-0 fw-bold">{{ $cloudStats['documents']['total'] ?? $pieces->total() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 me-3">
                        <i class="bi bi-cloud-check-fill fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Sur Cloud (Drive)</small>
                        <h4 class="mb-0 fw-bold text-success">{{ $cloudStats['documents']['synced'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 me-3">
                        <i class="bi bi-cloud-arrow-up-fill fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">En Attente</small>
                        <h4 class="mb-0 fw-bold text-warning">{{ $cloudStats['documents']['pending'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="bi bi-hdd-network fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Volume Documents</small>
                        <h4 class="mb-0 fw-bold text-info">{{ $cloudStats['documents']['size'] ?? '0 B' }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Formulaire de filtre -->
            <form action="{{ route('pieces-jointes.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Recherche dynamique</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Nom de fichier, utilisateur..." value="{{ request('search') }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Catégorie</label>
                    <select name="id_categorie" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id_categorie }}" {{ request('id_categorie') == $cat->id_categorie ? 'selected' : '' }}>
                                {{ $cat->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Statut Cloud Drive</label>
                    <select name="cloud_synced" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">Tous les statuts</option>
                        <option value="1" {{ request('cloud_synced') === '1' ? 'selected' : '' }}> Sauvegardé sur le Cloud</option>
                        <option value="0" {{ request('cloud_synced') === '0' ? 'selected' : '' }}> En attente de sauvegarde</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted text-uppercase">Par page</label>
                    <select name="per_page" id="per_page" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5 / page</option>
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10 / page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 / page</option>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fichier</th>
                            <th>Taille</th>
                            <th>Catégorie</th>
                            <th>Entité Associée</th>
                            <th>Sauvegarde Cloud</th>
                            <th>Ajouté le & Heure</th>
                            <th>Par</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pieces as $piece)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if(str_contains($piece->type_fichier, 'pdf'))
                                            <i class="fas fa-file-pdf text-danger fs-4 me-3"></i>
                                        @elseif(str_contains($piece->type_fichier, 'image'))
                                            <i class="fas fa-file-image text-info fs-4 me-3"></i>
                                        @else
                                            <i class="fas fa-file text-secondary fs-4 me-3"></i>
                                        @endif
                                        <div>
                                            <strong class="text-dark">{{ Str::limit($piece->nom_fichier, 35) }}</strong>
                                            @if($piece->sha256)
                                                <small class="d-block text-muted font-monospace" style="font-size: 0.7rem;" title="SHA-256: {{ $piece->sha256 }}">
                                                    SHA: {{ substr($piece->sha256, 0, 10) }}...
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-secondary border font-monospace">{{ $piece->taille_lisible }}</span>
                                </td>
                                <td><span class="badge bg-secondary">{{ $piece->categorie->libelle ?? 'Non classé' }}</span></td>
                                <td>
                                    @if($piece->attachable)
                                        @php
                                            $modelClass = class_basename($piece->attachable_type);
                                            $name = '';
                                            if($modelClass == 'Salarie' || $modelClass == 'AyantDroit') {
                                                $name = $piece->attachable->prenom . ' ' . $piece->attachable->nom;
                                            } elseif($modelClass == 'Demande') {
                                                $name = 'Demande #' . $piece->attachable->id_demande;
                                            } elseif($modelClass == 'Entreprise') {
                                                $name = $piece->attachable->raison_sociale;
                                            } elseif($modelClass == 'Prestation') {
                                                $name = 'Prestation #' . $piece->attachable->id_prestation;
                                            }
                                        @endphp
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">{{ $modelClass }}</span>
                                        <div class="small text-muted mt-1">{{ $name }}</div>
                                    @else
                                        <span class="text-muted fst-italic">Entité orpheline</span>
                                    @endif
                                </td>
                                <td>
                                    @if($piece->cloud_synced)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle px-2 py-1">
                                            <i class="bi bi-cloud-check-fill me-1"></i> Drive OK
                                        </span>
                                        @if($piece->cloud_synced_at)
                                            <small class="d-block text-muted mt-1" style="font-size: 0.72rem;">@formatDateTime($piece->cloud_synced_at)</small>
                                        @endif
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle px-2 py-1">
                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> En attente
                                        </span>
                                    @endif
                                </td>
                                <td class="text-nowrap"><i class="bi bi-clock me-1 text-muted"></i>@formatDateTime($piece->date_ajout)</td>
                                <td>{{ $piece->utilisateur->nom ?? 'Système' }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Actions pièce jointe">
                                        @if(str_contains($piece->type_fichier, 'pdf') || str_contains($piece->type_fichier, 'image'))
                                            <button class="btn btn-outline-info" onclick="previewDocument('{{ route('pieces-jointes.show', $piece->id_piece) }}', '{{ $piece->type_fichier }}', '{{ $piece->nom_fichier }}')" title="Aperçu">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        @endif
                                        
                                        <a href="{{ route('pieces-jointes.download', $piece->id_piece) }}" class="btn btn-outline-primary" target="_blank" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>

                                        @if($piece->cloud_url)
                                            <a href="{{ $piece->cloud_url }}" class="btn btn-outline-success" target="_blank" title="Ouvrir sur Google Drive">
                                                <i class="bi bi-google"></i>
                                            </a>
                                        @else
                                            <form action="{{ route('pieces-jointes.sync-cloud', $piece->id_piece) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-secondary" title="Synchroniser sur le Cloud Drive">
                                                    <i class="bi bi-cloud-arrow-up"></i>
                                                </button>
                                            </form>
                                        @endif

                                        <form action="{{ route('pieces-jointes.destroy', $piece->id_piece) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer définitivement ce document ?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fs-1 mb-3 text-light"></i>
                                    <p>Aucun document trouvé.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    Affichage de {{ $pieces->firstItem() ?? 0 }} à {{ $pieces->lastItem() ?? 0 }} sur {{ $pieces->total() }} documents
                </div>
                <div>
                    {{ $pieces->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Prévisualisation -->
<div class="modal fade" id="modalPreviewPiece" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="previewTitle">Prévisualisation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 text-center bg-light" id="previewContainer" style="height: 80vh; overflow:hidden;">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function previewDocument(url, mimeType, title) {
        document.getElementById('previewTitle').innerText = title;
        const container = document.getElementById('previewContainer');
        
        container.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><div class="spinner-border text-primary" role="status"></div></div>';
        
        const previewModal = new bootstrap.Modal(document.getElementById('modalPreviewPiece'));
        previewModal.show();

        setTimeout(() => {
            if (mimeType.includes('image')) {
                container.innerHTML = `<img src="${url}" class="img-fluid h-100" style="object-fit: contain;" alt="${title}">`;
            } else if (mimeType.includes('pdf')) {
                container.innerHTML = `<iframe src="${url}" width="100%" height="100%" frameborder="0"></iframe>`;
            }
        }, 500);
    }
</script>
@endpush

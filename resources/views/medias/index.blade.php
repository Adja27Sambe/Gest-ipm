@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- En-tête Principal Unifié -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-bold">
                    <i class="bi bi-collection-play-fill me-1"></i> Module Médias & Documents
                </span>
                <span class="badge {{ ($cloudStats['drive_connected'] ?? false) ? 'bg-success' : 'bg-secondary' }} px-3 py-2 rounded-pill">
                    <i class="bi bi-google me-1"></i> {{ ($cloudStats['drive_connected'] ?? false) ? 'Google Drive Connecté' : 'Coffre-fort Miroir Local' }}
                </span>
            </div>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">Médiathèque & Gestion Documentaire</h1>
            <p class="text-muted small mb-0">Centre unifié de gestion, d'optimisation et de sauvegarde Cloud (Google Drive) de tous les médias et documents.</p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @if($activeTab === 'medias')
                <form action="{{ route('medias.sync-all-cloud') }}" method="POST" class="d-inline" onsubmit="return confirm('Sauvegarder tous les médias vers Google Drive ?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Sauvegarder Médias sur Drive
                    </button>
                </form>
                <button type="button" class="btn btn-primary shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                    <i class="bi bi-cloud-plus-fill me-2"></i>Uploader des médias
                </button>
            @elseif($activeTab === 'documents')
                <form action="{{ route('pieces-jointes.sync-all-cloud') }}" method="POST" class="d-inline" onsubmit="return confirm('Sauvegarder tous les documents vers Google Drive ?');">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Sauvegarder Documents sur Drive
                    </button>
                </form>
                <a href="{{ route('pieces-jointes.export', request()->query()) }}" class="btn btn-success shadow-sm rounded-pill px-3">
                    <i class="fas fa-file-excel me-2"></i>Exporter Excel
                </a>
                <button type="button" class="btn btn-primary shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                    <i class="bi bi-file-earmark-plus-fill me-2"></i>Nouveau Document
                </button>
            @else
                <form action="{{ route('medias.sync-everything') }}" method="POST" class="d-inline" onsubmit="return confirm('Lancer la sauvegarde intégrale de tous les médias et documents vers Google Drive ?');">
                    @csrf
                    <button type="submit" class="btn btn-primary shadow-sm rounded-pill px-4">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Sauvegarder Tout sur Google Drive
                    </button>
                </form>
            @endif
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

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Cartes KPI Globales de Synchronisation Cloud & Volume -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-files fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Fichiers Gérés</small>
                        <h4 class="mb-0 fw-bold">{{ $cloudStats['total_files'] ?? ($medias->total() + $pieces->total()) }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ $cloudStats['medias']['total'] ?? $medias->total() }} médias &bull; {{ $cloudStats['documents']['total'] ?? $pieces->total() }} docs
                        </small>
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
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Sur Cloud (Drive)</small>
                        <h4 class="mb-0 fw-bold text-success">{{ $cloudStats['total_synced'] ?? 0 }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ $cloudStats['medias']['synced'] ?? 0 }} médias &bull; {{ $cloudStats['documents']['synced'] ?? 0 }} docs
                        </small>
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
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">En Attente Cloud</small>
                        <h4 class="mb-0 fw-bold text-warning">{{ $cloudStats['total_pending'] ?? 0 }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">
                            {{ $cloudStats['medias']['pending'] ?? 0 }} médias &bull; {{ $cloudStats['documents']['pending'] ?? 0 }} docs
                        </small>
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
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Espace Occupé</small>
                        <h4 class="mb-0 fw-bold text-info">{{ $cloudStats['total_size_readable'] ?? '0 B' }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">Médias + Documents</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Barre d'Onglets de Navigation Intégrée -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-2">
            <ul class="nav nav-pills nav-fill gap-2" id="mediaDocumentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link rounded-pill py-2 {{ $activeTab === 'medias' ? 'active bg-primary text-white shadow-sm' : 'text-dark bg-light' }}" href="{{ route('medias.index', ['tab' => 'medias']) }}">
                        <i class="bi bi-images me-2"></i>Médiathèque (Images & Vidéos)
                        <span class="badge {{ $activeTab === 'medias' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} ms-2 rounded-pill">
                            {{ $cloudStats['medias']['total'] ?? $medias->total() }}
                        </span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link rounded-pill py-2 {{ $activeTab === 'documents' ? 'active bg-primary text-white shadow-sm' : 'text-dark bg-light' }}" href="{{ route('medias.index', ['tab' => 'documents']) }}">
                        <i class="bi bi-folder2-open me-2"></i>Documents & Pièces Jointes
                        <span class="badge {{ $activeTab === 'documents' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} ms-2 rounded-pill">
                            {{ $cloudStats['documents']['total'] ?? $pieces->total() }}
                        </span>
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link rounded-pill py-2 {{ $activeTab === 'cloud' ? 'active bg-primary text-white shadow-sm' : 'text-dark bg-light' }}" href="{{ route('medias.index', ['tab' => 'cloud']) }}">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Sauvegarde Google Drive & État
                        <span class="badge {{ $activeTab === 'cloud' ? 'bg-white text-primary' : 'bg-secondary bg-opacity-25 text-secondary' }} ms-2 rounded-pill">
                            {{ ($cloudStats['drive_connected'] ?? false) ? 'Drive OK' : 'Miroir' }}
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- ONGLET 1 : MÉDIATHÈQUE (IMAGES & VIDÉOS)   -->
    <!-- ========================================== -->
    @if($activeTab === 'medias')
        <div class="card border-0 shadow-sm mb-4 rounded-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('medias.index') }}" class="row g-3 align-items-center">
                    <input type="hidden" name="tab" value="medias">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Recherche dynamique médias (titre, nom de fichier)..." value="{{ request('search') }}" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="cloud_synced" class="form-select bg-light border-0" onchange="this.form.submit()">
                            <option value="">Tous les statuts Cloud</option>
                            <option value="1" {{ request('cloud_synced') === '1' ? 'selected' : '' }}> Sauvegardé sur le Cloud</option>
                            <option value="0" {{ request('cloud_synced') === '0' ? 'selected' : '' }}> En attente de sauvegarde</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-center justify-content-end">
                        <label for="per_page" class="me-2 text-muted small fw-medium text-nowrap">Afficher :</label>
                        <select name="per_page" id="per_page" class="form-select bg-light border-0" style="width: 110px;" onchange="this.form.submit()">
                            <option value="8" {{ request('per_page', 8) == 8 ? 'selected' : '' }}>8 / page</option>
                            <option value="16" {{ request('per_page') == 16 ? 'selected' : '' }}>16 / page</option>
                            <option value="24" {{ request('per_page') == 24 ? 'selected' : '' }}>24 / page</option>
                            <option value="48" {{ request('per_page') == 48 ? 'selected' : '' }}>48 / page</option>
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <div class="row g-4">
            @forelse($medias as $media)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="card h-100 border-0 shadow-sm overflow-hidden position-relative group-hover rounded-4">
                        <div class="bg-light d-flex align-items-center justify-content-center position-relative" style="height: 180px;">
                            @if(Str::startsWith($media->type_mime, 'image/'))
                                <img src="{{ $media->url }}" class="img-fluid h-100 w-100" style="object-fit: cover;" alt="{{ $media->texte_alternatif ?? $media->titre }}">
                            @elseif(Str::startsWith($media->type_mime, 'video/'))
                                <i class="bi bi-play-circle text-secondary fs-1"></i>
                            @elseif($media->type_mime == 'application/pdf')
                                <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
                            @else
                                <i class="bi bi-file-earmark text-secondary fs-1"></i>
                            @endif

                            <!-- Badge Cloud en haut à droite de l'image -->
                            <div class="position-absolute top-0 end-0 m-2">
                                @if($media->cloud_synced)
                                    <span class="badge bg-success shadow-sm" title="Synchronisé sur Google Drive">
                                        <i class="bi bi-cloud-check-fill"></i>
                                    </span>
                                @else
                                    <span class="badge bg-warning shadow-sm" title="En attente de synchronisation Cloud">
                                        <i class="bi bi-cloud-arrow-up-fill"></i>
                                    </span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="card-body p-3">
                            <h6 class="text-truncate mb-1 fw-bold text-dark" title="{{ $media->titre }}">{{ $media->titre }}</h6>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted font-monospace">{{ $media->taille_lisible }}</small>
                                <small class="badge bg-light text-dark border">{{ strtoupper(explode('/', $media->type_mime)[1] ?? 'FILE') }}</small>
                            </div>
                            @if($media->dimensions)
                                <small class="d-block text-muted mt-1" style="font-size: 0.72rem;"><i class="bi bi-aspect-ratio me-1"></i>{{ $media->dimensions }}</small>
                            @endif
                        </div>

                        <!-- Overlay d'actions au survol -->
                        <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex flex-column align-items-center justify-content-center opacity-0 transition-opacity" style="opacity: 0; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                            <button class="btn btn-sm btn-light mb-2 w-75 shadow-sm" onclick="copyToClipboard('{{ $media->url }}')">
                                <i class="bi bi-link-45deg me-1"></i> Copier l'URL
                            </button>
                            
                            @if(Str::startsWith($media->type_mime, 'image/'))
                            <a href="{{ $media->url }}" target="_blank" class="btn btn-sm btn-primary mb-2 w-75 shadow-sm">
                                <i class="bi bi-eye me-1"></i> Voir
                            </a>
                            @endif

                            @if(!$media->cloud_synced)
                                <form action="{{ route('medias.sync-cloud', $media->id_media) }}" method="POST" class="w-75 mb-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-light w-100 shadow-sm">
                                        <i class="bi bi-cloud-arrow-up me-1"></i> Sauvegarder Drive
                                    </button>
                                </form>
                            @elseif($media->cloud_url)
                                <a href="{{ $media->cloud_url }}" target="_blank" class="btn btn-sm btn-outline-success w-75 mb-2 shadow-sm">
                                    <i class="bi bi-google me-1"></i> Ouvrir sur Drive
                                </a>
                            @endif

                            <form action="{{ route('medias.destroy', $media) }}" method="POST" class="w-75" onsubmit="return confirm('Supprimer définitivement ce média ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100 shadow-sm">
                                    <i class="bi bi-trash me-1"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 bg-white rounded-4 shadow-sm">
                        <i class="bi bi-images display-1 text-light mb-3"></i>
                        <h4>La médiathèque est vide</h4>
                        <p class="text-muted mb-4">Uploadez des images, photos, logos ou bannières pour les sécuriser sur Google Drive.</p>
                        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                            <i class="bi bi-cloud-arrow-up me-2"></i>Ajouter mon premier média
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Affichage de {{ $medias->firstItem() ?? 0 }} à {{ $medias->lastItem() ?? 0 }} sur {{ $medias->total() }} médias
            </div>
            <div>
                {{ $medias->appends(array_merge(request()->query(), ['tab' => 'medias']))->links('pagination::bootstrap-5') }}
            </div>
        </div>

    <!-- ================================================== -->
    <!-- ONGLET 2 : DOCUMENTS & PIÈCES JOINTES (GED)        -->
    <!-- ================================================== -->
    @elseif($activeTab === 'documents')
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <!-- Formulaire de filtre documents -->
                <form action="{{ route('medias.index') }}" method="GET" class="row g-3 mb-4 align-items-end">
                    <input type="hidden" name="tab" value="documents">
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
                        <select name="doc_per_page" id="doc_per_page" class="form-select bg-light border-0" onchange="this.form.submit()">
                            <option value="5" {{ request('doc_per_page', 10) == 5 ? 'selected' : '' }}>5 / page</option>
                            <option value="10" {{ request('doc_per_page', 10) == 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="25" {{ request('doc_per_page', 10) == 25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ request('doc_per_page', 10) == 50 ? 'selected' : '' }}>50 / page</option>
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
                                <th>Rattaché à</th>
                                <th>Statut Cloud Drive</th>
                                <th>Date d'ajout</th>
                                <th>Auteur</th>
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
                                            @elseif(str_contains($piece->type_fichier, 'word') || str_contains($piece->type_fichier, 'officedocument.word'))
                                                <i class="fas fa-file-word text-primary fs-4 me-3"></i>
                                            @elseif(str_contains($piece->type_fichier, 'excel') || str_contains($piece->type_fichier, 'spreadsheet'))
                                                <i class="fas fa-file-excel text-success fs-4 me-3"></i>
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
                                            <span class="badge bg-light text-muted border">Document Général</span>
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
                                        <div class="btn-group btn-group-sm" role="group" aria-label="Actions document">
                                            @if(str_contains($piece->type_fichier, 'pdf') || str_contains($piece->type_fichier, 'image'))
                                                <button class="btn btn-outline-info" onclick="previewDocument('{{ route('pieces-jointes.show', $piece->id_piece) }}', '{{ $piece->type_fichier }}', '{{ addslashes($piece->nom_fichier) }}')" title="Aperçu rapide">
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
                                                    <button type="submit" class="btn btn-outline-secondary" title="Sauvegarder vers le Cloud Drive">
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
                        {{ $pieces->appends(array_merge(request()->query(), ['tab' => 'documents']))->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>

    <!-- ================================================== -->
    <!-- ONGLET 3 : SAUVEGARDE GOOGLE DRIVE & TABLEAU ÉTAT  -->
    <!-- ================================================== -->
    @elseif($activeTab === 'cloud')
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Statut de Connexion Google Drive -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="bi bi-google text-primary me-2"></i>État du Connecteur Cloud Google Drive
                        </h5>
                        @if($cloudStats['drive_connected'] ?? false)
                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                <i class="bi bi-check-circle-fill me-1"></i> Connecté & Actif
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                <i class="bi bi-shield-check me-1"></i> Coffre-fort Miroir Local (Actif)
                            </span>
                        @endif
                    </div>
                    <div class="card-body p-4 pt-0">
                        <div class="p-3 rounded-4 {{ ($cloudStats['drive_connected'] ?? false) ? 'bg-success bg-opacity-10 border border-success-subtle' : 'bg-light border' }} mb-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="fs-1 {{ ($cloudStats['drive_connected'] ?? false) ? 'text-success' : 'text-primary' }}">
                                    <i class="bi {{ ($cloudStats['drive_connected'] ?? false) ? 'bi-cloud-check-fill' : 'bi-shield-shaded' }}"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">
                                        {{ ($cloudStats['drive_connected'] ?? false) ? 'Synchronisation automatique Google Drive en temps réel' : 'Mode Coffre-fort Miroir sécurisé' }}
                                    </h6>
                                    <p class="mb-0 text-muted small">
                                        @if($cloudStats['drive_connected'] ?? false)
                                            Tous les nouveaux documents et médias sont téléversés directement dans vos répertoires Google Drive <strong>Documents/</strong> et <strong>Medias/</strong> avec lien web sécurisé.
                                        @else
                                            Les fichiers sont immédiatement dupliqués et préservés dans le coffre-fort miroir (<code>storage/app/cloud_backup/</code>). Dès l'ajout des clés Google Drive dans <code>.env</code>, une synchronisation transférera l'ensemble vers Google Drive.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Tableau comparatif Médias vs Documents -->
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Répartition de la Sauvegarde</h6>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Module</th>
                                        <th>Total Fichiers</th>
                                        <th>Sur le Cloud (Drive)</th>
                                        <th>En Attente</th>
                                        <th>Volume</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <i class="bi bi-folder2-open text-primary me-2"></i><strong>Documents & GED</strong>
                                        </td>
                                        <td>{{ $cloudStats['documents']['total'] }}</td>
                                        <td><span class="badge bg-success">{{ $cloudStats['documents']['synced'] }}</span></td>
                                        <td><span class="badge bg-warning text-dark">{{ $cloudStats['documents']['pending'] }}</span></td>
                                        <td><code>{{ $cloudStats['documents']['size'] }}</code></td>
                                        <td class="text-end">
                                            <form action="{{ route('pieces-jointes.sync-all-cloud') }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-cloud-arrow-up me-1"></i> Synchroniser Documents
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <i class="bi bi-images text-info me-2"></i><strong>Médiathèque</strong>
                                        </td>
                                        <td>{{ $cloudStats['medias']['total'] }}</td>
                                        <td><span class="badge bg-success">{{ $cloudStats['medias']['synced'] }}</span></td>
                                        <td><span class="badge bg-warning text-dark">{{ $cloudStats['medias']['pending'] }}</span></td>
                                        <td><code>{{ $cloudStats['medias']['size'] }}</code></td>
                                        <td class="text-end">
                                            <form action="{{ route('medias.sync-all-cloud') }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                                    <i class="bi bi-cloud-arrow-up me-1"></i> Synchroniser Médias
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bouton d'action globale -->
                        <div class="d-flex justify-content-end mt-3">
                            <form action="{{ route('medias.sync-everything') }}" method="POST" class="d-inline" onsubmit="return confirm('Lancer la sauvegarde intégrale de tous les fichiers vers Google Drive ?');">
                                @csrf
                                <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Synchroniser Tous les Fichiers (Médias + Documents)
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne latérale droite : Informations techniques & Guide -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold mb-3"><i class="bi bi-terminal text-primary me-2"></i>Commandes En Ligne (Artisan)</h6>
                        <p class="text-muted small">Vous pouvez planifier la sauvegarde automatique via cron ou la déclencher en terminal :</p>
                        <div class="bg-dark text-light p-3 rounded-3 font-monospace small mb-3">
                            <div># Sauvegarde incrémentale</div>
                            <div class="text-warning">php artisan cloud:backup</div>
                            <div class="mt-2 text-muted"># Test de connexion Drive</div>
                            <div class="text-info">php artisan cloud:backup --test</div>
                        </div>

                        <hr class="opacity-25 my-3">

                        <h6 class="fw-bold mb-3"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Variables d'Environnement (.env)</h6>
                        <p class="text-muted small">Pour connecter votre compte Google Drive :</p>
                        <div class="bg-light p-3 rounded-3 font-monospace small text-dark border">
                            <div>CLOUD_BACKUP_DRIVER=drive</div>
                            <div>CLOUD_AUTO_BACKUP=true</div>
                            <div>GOOGLE_DRIVE_FOLDER_ID=...</div>
                            <div>GOOGLE_DRIVE_CLIENT_ID=...</div>
                            <div>GOOGLE_DRIVE_CLIENT_SECRET=...</div>
                            <div>GOOGLE_DRIVE_REFRESH_TOKEN=...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- ================================================== -->
<!-- MODAL 1 : UPLOAD MÉDIAS                            -->
<!-- ================================================== -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-labelledby="uploadMediaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="uploadMediaModalLabel">
                    <i class="bi bi-cloud-arrow-up text-primary me-2"></i>Uploader des Médias
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('medias.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="fichiers" class="form-label fw-bold">Sélectionner un ou plusieurs fichiers <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="fichiers" name="fichiers[]" multiple required accept="image/*,video/mp4,application/pdf">
                        <div class="form-text">Formats : JPG, PNG, WebP, GIF, SVG, PDF, MP4 (Max 20 Mo par fichier). Compression et optimisation automatiques.</div>
                    </div>
                    <div class="mb-3">
                        <label for="titre" class="form-label fw-bold">Titre (optionnel si plusieurs fichiers)</label>
                        <input type="text" class="form-control" id="titre" name="titre" placeholder="Titre descriptif du média">
                    </div>
                    <div class="mb-3">
                        <label for="texte_alternatif" class="form-label fw-bold">Texte alternatif (SEO / A11y)</label>
                        <input type="text" class="form-control" id="texte_alternatif" name="texte_alternatif" placeholder="Description courte pour l'accessibilité">
                    </div>
                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bi bi-cloud-check-fill text-success me-1"></i> Chaque média est optimisé, dédupliqué et sauvegardé sur le Cloud Drive.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-upload me-1"></i> Démarrer l'upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================== -->
<!-- MODAL 2 : NOUVEAU DOCUMENT / PIÈCE JOINTE          -->
<!-- ================================================== -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="uploadDocumentModalLabel">
                    <i class="bi bi-file-earmark-plus text-primary me-2"></i>Ajouter un Document (GED)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pieces-jointes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="doc_fichier" class="form-label fw-bold">Fichier du document <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="doc_fichier" name="fichier" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
                        <div class="form-text">Formats autorisés : PDF, JPEG, PNG, Word, Excel (Max 20 Mo). Calcul automatique d'intégrité SHA-256.</div>
                    </div>
                    <div class="mb-3">
                        <label for="doc_id_categorie" class="form-label fw-bold">Catégorie du document <span class="text-danger">*</span></label>
                        <select class="form-select" id="doc_id_categorie" name="id_categorie" required>
                            <option value="">Sélectionner une catégorie...</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_categorie }}">{{ $cat->libelle }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i> Sauvegarde automatique sur Google Drive avec synchronisation temps réel.
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-upload me-1"></i> Enregistrer le document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================== -->
<!-- MODAL 3 : PRÉVISUALISATION UNIVERSELLE DOCUMENTS   -->
<!-- ================================================== -->
<div class="modal fade" id="modalPreviewPiece" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="previewTitle">Prévisualisation du document</h5>
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
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('URL copiée dans le presse-papiers !');
        }, function(err) {
            console.error('Erreur lors de la copie : ', err);
        });
    }

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
            } else {
                container.innerHTML = `<div class="d-flex flex-column justify-content-center align-items-center h-100">
                    <i class="fas fa-file text-secondary display-1 mb-3"></i>
                    <h5>Aperçu non disponible pour ce type de fichier.</h5>
                    <a href="${url}" class="btn btn-primary rounded-pill px-4 mt-2" target="_blank">
                        <i class="fas fa-download me-2"></i>Télécharger le fichier
                    </a>
                </div>`;
            }
        }, 300);
    }
</script>
@endpush

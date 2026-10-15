@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- En-tête -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h1 class="h3 mb-1 text-gray-800"><i class="bi bi-images text-primary me-2"></i>Médiathèque Globale</h1>
            <p class="text-muted small mb-0">Gestion optimisée des images, logos et médias avec sauvegarde Cloud Drive automatique.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('medias.sync-all-cloud') }}" method="POST" class="d-inline" onsubmit="return confirm('Sauvegarder tous les médias non synchronisés vers Google Drive ?');">
                @csrf
                <button type="submit" class="btn btn-outline-primary shadow-sm rounded-pill px-3">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Sauvegarder Médias sur Drive
                </button>
            </form>
            <button type="button" class="btn btn-primary shadow-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                <i class="bi bi-cloud-plus-fill me-2"></i>Uploader des médias
            </button>
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

    <!-- Cartes statistiques de synchronisation -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 me-3">
                        <i class="bi bi-image fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Total Médias</small>
                        <h4 class="mb-0 fw-bold">{{ $cloudStats['medias']['total'] ?? $medias->total() }}</h4>
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
                        <h4 class="mb-0 fw-bold text-success">{{ $cloudStats['medias']['synced'] ?? 0 }}</h4>
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
                        <h4 class="mb-0 fw-bold text-warning">{{ $cloudStats['medias']['pending'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-3 p-3 me-3">
                        <i class="bi bi-hdd fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Volume Médias</small>
                        <h4 class="mb-0 fw-bold text-info">{{ $cloudStats['medias']['size'] ?? '0 B' }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('medias.index') }}" class="row g-3 align-items-center">
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
                    <p class="text-muted mb-4">Uploadez des images, logos ou bannières pour les sauvegarder sur le Cloud Drive et les réutiliser.</p>
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
            {{ $medias->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Modal Upload Media -->
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
                        <div class="form-text">Formats acceptés : JPG, PNG, WebP, GIF, SVG, PDF, MP4 (Max 20 Mo par fichier). Optimisation et compression automatiques.</div>
                    </div>
                    <div class="mb-3">
                        <label for="titre" class="form-label fw-bold">Titre (optionnel si plusieurs fichiers)</label>
                        <input type="text" class="form-control" id="titre" name="titre" placeholder="Titre descriptif du média">
                    </div>
                    <div class="mb-3">
                        <label for="texte_alternatif" class="form-label fw-bold">Texte alternatif (Accessibilité SEO/A11y)</label>
                        <input type="text" class="form-control" id="texte_alternatif" name="texte_alternatif" placeholder="Description courte pour les lecteurs d'écran">
                    </div>
                    <div class="alert alert-light border small text-muted mb-0">
                        <i class="bi bi-cloud-check-fill text-success me-1"></i> Chaque média uploadé est immédiatement dupliqué et sécurisé dans le Cloud Drive.
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
</script>
@endpush

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="bi bi-images text-primary me-2"></i>Médiathèque Globale</h1>
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
            <i class="bi bi-cloud-arrow-up me-2"></i>Uploader des médias
        </button>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        @forelse($medias as $media)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 border-0 shadow-sm overflow-hidden position-relative group-hover">
                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 180px;">
                        @if(Str::startsWith($media->type_mime, 'image/'))
                            <img src="{{ $media->url }}" class="img-fluid h-100 w-100" style="object-fit: cover;" alt="{{ $media->texte_alternatif ?? $media->titre }}">
                        @elseif(Str::startsWith($media->type_mime, 'video/'))
                            <i class="bi bi-play-circle text-secondary fs-1"></i>
                        @elseif($media->type_mime == 'application/pdf')
                            <i class="bi bi-file-earmark-pdf text-danger fs-1"></i>
                        @else
                            <i class="bi bi-file-earmark text-secondary fs-1"></i>
                        @endif
                    </div>
                    
                    <div class="card-body p-3">
                        <h6 class="text-truncate mb-1" title="{{ $media->titre }}">{{ $media->titre }}</h6>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <small class="text-muted">{{ number_format($media->taille / 1024, 1) }} KB</small>
                            <small class="badge bg-light text-dark border">{{ strtoupper(explode('/', $media->type_mime)[1] ?? 'FILE') }}</small>
                        </div>
                    </div>

                    <!-- Overlay d'actions au survol -->
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex flex-column align-items-center justify-content-center opacity-0 transition-opacity" style="opacity: 0; transition: 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                        <button class="btn btn-sm btn-light mb-2 w-75" onclick="copyToClipboard('{{ $media->url }}')">
                            <i class="bi bi-link-45deg me-1"></i> Copier l'URL
                        </button>
                        
                        @if(Str::startsWith($media->type_mime, 'image/'))
                        <a href="{{ $media->url }}" target="_blank" class="btn btn-sm btn-primary mb-2 w-75">
                            <i class="bi bi-eye me-1"></i> Voir
                        </a>
                        @endif

                        <form action="{{ route('medias.destroy', $media) }}" method="POST" class="w-75" onsubmit="return confirm('Supprimer définitivement ce média ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger w-100">
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
                    <p class="text-muted mb-4">Uploadez des images, logos ou bannières pour les réutiliser dans l'application.</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadMediaModal">
                        <i class="bi bi-cloud-arrow-up me-2"></i>Ajouter mon premier média
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $medias->links() }}
    </div>
</div>
@endsection

@section('modals')
<!-- Modal Upload -->
<div class="modal fade" id="uploadMediaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('medias.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-primary"><i class="bi bi-cloud-upload me-2"></i>Uploader des médias</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-medium">Sélectionner des fichiers (Images, PDF, MP4)</label>
                        <input class="form-control bg-light border-0" type="file" name="fichiers[]" multiple required accept="image/*,application/pdf,video/mp4">
                        <div class="form-text">Vous pouvez sélectionner plusieurs fichiers en même temps (Max 20 Mo / fichier).</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-medium">Titre générique (Optionnel)</label>
                        <input type="text" name="titre" class="form-control bg-light border-0" placeholder="Ex: Logo Entreprise X">
                        <div class="form-text">Si laissé vide, le nom d'origine du fichier sera utilisé.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Uploader</button>
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
            alert('URL copiée dans le presse-papier !');
        }, function(err) {
            console.error('Erreur lors de la copie : ', err);
        });
    }
</script>
@endpush

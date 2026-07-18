@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-folder-open text-primary me-2"></i>Gestion Documentaire</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}</div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <!-- Formulaire de filtre -->
            <form action="{{ route('pieces-jointes.index') }}" method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Filtrer par Catégorie</label>
                    <select name="id_categorie" class="form-select bg-light border-0" onchange="this.form.submit()">
                        <option value="">Toutes les catégories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id_categorie }}" {{ request('id_categorie') == $cat->id_categorie ? 'selected' : '' }}>
                                {{ $cat->libelle }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Fichier</th>
                            <th>Catégorie</th>
                            <th>Entité Associée</th>
                            <th>Ajouté le</th>
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
                                            <strong>{{ Str::limit($piece->nom_fichier, 40) }}</strong>
                                        </div>
                                    </div>
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
                                            }
                                        @endphp
                                        <span class="badge bg-primary bg-opacity-10 text-primary">{{ $modelClass }}</span>
                                        <div class="small text-muted mt-1">{{ $name }}</div>
                                    @else
                                        <span class="text-muted fst-italic">Entité orpheline</span>
                                    @endif
                                </td>
                                <td>{{ \Carbon\Carbon::parse($piece->date_ajout)->format('d/m/Y H:i') }}</td>
                                <td>{{ $piece->utilisateur->nom ?? 'Système' }}</td>
                                <td class="text-end">
                                    @if(str_contains($piece->type_fichier, 'pdf') || str_contains($piece->type_fichier, 'image'))
                                        <button class="btn btn-sm btn-outline-info me-1" onclick="previewDocument('{{ route('pieces-jointes.show', $piece->id_piece) }}', '{{ $piece->type_fichier }}', '{{ $piece->nom_fichier }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    
                                    <a href="{{ route('pieces-jointes.download', $piece->id_piece) }}" class="btn btn-sm btn-outline-primary me-1" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fs-1 mb-3 text-light"></i>
                                    <p>Aucun document trouvé.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4">
                {{ $pieces->links() }}
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

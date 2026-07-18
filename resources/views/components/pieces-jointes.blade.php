@props(['model'])

@php
    $categories = \App\Models\CategorieDocument::orderBy('libelle')->get();
    $pieces = $model->piecesJointes()->with(['categorie', 'utilisateur'])->latest('date_ajout')->get();
@endphp

<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-primary"><i class="fas fa-folder-open me-2"></i> Documents & Pièces Jointes</h5>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddPiece">
            <i class="fas fa-plus"></i> Ajouter un document
        </button>
    </div>
    <div class="card-body p-0">
        @if($pieces->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fichier</th>
                            <th>Catégorie</th>
                            <th>Ajouté le</th>
                            <th>Par</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pieces as $piece)
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
                                            <strong>{{ Str::limit($piece->nom_fichier, 40) }}</strong><br>
                                            <small class="text-muted">{{ strtoupper(explode('/', $piece->type_fichier)[1] ?? 'Fichier') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge bg-secondary">{{ $piece->categorie->libelle ?? 'Non classé' }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($piece->date_ajout)->format('d/m/Y H:i') }}</td>
                                <td>{{ $piece->utilisateur->nom ?? 'Système' }}</td>
                                <td class="text-end">
                                    <!-- Prévisualisation si PDF ou Image -->
                                    @if(str_contains($piece->type_fichier, 'pdf') || str_contains($piece->type_fichier, 'image'))
                                        <button class="btn btn-sm btn-outline-info me-1" onclick="previewDocument('{{ route('pieces-jointes.show', $piece->id_piece) }}', '{{ $piece->type_fichier }}', '{{ $piece->nom_fichier }}')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @endif
                                    
                                    <a href="{{ route('pieces-jointes.download', $piece->id_piece) }}" class="btn btn-sm btn-outline-primary me-1" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    
                                    <form action="{{ route('pieces-jointes.destroy', $piece->id_piece) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette pièce jointe ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5 text-muted">
                <i class="fas fa-folder-open fs-1 mb-3 text-light"></i>
                <p>Aucun document attaché pour le moment.</p>
            </div>
        @endif
    </div>
</div>

<!-- Modal Ajout Pièce Jointe -->
<div class="modal fade" id="modalAddPiece" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('pieces-jointes.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-light">
                    <h5 class="modal-title text-primary"><i class="fas fa-upload me-2"></i> Téléverser un document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    
                    <!-- Champs Polymorphes Cachés -->
                    <input type="hidden" name="attachable_type" value="{{ get_class($model) }}">
                    <input type="hidden" name="attachable_id" value="{{ $model->getKey() }}">

                    <div class="mb-4">
                        <label class="form-label fw-bold">Catégorie du document <span class="text-danger">*</span></label>
                        <select name="id_categorie" class="form-select shadow-sm" required>
                            <option value="">-- Sélectionner une catégorie --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id_categorie }}">{{ $cat->libelle }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Fichier (PDF, JPG, PNG) <span class="text-danger">*</span></label>
                        <input type="file" name="fichier" class="form-control shadow-sm" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="form-text mt-2"><i class="fas fa-info-circle me-1"></i> Taille maximale autorisée : 10 Mo.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Enregistrer</button>
                </div>
            </form>
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
                <!-- Content injected via JS -->
            </div>
        </div>
    </div>
</div>

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

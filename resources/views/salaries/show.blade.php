@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('salaries.index') }}" class="btn btn-link text-decoration-none text-secondary p-0 mb-2">
                <i class="bi bi-arrow-left"></i> Retour aux salariés
            </a>
            <h1 class="h3 mb-0 text-gray-800">Dossier Famille : {{ $salarie->prenom }} {{ $salarie->nom }}</h1>
        </div>
        <div>
            <a href="{{ route('salaries.edit', $salarie) }}" class="btn btn-light text-primary border shadow-sm rounded-3">
                <i class="bi bi-pencil me-2"></i>Modifier
            </a>
        </div>
    </div>

    <!-- Alertes -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Erreur lors de l'ajout :</strong>
            <ul class="mb-0 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-4">
        <!-- Informations du Salarié -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="text-primary mb-4"><i class="bi bi-person-badge me-2"></i>Informations Salarié</h5>
                    
                    <div class="text-center mb-4">
                        @if($salarie->photo)
                            <img src="{{ $salarie->photo->url }}" alt="Photo de profil" class="rounded-circle object-fit-cover shadow-sm border border-3 border-white" style="width: 120px; height: 120px;">
                        @else
                            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center shadow-sm border border-3 border-white text-secondary" style="width: 120px; height: 120px;">
                                <i class="bi bi-person" style="font-size: 4rem;"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Matricule</small>
                        <span class="fw-medium">{{ $salarie->matricule ?? 'Non défini' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Entreprise</small>
                        <span class="fw-medium">{{ $salarie->entreprise->raison_sociale ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Statut</small>
                        @if($salarie->statut == 'actif')
                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">Actif</span>
                        @elseif($salarie->statut == 'suspendu')
                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill">Suspendu</span>
                        @else
                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill">Radié</span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Sexe</small>
                        <span class="fw-medium">{{ $salarie->sexe == 'M' ? 'Masculin' : ($salarie->sexe == 'F' ? 'Féminin' : 'Non défini') }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Date de naissance</small>
                        <span class="fw-medium">{{ $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->format('d/m/Y') : 'Non définie' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Date d'embauche</small>
                        <span class="fw-medium">{{ $salarie->date_embauche ? \Carbon\Carbon::parse($salarie->date_embauche)->format('d/m/Y') : 'Non définie' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Téléphone</small>
                        <span class="fw-medium">{{ $salarie->telephone ?? 'Non défini' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Carte Assuré -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="bi bi-credit-card-2-front me-2"></i>Carte d'Assuré (IPM)</h5>
                        <i class="bi bi-shield-check fs-2 opacity-50"></i>
                    </div>
                    
                    @if($salarie->carteAssure)
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-1 text-white opacity-75" style="letter-spacing: 2px;">{{ $salarie->carteAssure->numero_carte }}</h3>
                                <p class="mb-4 text-white-50">Délivrée le {{ \Carbon\Carbon::parse($salarie->carteAssure->date_emission)->format('d/m/Y') }}</p>
                                
                                <div class="d-flex gap-4 mb-4">
                                    <div>
                                        <small class="text-white-50 d-block">Titulaire</small>
                                        <span class="fw-bold">{{ mb_strtoupper($salarie->nom) }} {{ $salarie->prenom }}</span>
                                    </div>
                                    <div>
                                        <small class="text-white-50 d-block">Statut Carte</small>
                                        <span class="badge bg-white text-primary rounded-pill">{{ ucfirst($salarie->carteAssure->statut) }}</span>
                                    </div>
                                </div>
                                
                                <a href="{{ route('cartes-assurees.download', $salarie->carteAssure) }}" class="btn btn-light text-primary rounded-pill px-4 fw-bold">
                                    <i class="bi bi-file-earmark-pdf-fill me-2"></i>Télécharger PDF
                                </a>
                            </div>
                            <div class="col-md-4 text-center mt-3 mt-md-0">
                                <div class="bg-white p-2 rounded-3 d-inline-block shadow-sm">
                                    {!! $salarie->carteAssure->qr_code !!}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-credit-card fs-1 d-block mb-3 opacity-50"></i>
                            <p class="mb-3">Aucune carte d'assuré n'a été générée pour ce salarié.</p>
                            <form action="{{ route('cartes-assurees.generate', $salarie) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-light text-primary rounded-pill px-4 fw-bold shadow-sm">
                                    <i class="bi bi-magic me-2"></i>Générer la carte
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Liste des Ayants Droit -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="text-primary mb-0"><i class="bi bi-people-fill me-2"></i>Ayants Droit</h5>
                        @if($salarie->statut != 'radie')
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#addAyantDroitModal">
                            <i class="bi bi-plus-lg me-1"></i>Ajouter
                        </button>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nom Complet</th>
                                    <th>Lien</th>
                                    <th>Âge</th>
                                    <th>Sexe</th>
                                    <th>Statut</th>
                                    <th class="text-end pe-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($salarie->ayantsDroit as $ayant)
                                    <tr>
                                        <td class="ps-3 fw-medium">
                                            <div class="d-flex align-items-center">
                                                @if($ayant->photo)
                                                    <img src="{{ $ayant->photo->url }}" alt="Photo" class="rounded-circle object-fit-cover me-2 border" style="width: 32px; height: 32px;">
                                                @else
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2 border text-secondary" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                                        <i class="bi bi-person"></i>
                                                    </div>
                                                @endif
                                                {{ $ayant->prenom }} {{ $ayant->nom }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ ucfirst($ayant->lien_parente) }}</span>
                                        </td>
                                        <td>
                                            {{ $ayant->date_naissance ? \Carbon\Carbon::parse($ayant->date_naissance)->age . ' ans' : 'N/A' }}
                                        </td>
                                        <td>{{ $ayant->sexe }}</td>
                                        <td>
                                            @if($ayant->statut == 'actif')
                                                <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Actif</span>
                                            @else
                                                <span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Inactif</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('ayants-droit.edit', $ayant) }}" class="btn btn-sm btn-light text-secondary rounded-circle me-1" title="Modifier">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('ayants-droit.destroy', $ayant) }}" method="POST" class="d-inline" onsubmit="return confirm('Confirmer la suppression de cet ayant droit ?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light text-danger rounded-circle" title="Supprimer">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            Aucun ayant droit enregistré pour le moment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Documents et Pièces Jointes -->
            <x-pieces-jointes :model="$salarie" />
            
        </div>
    </div>
@endsection

@section('modals')
<!-- Modal Ajout Ayant Droit -->
<div class="modal fade" id="addAyantDroitModal" tabindex="-1" aria-labelledby="addAyantDroitModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="{{ route('ayants-droit.store', $salarie) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id_salarie" value="{{ $salarie->id_salarie }}">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title text-primary" id="addAyantDroitModalLabel">Ajouter un Ayant Droit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Photo de profil</label>
                            <input type="file" name="photo" class="form-control bg-light border-0" accept="image/*">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Prénom</label>
                            <input type="text" name="prenom" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Nom *</label>
                            <input type="text" name="nom" class="form-control bg-light border-0" value="{{ $salarie->nom }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Lien de parenté *</label>
                            <select name="lien_parente" class="form-select bg-light border-0" required>
                                <option value="">Sélectionnez un lien</option>
                                <option value="conjoint">Conjoint(e)</option>
                                <option value="enfant">Enfant</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Sexe</label>
                            <select name="sexe" class="form-select bg-light border-0">
                                <option value="">Non spécifié</option>
                                <option value="M">Masculin</option>
                                <option value="F">Féminin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-medium">Date de naissance</label>
                            <input type="date" name="date_naissance" class="form-control bg-light border-0">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-medium">Date de mariage (Optionnel)</label>
                            <input type="date" name="date_mariage" class="form-control bg-light border-0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 shadow-sm">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

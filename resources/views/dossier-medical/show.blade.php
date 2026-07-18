@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <h2>Dossier Médical de {{ $beneficiaire->prenom }} {{ $beneficiaire->nom }}</h2>
        <a href="{{ route('dossier-medical.index') }}" class="btn btn-secondary">Retour à la recherche</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <!-- Formulaire d'ajout -->
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Nouvelle consultation</strong>
                </div>
                <div class="card-body">
                    <form action="{{ route('dossier-medical.store', ['type' => $type, 'id' => $beneficiaire->id_salarie ?? $beneficiaire->id_ayant_droit]) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Date de consultation</label>
                            <input type="date" name="date_consultation" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Prestataire (Optionnel)</label>
                            <select name="id_prestataire" class="form-select">
                                <option value="">-- Sélectionner --</option>
                                @foreach($prestataires as $prestataire)
                                    <option value="{{ $prestataire->id_prestataire }}">{{ $prestataire->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Pathologie (Optionnel)</label>
                            <select name="id_pathologie" class="form-select">
                                <option value="">-- Sélectionner --</option>
                                @foreach($pathologies as $pathologie)
                                    <option value="{{ $pathologie->id_pathologie }}">{{ $pathologie->nom }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Diagnostic</label>
                            <textarea name="diagnostic" class="form-control" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Traitement</label>
                            <textarea name="traitement" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observations</label>
                            <textarea name="observation" class="form-control" rows="2"></textarea>
                        </div>

                        <hr>
                        <h5>Prescriptions</h5>
                        <div id="prescriptions-container">
                            <div class="prescription-row mb-2 p-2 border rounded bg-light">
                                <input type="text" name="prescriptions[0][medicament]" class="form-control form-control-sm mb-1" placeholder="Médicament (ex: Paracétamol)">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="prescriptions[0][posologie]" class="form-control" placeholder="Posologie (ex: 2x/jour)">
                                    <input type="text" name="prescriptions[0][duree]" class="form-control" placeholder="Durée (ex: 5 jours)">
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" onclick="addPrescription()">+ Ajouter un autre médicament</button>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Enregistrer dans le dossier
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Historique Chronologique -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <strong>Historique Chronologique</strong>
                </div>
                <div class="card-body">
                    @forelse($historique as $entree)
                        <div class="card mb-3 border-info">
                            <div class="card-header bg-info text-white d-flex justify-content-between align-items-center py-2">
                                <strong><i class="fas fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($entree->date_consultation)->format('d/m/Y') }}</strong>
                                <span><i class="fas fa-hospital"></i> {{ $entree->prestataire->nom ?? 'Prestataire non spécifié' }}</span>
                            </div>
                            <div class="card-body">
                                @if($entree->pathologie)
                                    <span class="badge bg-danger mb-3 px-2 py-1"><i class="fas fa-virus"></i> {{ $entree->pathologie->nom }}</span>
                                @endif
                                
                                <p class="mb-1"><strong>Diagnostic :</strong> {{ $entree->diagnostic }}</p>
                                
                                @if($entree->traitement)
                                    <p class="mb-1"><strong>Traitement :</strong> {{ $entree->traitement }}</p>
                                @endif
                                
                                @if($entree->observation)
                                    <p class="mb-1 text-muted"><strong>Observations :</strong> {{ $entree->observation }}</p>
                                @endif

                                @if($entree->prescriptions && $entree->prescriptions->count() > 0)
                                    <div class="mt-3 pt-2 border-top">
                                        <h6 class="text-secondary"><i class="fas fa-pills"></i> Prescriptions</h6>
                                        <ul class="list-group list-group-flush">
                                            @foreach($entree->prescriptions as $prescription)
                                                <li class="list-group-item py-1 px-0 border-0 bg-transparent">
                                                    <strong>{{ $prescription->medicament }}</strong> 
                                                    @if($prescription->posologie) - <span class="text-muted">{{ $prescription->posologie }}</span> @endif
                                                    @if($prescription->duree) <em>({{ $prescription->duree }})</em> @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info text-center m-0">
                            Aucun antécédent médical n'a encore été enregistré pour ce bénéficiaire.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let presIdx = 1;
    function addPrescription() {
        const container = document.getElementById('prescriptions-container');
        const html = `
            <div class="prescription-row mb-2 p-2 border rounded bg-light">
                <input type="text" name="prescriptions[${presIdx}][medicament]" class="form-control form-control-sm mb-1" placeholder="Médicament">
                <div class="input-group input-group-sm">
                    <input type="text" name="prescriptions[${presIdx}][posologie]" class="form-control" placeholder="Posologie">
                    <input type="text" name="prescriptions[${presIdx}][duree]" class="form-control" placeholder="Durée">
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
        presIdx++;
    }
</script>
@endsection

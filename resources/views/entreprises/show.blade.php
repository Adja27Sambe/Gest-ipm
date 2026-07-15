@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="h3 fw-bold text-dark mb-0">Détails de l'entreprise</h2>
            <div>
                <a href="{{ route('entreprises.edit', $entreprise->id_entreprise) }}" class="btn btn-warning text-dark me-2">
                    Éditer
                </a>
                <a href="{{ route('entreprises.index') }}" class="btn btn-light text-muted">
                    Retour
                </a>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">{{ $entreprise->raison_sociale }}</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Code adhérent</p>
                        <p class="fs-5">{{ $entreprise->code_adherent ?? 'Non renseigné' }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Statut</p>
                        <p class="fs-5">
                            @if($entreprise->statut == 'actif')
                                <span class="badge bg-success">Actif</span>
                            @elseif($entreprise->statut == 'suspendu')
                                <span class="badge bg-warning text-dark">Suspendu</span>
                            @elseif($entreprise->statut == 'résilié')
                                <span class="badge bg-danger">Résilié</span>
                            @else
                                <span class="badge bg-secondary">{{ $entreprise->statut ?? 'Non défini' }}</span>
                            @endif
                        </p>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Contact</p>
                        <p class="mb-1"><strong>Email :</strong> {{ $entreprise->email ?? '-' }}</p>
                        <p class="mb-0"><strong>Téléphone :</strong> {{ $entreprise->telephone ?? '-' }}</p>
                    </div>
                    
                    <div class="col-md-6 mb-4">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Statistiques</p>
                        <p class="mb-0"><strong>Salariés rattachés :</strong> <span class="badge bg-primary rounded-pill">{{ $entreprise->salaries_count }}</span></p>
                    </div>

                    <div class="col-12 mb-4">
                        <p class="text-muted mb-1 text-uppercase small fw-bold">Adresse</p>
                        <p class="mb-0">{{ $entreprise->adresse ?? 'Non renseignée' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

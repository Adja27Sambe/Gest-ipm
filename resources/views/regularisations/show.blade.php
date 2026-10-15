@extends('layouts.app')

@section('title', 'Détail de la Régularisation #' . $demande->id_demande)

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- En-tête -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Tableau de bord</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('regularisations.index') }}" class="text-decoration-none">Régularisations</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Dossier #{{ $demande->id_demande }}</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-shield-check text-success"></i> Régularisation #{{ $demande->id_demande }}
                @if($demande->numero_demande)
                    <span class="badge bg-light text-dark border fw-normal fs-6">({{ $demande->numero_demande }})</span>
                @endif
            </h1>
            <p class="text-muted small mb-0">Traçabilité complète et détails de la prise en charge régularisée.</p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('regularisations.index') }}" class="btn btn-outline-secondary rounded-3">
                <i class="bi bi-arrow-left me-1"></i> Retour à la liste
            </a>
            @if($demande->prestation)
                <a href="{{ route('prestations.edit', $demande->prestation) }}" class="btn btn-primary rounded-3">
                    <i class="bi bi-receipt me-1"></i> Voir la Prestation
                </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations du Dossier -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-file-earmark-text text-primary me-2"></i>Détails du Dossier de Régularisation
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Participant (Assuré principal) :</span>
                            <span class="fw-bold text-dark fs-6">{{ $demande->salarie->nom ?? '' }} {{ $demande->salarie->prenom ?? '' }}</span>
                            <div class="small text-muted">Matricule: <code>{{ $demande->salarie->matricule ?? 'N/A' }}</code></div>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Bénéficiaire des soins :</span>
                            @if($demande->ayantDroit)
                                <span class="fw-bold text-dark fs-6">{{ $demande->ayantDroit->nom }} {{ $demande->ayantDroit->prenom }}</span>
                                <div class="small text-muted">(Ayant droit: {{ $demande->ayantDroit->lien_parente ?? 'N/A' }})</div>
                            @else
                                <span class="fw-bold text-dark fs-6">Lui-même (Participant)</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Type de prise en charge :</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold">
                                {{ $demande->typeDemande->libelle ?? 'Prise en charge' }}
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Régime de couverture :</span>
                            @if(!empty($demande->sous_couvert))
                                <span class="badge bg-info bg-opacity-15 text-primary border border-info border-opacity-50 px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-shield-fill-check me-1"></i>SOUS COUVERT (100% IPM)
                                </span>
                            @else
                                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fw-semibold">Standard</span>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Prestataire conventionné :</span>
                            <span class="fw-bold text-dark">
                                @if($demande->praticien)
                                    🩺 {{ $demande->praticien->nom ?? $demande->praticien->NOMPRAT }}
                                @elseif($demande->pharmacie)
                                    💊 {{ $demande->pharmacie->nom ?? $demande->pharmacie->NOMPHARM }}
                                @else
                                    Non spécifié
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted small d-block">Date de l'acte / demande :</span>
                            <span class="fw-bold text-dark">{{ $demande->date_demande ? $demande->date_demande->format('d/m/Y') : '-' }}</span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-3">
                        <label class="fw-bold text-dark small text-uppercase d-block mb-1">
                            <i class="bi bi-exclamation-circle text-warning me-1"></i>Situation Initiale :
                        </label>
                        <div class="p-3 bg-light rounded-3 border text-dark">
                            {{ $demande->situation_initiale ?? 'Aucune précision sur la situation initiale enregistrée.' }}
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="fw-bold text-dark small text-uppercase d-block mb-1">
                            <i class="bi bi-chat-left-text text-primary me-1"></i>Motif de la Régularisation :
                        </label>
                        <div class="p-3 bg-light rounded-3 border text-dark fw-medium">
                            {{ $demande->motif_regularisation ?? $demande->motif ?? 'Non spécifié.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traçabilité & Historique des Mouvements -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-clock-history text-info"></i> Journal d'Audit & Traçabilité
                    </h5>
                    <p class="text-muted small mb-0 mt-1">Horodatage et opérations tracées.</p>
                </div>
                <div class="card-body p-4">
                    <div class="timeline">
                        <div class="p-3 bg-light rounded-3 border mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold">Création Régularisation</span>
                                <small class="text-muted">{{ $demande->created_at ? $demande->created_at->format('d/m/Y H:i') : '-' }}</small>
                            </div>
                            <small class="text-dark d-block">
                                Enregistré par : <strong>{{ $demande->utilisateur->nom_complet ?? $demande->utilisateur->name ?? 'Opérateur IPM' }}</strong>
                            </small>
                            <small class="text-muted d-block mt-1">Statut initial : <em>{{ $demande->statut }}</em></small>
                        </div>

                        @forelse($audits as $audit)
                            <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">{{ $audit->action ?? $audit->module }}</span>
                                    <small class="text-muted">{{ $audit->date_heure ? \Carbon\Carbon::parse($audit->date_heure)->format('d/m/Y H:i') : '' }}</small>
                                </div>
                                <div class="small text-dark">{{ $audit->description }}</div>
                                @if($audit->utilisateur)
                                    <div class="small text-muted mt-1"><i class="bi bi-person me-1"></i>{{ $audit->utilisateur->name ?? $audit->utilisateur->nom_complet ?? 'Opérateur' }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="text-muted small text-center py-3">
                                <i class="bi bi-check2-circle text-success fs-4 d-block mb-1"></i>
                                Dossier conforme et audité.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

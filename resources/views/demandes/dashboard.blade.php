@extends('layouts.app')

@section('title', 'Dashboard des Demandes')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">
                <i class="fas fa-chart-line text-primary me-2"></i>Dashboard Demandes
            </h2>
            <p class="text-muted mb-0">Vue d'ensemble et statistiques des demandes</p>
        </div>
        <a href="{{ route('demandes.create') }}" class="btn btn-primary shadow-sm rounded-pill px-4">
            <i class="fas fa-plus me-2"></i>Nouvelle Demande
        </a>
    </div>

    <!-- KPIs -->
    <div class="row g-4 mb-4">
        <!-- Total -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-4" style="border-left: 5px solid #4e73df !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Demandes</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approuvées -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-4" style="border-left: 5px solid #1cc88a !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approuvées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approuvees'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- En cours -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-4" style="border-left: 5px solid #f6c23e !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">En Cours</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['en_cours'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejetées -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100 py-2 rounded-4" style="border-left: 5px solid #e74a3b !important;">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejetées</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['rejetes'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Demandes récentes -->
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-3">
            <h6 class="m-0 font-weight-bold text-primary">5 Dernières Demandes</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>N° Demande</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Participant</th>
                            <th>Partenaire</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentes as $demande)
                        <tr>
                            <td><strong>{{ $demande->numero_demande }}</strong></td>
                            <td>{{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge bg-secondary rounded-pill px-3">{{ $demande->typeDemande->libelle ?? '-' }}</span>
                            </td>
                            <td>
                                {{ $demande->salarie->nom }} {{ $demande->salarie->prenom }}
                            </td>
                            <td>
                                @if($demande->praticien)
                                    <i class="fas fa-user-md text-info me-1"></i> {{ $demande->praticien->nom }}
                                @elseif($demande->pharmacie)
                                    <i class="fas fa-clinic-medical text-success me-1"></i> {{ $demande->pharmacie->nom }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($demande->statut) {
                                        'Approuvée' => 'success',
                                        'Rejetée' => 'danger',
                                        'En cours', 'en_attente' => 'warning',
                                        default => 'secondary'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }} rounded-pill px-3">{{ ucfirst($demande->statut) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('demandes.show', $demande->id_demande) }}" class="btn btn-sm btn-outline-primary rounded-circle" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-3"></i><br>
                                Aucune demande récente trouvée.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('demandes.index') }}" class="btn btn-link text-decoration-none">
                    Voir toutes les demandes <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    .card { transition: transform 0.2s ease-in-out; }
    .card:hover { transform: translateY(-5px); }
</style>
@endsection

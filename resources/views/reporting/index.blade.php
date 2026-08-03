@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="fw-bold text-dark mb-0">Tableau de Bord & Reporting</h2>
            <p class="text-muted mb-0">Vue d'ensemble des statistiques de l'IPM</p>
        </div>
        <div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill shadow-sm">
                <i class="bi bi-calendar3 me-2"></i>{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </span>
        </div>
    </div>

    <!-- KPIs Top Section -->
    <div class="row g-4 mb-5">
        <!-- Adhérents -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Adhérents</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalEntreprises, 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bénéficiaires -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Bénéficiaires</h6>
                        <h3 class="mb-0 fw-bold text-dark">{{ number_format($totalBeneficiaires, 0, ',', ' ') }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Facturé -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="bi bi-wallet2 fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Total Facturé</h6>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($totalFacture, 0, ',', ' ') }} <small class="text-muted fs-6">FCFA</small></h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Dû -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden" style="transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex justify-content-center align-items-center me-3 flex-shrink-0" style="width: 50px; height: 50px;">
                        <i class="bi bi-exclamation-circle fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1 text-uppercase fw-semibold" style="font-size: 0.75rem;">Reste à Payer</h6>
                        <h4 class="mb-0 fw-bold text-dark">{{ number_format($totalDu, 0, ',', ' ') }} <small class="text-muted fs-6">FCFA</small></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-5">
        <!-- Évolution Dépenses -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-graph-up text-primary me-2"></i>Évolution des Facturations (6 Derniers Mois)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <canvas id="evolutionChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Répartition Statuts -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-pie-chart text-success me-2"></i>Statut des Demandes
                    </h6>
                </div>
                <div class="card-body p-4 d-flex justify-content-center align-items-center">
                    <canvas id="statutChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Dernières Factures & Partenaires -->
    <div class="row g-4">
        <!-- Partenaires -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden position-relative h-100">
                <div class="position-absolute top-0 end-0 opacity-25 p-4" style="transform: translate(20%, -20%);">
                    <i class="bi bi-hospital" style="font-size: 8rem;"></i>
                </div>
                <div class="card-body p-4 position-relative z-1 d-flex flex-column justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-4">Réseau de Santé</h5>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-5 opacity-75">Praticiens</span>
                            <h2 class="fw-bold mb-0">{{ $totalPraticiens }}</h2>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fs-5 opacity-75">Pharmacies</span>
                            <h2 class="fw-bold mb-0">{{ $totalPharmacies }}</h2>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top border-white border-opacity-25">
                        <a href="{{ route('praticiens.index') }}" class="btn btn-light btn-sm rounded-pill fw-medium px-3">Gérer le réseau</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dernières factures -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 d-flex align-items-center">
                        <i class="bi bi-receipt text-info me-2"></i>Dernières Factures Émises
                    </h6>
                    <a href="{{ route('factures.index') }}" class="btn btn-sm btn-link text-decoration-none p-0">Tout voir <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 px-4 small text-uppercase text-secondary fw-semibold">N° Facture</th>
                                    <th class="py-3 small text-uppercase text-secondary fw-semibold">Partenaire</th>
                                    <th class="py-3 small text-uppercase text-secondary fw-semibold text-end">Montant</th>
                                    <th class="pe-4 py-3 small text-uppercase text-secondary fw-semibold text-center">Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dernieresFactures as $facture)
                                    <tr class="clickable-row" data-href="{{ route('factures.show', $facture->id_facture) }}">
                                        <td class="px-4 py-3 fw-bold text-dark">{{ $facture->numero_facture }}</td>
                                        <td class="py-3">
                                            <span class="d-flex align-items-center">
                                                <i class="bi {{ $facture->praticien ? 'bi-person-badge text-primary' : 'bi-capsule text-success' }} me-2"></i>
                                                {{ $facture->partenaire->nom ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="py-3 text-end font-monospace fw-bold">{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</td>
                                        <td class="pe-4 py-3 text-center">
                                            @if($facture->statut_paiement == 'en_attente')
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-1">En attente</span>
                                            @elseif($facture->statut_paiement == 'partiellement_payee')
                                                <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill px-3 py-1">Partiel</span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-1">Soldée</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Aucune facture récente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Inclusion de Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Chart: Évolution des Dépenses (Bar Chart)
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    
    // Gradient pour les barres
    let gradient = evolutionCtx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.8)'); // Primary color
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0.2)');

    new Chart(evolutionCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($labelsEvolution) !!},
            datasets: [{
                label: 'Montant Facturé (FCFA)',
                data: {!! json_encode($dataEvolution) !!},
                backgroundColor: gradient,
                borderColor: '#0d6efd',
                borderWidth: 1,
                borderRadius: 6,
                hoverBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [5, 5], color: '#e9ecef' },
                    ticks: {
                        callback: function(value) {
                            return value >= 1000000 ? (value / 1000000) + 'M' : value;
                        }
                    }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // 2. Chart: Statut des Demandes (Doughnut Chart)
    const statutCtx = document.getElementById('statutChart').getContext('2d');
    new Chart(statutCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($labelsStatut) !!},
            datasets: [{
                data: {!! json_encode($dataStatut) !!},
                backgroundColor: {!! json_encode($colorsStatut) !!},
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { usePointStyle: true, padding: 20 }
                }
            }
        }
    });
});
</script>
@endsection

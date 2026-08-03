@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Audit & Historique</h2>
        <p class="text-muted mb-0">Traçabilité complète des actions effectuées sur la plateforme</p>
    </div>
    <a href="{{ route('audit.export', request()->all()) }}" class="btn btn-success rounded-pill px-4 shadow-sm">
        <i class="bi bi-file-earmark-excel me-2"></i> Exporter en CSV
    </a>
</div>

<!-- Filtres de recherche -->
<div class="card border-0 shadow-sm rounded-4 mb-5 bg-white">
    <div class="card-body p-4">
        <form action="{{ route('audit.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold text-uppercase">Recherche dynamique</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-0 ps-0 dynamic-search-input" placeholder="Description, utilisateur, IP..." value="{{ request('search') }}" autocomplete="off">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Utilisateur</label>
                <select name="id_utilisateur" class="form-select bg-light border-0 shadow-none">
                    <option value="">Tous</option>
                    @foreach($utilisateurs as $user)
                        <option value="{{ $user->id_utilisateur }}" {{ request('id_utilisateur') == $user->id_utilisateur ? 'selected' : '' }}>
                            {{ $user->login }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Module</label>
                <select name="module" class="form-select bg-light border-0 shadow-none">
                    <option value="">Tous</option>
                    @foreach($modules_disponibles as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Action</label>
                <select name="action" class="form-select bg-light border-0 shadow-none">
                    <option value="">Toutes</option>
                    @foreach($actions_disponibles as $act)
                        <option value="{{ $act }}" {{ request('action') == $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Du</label>
                <input type="date" name="date_debut" class="form-control bg-light border-0 shadow-none" value="{{ request('date_debut') }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold text-uppercase">Au</label>
                <input type="date" name="date_fin" class="form-control bg-light border-0 shadow-none" value="{{ request('date_fin') }}">
            </div>

            <div class="col-md-1">
                <label class="form-label text-muted small fw-bold text-uppercase">Afficher</label>
                <select name="per_page" id="per_page" class="form-select bg-light border-0 shadow-none" onchange="this.form.submit()">
                    <option value="5" {{ request('per_page', 5) == 5 ? 'selected' : '' }}>5</option>
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                </select>
            </div>
        </form>
    </div>
</div>

<!-- Liste de l'historique -->
<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Date & Heure</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Utilisateur / IP</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Action / Module</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Description</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-end">Détails techniques</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($historiques as $log)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="text-dark fw-medium">{{ \Carbon\Carbon::parse($log->date_heure)->format('d/m/Y') }}</div>
                            <small class="text-muted"><i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($log->date_heure)->format('H:i:s') }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 36px; height: 36px;">
                                    <i class="bi bi-person"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $log->utilisateur ? $log->utilisateur->login : 'Système' }}</h6>
                                    <small class="text-muted font-monospace">{{ $log->adresse_ip }}</small>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                @if($log->action == 'created')
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1 rounded-2 mb-1">
                                        <i class="bi bi-plus-circle me-1"></i> Création
                                    </span>
                                @elseif($log->action == 'updated')
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2 py-1 rounded-2 mb-1">
                                        <i class="bi bi-pencil me-1"></i> Modification
                                    </span>
                                @elseif($log->action == 'deleted')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-2 mb-1">
                                        <i class="bi bi-trash me-1"></i> Suppression
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 rounded-2 mb-1">
                                        {{ $log->action }}
                                    </span>
                                @endif
                            </div>
                            <small class="text-muted fw-medium"><i class="bi bi-box me-1"></i>{{ $log->module }}</small>
                        </td>
                        <td class="px-4 py-3">
                            <p class="mb-0 text-dark">{{ $log->description }}</p>
                        </td>
                        <td class="px-4 py-3 text-end">
                            <button type="button" class="btn btn-sm btn-light rounded-pill px-3 border shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDetails{{ $log->id_historique }}">
                                <i class="bi bi-eye"></i> Voir Diff
                            </button>
                            
                            <!-- Modal -->
                            <div class="modal fade" id="modalDetails{{ $log->id_historique }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-bottom-0 pb-0">
                                            <h5 class="modal-title fw-bold">Détails du mouvement #{{ $log->id_historique }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start">
                                            <div class="row g-4 mt-1">
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-danger mb-3"><i class="bi bi-dash-circle me-2"></i>Ancienne Valeur</h6>
                                                    <div class="bg-light p-3 rounded-3 font-monospace small" style="max-height: 300px; overflow-y: auto;">
                                                        <pre class="mb-0 text-danger"><code>@if($log->ancienne_valeur){{ json_encode(json_decode($log->ancienne_valeur), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}@else Aucun @endif</code></pre>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-circle me-2"></i>Nouvelle Valeur</h6>
                                                    <div class="bg-light p-3 rounded-3 font-monospace small" style="max-height: 300px; overflow-y: auto;">
                                                        <pre class="mb-0 text-success"><code>@if($log->nouvelle_valeur){{ json_encode(json_decode($log->nouvelle_valeur), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}@else Aucun @endif</code></pre>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 pt-0">
                                            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Fermer</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <div class="text-muted">
                                <i class="bi bi-shield-check fs-1 d-block mb-3 opacity-50"></i>
                                <h5>Aucun historique trouvé</h5>
                                <p class="mb-0">Les actions des utilisateurs apparaîtront ici.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted small">
        Affichage de {{ $historiques->firstItem() ?? 0 }} à {{ $historiques->lastItem() ?? 0 }} sur {{ $historiques->total() }} entrées d'audit
    </div>
    <div>
        {{ $historiques->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

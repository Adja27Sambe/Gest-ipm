@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-5">
    <div>
        <h2 class="fw-bold mb-1 text-dark" style="letter-spacing: -0.5px;">Paramètres de Couverture</h2>
        <p class="text-muted mb-0">Gestion des taux et plafonds de prise en charge par type de prestation</p>
    </div>
    <a href="{{ route('parametres-couverture.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
        <i class="bi bi-plus-lg me-2"></i> Nouveau Paramètre
    </a>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase">Type de Prestation</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-center">Taux Prise en Charge</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-center">Plafond par Acte</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-center">Plafond Annuel</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-center">Ticket Modérateur</th>
                    <th class="py-3 px-4 text-muted small fw-bold text-uppercase text-end">Actions</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($parametres as $param)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center fw-bold me-3" style="width: 40px; height: 40px;">
                                    <i class="bi bi-file-medical"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $param->typePrestation->libelle ?? 'Type inconnu' }}</h6>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fs-6 border border-success border-opacity-25">
                                {{ $param->taux_prise_charge }} %
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($param->plafond_par_acte)
                                <div class="fw-medium text-dark">{{ number_format($param->plafond_par_acte, 0, ',', ' ') }} <small class="text-muted">FCFA</small></div>
                            @else
                                <span class="text-muted fst-italic">Illimité</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($param->plafond_annuel)
                                <div class="fw-medium text-dark">{{ number_format($param->plafond_annuel, 0, ',', ' ') }} <small class="text-muted">FCFA</small></div>
                            @else
                                <span class="text-muted fst-italic">Illimité</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($param->ticket_moderateur)
                                <div class="fw-medium text-warning">{{ $param->ticket_moderateur }} %</div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('parametres-couverture.edit', $param->id_parametre) }}" class="btn btn-sm btn-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Modifier">
                                    <i class="bi bi-pencil text-primary"></i>
                                </a>
                                <form action="{{ route('parametres-couverture.destroy', $param->id_parametre) }}" method="POST" class="d-inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce paramètre ? Cela bloquera les futures saisies pour ce type de prestation.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Supprimer">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <div class="bg-secondary bg-opacity-10 text-secondary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                                    <i class="bi bi-gear fs-2"></i>
                                </div>
                                <h5 class="fw-bold text-dark">Aucun paramètre configuré</h5>
                                <p class="mb-0">Veuillez créer des paramètres pour autoriser la saisie de prestations.</p>
                                <a href="{{ route('parametres-couverture.create') }}" class="btn btn-primary rounded-pill mt-3 px-4">Créer le premier paramètre</a>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="d-flex justify-content-center mt-4">
    {{ $parametres->links() }}
</div>
@endsection

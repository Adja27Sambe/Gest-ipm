@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-4">
        <a href="{{ route('factures.index') }}" class="text-decoration-none text-muted mb-2 d-inline-block">
            <i class="bi bi-arrow-left me-1"></i> Retour aux factures
        </a>
        <h2 class="fw-bold text-dark mb-0">Générer une Facture</h2>
        <p class="text-muted mb-0">Regrouper les prestations non facturées d'un partenaire</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm rounded-4 border-0 mb-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-building text-primary me-2"></i> Sélection du partenaire</h5>
                    <form method="GET" action="{{ route('factures.create') }}" id="form-partenaire">
                        <div class="form-group mb-0">
                            <label class="form-label text-muted small fw-semibold">Partenaire médical</label>
                            <select name="partenaire" class="form-select form-select-lg rounded-3" onchange="document.getElementById('form-partenaire').submit();">
                                <option value="">Choisir un partenaire...</option>
                                @foreach($partenaires as $part)
                                    <option value="{{ $part->value }}" {{ request('partenaire') == $part->value ? 'selected' : '' }}>
                                        {{ $part->nom }} ({{ ucfirst($part->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            @if(request('partenaire'))
                @if($prestationsNonFacturees->isEmpty())
                    <div class="card border-0 shadow-sm rounded-4 mb-4 text-center py-5">
                        <div class="card-body">
                            <i class="bi bi-inbox text-muted opacity-50 display-1 mb-3"></i>
                            <h5 class="fw-bold text-dark">Aucune prestation en attente</h5>
                            <p class="text-muted">Ce partenaire n'a aucune prestation non facturée.</p>
                        </div>
                    </div>
                @else
                    <form method="POST" action="{{ route('factures.store') }}">
                        @csrf
                        <input type="hidden" name="partenaire" value="{{ request('partenaire') }}">
                        
                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4"><i class="bi bi-file-earmark-text text-primary me-2"></i> Détails de la facture</h5>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-semibold">Numéro de facture (Partenaire)</label>
                                        <input type="text" name="numero_facture" class="form-control form-control-lg rounded-3" value="{{ old('numero_facture') }}" required placeholder="Ex: FACT-2026-001">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-muted small fw-semibold">Date de la facture</label>
                                        <input type="date" name="date_facture" class="form-control form-control-lg rounded-3" value="{{ old('date_facture', date('Y-m-d')) }}" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 mb-4">
                            <div class="card-body p-0">
                                <div class="p-4 border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="fw-bold mb-0"><i class="bi bi-list-check text-primary me-2"></i> Sélection des prestations</h5>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="checkAll">
                                            <label class="form-check-label fw-semibold" for="checkAll">
                                                Tout sélectionner
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-4" style="width: 50px;"></th>
                                                <th>Date</th>
                                                <th>Bénéficiaire</th>
                                                <th>Type</th>
                                                <th class="text-end pe-4">Part IPM (FCFA)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($prestationsNonFacturees as $prestation)
                                                @php
                                                    $partIpm = $prestation->montant - $prestation->reste_a_charge;
                                                    $beneficiaire = $prestation->demande->ayantDroit 
                                                        ? $prestation->demande->ayantDroit->nom . ' ' . $prestation->demande->ayantDroit->prenom . ' (Ayant Droit)'
                                                        : ($prestation->demande->salarie 
                                                            ? $prestation->demande->salarie->nom . ' ' . $prestation->demande->salarie->prenom . ' (Salarié)'
                                                            : 'N/A');
                                                @endphp
                                                <tr class="clickable-row">
                                                    <td class="ps-4">
                                                        <input class="form-check-input prestation-checkbox" type="checkbox" name="prestations[]" value="{{ $prestation->id_prestation }}" data-montant="{{ $partIpm }}" {{ in_array($prestation->id_prestation, old('prestations', [])) ? 'checked' : '' }}>
                                                    </td>
                                                    <td>{{ $prestation->date_prestation ? $prestation->date_prestation->format('d/m/Y') : 'N/A' }}</td>
                                                    <td>
                                                        <span class="d-block fw-semibold text-dark">{{ $beneficiaire }}</span>
                                                    </td>
                                                    <td><span class="badge bg-light text-secondary border">{{ $prestation->typePrestation->libelle ?? 'N/A' }}</span></td>
                                                    <td class="text-end pe-4 fw-semibold text-primary">
                                                        {{ number_format($partIpm, 0, ',', ' ') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-light">
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold pt-3 pb-3">Montant Total de la Facture :</td>
                                                <td class="text-end pe-4 fw-bold fs-5 text-primary pt-3 pb-3"><span id="total-amount">0</span> FCFA</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mb-5">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                                <i class="bi bi-check2-circle me-2"></i> Générer la Facture
                            </button>
                        </div>
                    </form>
                @endif
            @else
                <div class="card border-0 shadow-sm rounded-4 mb-4 text-center py-5">
                    <div class="card-body">
                        <i class="bi bi-building text-muted opacity-25 display-1 mb-3"></i>
                        <h5 class="fw-bold text-dark">Veuillez sélectionner un partenaire</h5>
                        <p class="text-muted">Sélectionnez un partenaire dans la liste pour voir ses prestations en attente.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('checkAll');
        const checkboxes = document.querySelectorAll('.prestation-checkbox');
        const totalAmountEl = document.getElementById('total-amount');

        function updateTotal() {
            let total = 0;
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    total += parseFloat(cb.dataset.montant || 0);
                }
            });
            totalAmountEl.textContent = new Intl.NumberFormat('fr-FR').format(total);
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                });
                updateTotal();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                if (!this.checked && checkAll) {
                    checkAll.checked = false;
                }
                updateTotal();
            });
        });

        // Initialize on load
        updateTotal();
    });
</script>
@endpush
@endsection

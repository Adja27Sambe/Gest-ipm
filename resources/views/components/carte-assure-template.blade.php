@props(['carte', 'salarie' => null, 'onlyRecto' => true])

@php
    $salarie        = $salarie ?? $carte->salarie;
    $entrepriseNom  = mb_strtoupper($salarie->entreprise->raison_sociale ?? $salarie->entreprise->nom_entreprise ?? 'SOCIETE');
    $matricule      = $salarie->matricule ?? $carte->matricule ?? 'XXXXX';
    $numeroCarte    = $carte->numero_carte ?? '—';
    $dateEmission   = $carte->date_emission ? \Carbon\Carbon::parse($carte->date_emission)->translatedFormat('d/m/Y') : '—';
    $nom            = mb_strtoupper($salarie->nom ?? 'XXXX');
    $prenom         = $salarie->prenom ?? 'XXXXXX';
    $telephone      = $salarie->telephone ?? '';
    $dateNaissance  = $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->translatedFormat('d/m/Y') : '—';
    $statut         = $carte->statut ?? 'actif';
    $rid            = $carte->id_carte; // ID unique pour DOM
@endphp

<style>
/* ─── Styles d'impression sans marge et aux dimensions exactes CR80 ─── */
@media print {
    @page {
        size: 85.6mm 53.98mm;
        margin: 0 !important;
        padding: 0 !important;
    }
    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 85.6mm !important;
        height: 53.98mm !important;
        background: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    body * {
        visibility: hidden !important;
    }
    .carte-assure-wrapper-{{ $rid }},
    .carte-assure-wrapper-{{ $rid }} * {
        visibility: visible !important;
    }
    .carte-assure-wrapper-{{ $rid }} {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 85.6mm !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .carte-actions-bar,
    .carte-card-label {
        display: none !important;
    }
    .carte-box-print {
        width: 85.6mm !important;
        height: 53.98mm !important;
        min-width: 85.6mm !important;
        min-height: 53.98mm !important;
        max-width: 85.6mm !important;
        max-height: 53.98mm !important;
        margin: 0 !important;
        padding: 0 !important;
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        page-break-inside: avoid !important;
        page-break-after: always !important;
        overflow: hidden !important;
        box-sizing: border-box !important;
    }
    .carte-box-print:last-child {
        page-break-after: avoid !important;
    }
    #verso-wrapper-{{ $rid }} {
        display: block !important;
    }
}

/* ─── Hover et transitions en mode écran ─── */
.carte-container-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.carte-container-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(4, 86, 137, 0.18) !important;
}
.carte-qr-box svg {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
    display: block !important;
}
</style>

<div class="carte-assure-wrapper carte-assure-wrapper-{{ $rid }} d-flex flex-column align-items-center gap-4 py-2">

    {{-- ── Barre d'actions ── --}}
    <div class="carte-actions-bar d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-success rounded-pill px-3 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2"
                onclick="downloadBothCartesPNG{{ $rid }}()">
            <i class="bi bi-images"></i>
            <span>Télécharger PNG (Recto - Verso)</span>
        </button>
        <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2"
                onclick="downloadCartePNG('recto-{{ $rid }}', 'carte_recto_{{ $matricule }}')">
            <i class="bi bi-download"></i>
            <span>PNG Recto</span>
        </button>
        <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2"
                onclick="downloadCartePNG('verso-{{ $rid }}', 'carte_verso_{{ $matricule }}')">
            <i class="bi bi-download"></i>
            <span>PNG Verso</span>
        </button>
        <a href="{{ route('cartes-assurees.download', $carte->id_carte) }}"
           class="btn btn-outline-danger rounded-pill px-3 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i>
            <span>Télécharger PDF (2 pages)</span>
        </a>
        <button type="button" class="btn btn-outline-primary rounded-pill px-3 py-2 shadow-sm fw-semibold d-inline-flex align-items-center gap-2"
                onclick="window.print()">
            <i class="bi bi-printer"></i>
            <span>Imprimer</span>
        </button>
    </div>

    <div class="d-flex flex-column align-items-center gap-4 w-100">

        {{-- ══════════════════════════════════════════════════
             RECTO (Dimensions standard CR80 : 85.6 × 53.98 mm)
             Rapport exact 1.5858 → 536 × 338 px
        ══════════════════════════════════════════════════ --}}
        <div class="carte-card-wrapper w-100 d-flex flex-column align-items-center">
            <div class="carte-card-label d-flex justify-content-between align-items-center mb-2 px-1" style="width: 536px; max-width: 100%;">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">
                    <i class="bi bi-credit-card-2-front me-1"></i> RECTO — CARTE PARTICIPANT
                </span>
                <span class="badge bg-light text-secondary border">Format standard CR80 (85.6 × 54 mm)</span>
            </div>

            <div id="recto-{{ $rid }}"
                 class="carte-container-card carte-box-print"
                 style="
                    width: 536px;
                    height: 338px;
                    border-radius: 12px;
                    font-family: 'Poppins', 'Segoe UI', system-ui, -apple-system, sans-serif;
                    box-sizing: border-box;
                    overflow: hidden;
                    position: relative;
                    background-color: #ffffff;
                    background-image: url('{{ asset('images/template_carte_recto_bg.png') }}');
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                    background-position: center;
                    border: 1px solid #d0d7de;
                    box-shadow: 0 8px 24px rgba(4, 86, 137, 0.14);
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                ">

                {{-- Contenu superposé --}}
                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; box-sizing: border-box; padding: 10px 14px; display: flex; flex-direction: column; justify-content: space-between;">
                    
                    {{-- ── En-tête de la carte ── --}}
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-left: 95px; padding-bottom: 6px; border-bottom: 1px solid rgba(4, 86, 137, 0.20);">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <img src="{{ asset('images/logo_clean.png') }}" alt="Logo IPM"
                                 style="height: 38px; width: auto; max-width: 130px; object-fit: contain;">
                            <div style="line-height: 1.2;">
                                <div style="font-size: 14px; font-weight: 800; color: #045689; text-transform: uppercase; letter-spacing: 0.3px;">
                                    Institut de Prévoyance Maladie
                                </div>
                                <div style="font-size: 11px; font-weight: 700; color: #045689; text-transform: uppercase; letter-spacing: 0.2px;">
                                    INTER-ENTREPRISES
                                </div>
                                <div style="font-size: 12px; font-weight: 700; color: #1ea3e4; letter-spacing: 0.2px;">
                                    Carte de Participant Assuré
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Corps : Photo + QR Code à gauche, Infos à droite ── --}}
                    <div style="display: flex; gap: 14px; align-items: flex-start; flex: 1; margin-top: 8px;">

                        {{-- Colonne gauche : Photo & QR Code agrandis et optimisés --}}
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px; flex-shrink: 0; width: 106px;">
                            {{-- Cadre Photo --}}
                            <div style="
                                width: 102px;
                                height: 110px;
                                border-radius: 8px;
                                border: 2px solid #045689;
                                overflow: hidden;
                                background-color: #f0f4f8;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                box-shadow: 0 2px 8px rgba(4, 86, 137, 0.18);
                            ">
                                @if($salarie && $salarie->photo_url)
                                    <img src="{{ $salarie->photo_url }}" alt="Photo {{ $prenom }} {{ $nom }}"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                @else
                                    <div style="display: flex; flex-direction: column; align-items: center; color: #8898aa;">
                                        <svg width="46" height="46" viewBox="0 0 24 24" fill="#90a4ae">
                                            <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                        </svg>
                                        <span style="font-size: 10px; font-weight: 700; text-transform: uppercase;">Photo</span>
                                    </div>
                                @endif
                            </div>

                            {{-- QR Code --}}
                            <div class="carte-qr-box" style="
                                width: 102px;
                                height: 102px;
                                background: #ffffff;
                                padding: 4px;
                                border-radius: 8px;
                                border: 1.2px solid #d0d7de;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                box-shadow: 0 2px 6px rgba(0,0,0,0.08);
                                box-sizing: border-box;
                            ">
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                    {!! $carte->qr_code !!}
                                </div>
                            </div>
                        </div>

                        {{-- Colonne droite : Informations participant --}}
                        <div style="flex: 1; display: flex; flex-direction: column; justify-content: space-between; height: 100%; min-width: 0;">

                            <div>
                                {{-- Badge Matricule --}}
                                <div style="
                                    display: inline-flex;
                                    align-items: center;
                                    gap: 6px;
                                    background-color: #076B27;
                                    color: #ffffff;
                                    font-size: 14px;
                                    font-weight: 800;
                                    padding: 4px 14px;
                                    border-radius: 6px;
                                    letter-spacing: 0.5px;
                                    box-shadow: 0 2px 6px rgba(7, 107, 39, 0.28);
                                    margin-bottom: 6px;
                                ">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="white" style="flex-shrink: 0;">
                                        <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                                    </svg>
                                    <span>MATRICULE : {{ $matricule }}</span>
                                </div>

                                {{-- Tableau / Lignes d'informations calibrées à 14px --}}
                                <table style="border-collapse: collapse; width: 100%; font-size: 14px; color: #1a2333;">
                                    <tr>
                                        <td style="padding: 2.5px 0; color: #5a6880; font-weight: 500; width: 80px; font-size: 14px;">Nom :</td>
                                        <td style="padding: 2.5px 0; font-weight: 800; font-size: 16px; color: #045689; text-transform: uppercase;">
                                            {{ $nom }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2.5px 0; color: #5a6880; font-weight: 500; font-size: 14px;">Prénom :</td>
                                        <td style="padding: 2.5px 0; font-weight: 700; font-size: 14px; color: #1a2333;">
                                            {{ $prenom }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2.5px 0; color: #5a6880; font-weight: 500; font-size: 14px;">Né(e) le :</td>
                                        <td style="padding: 2.5px 0; font-weight: 600; font-size: 14px; color: #1a2333;">
                                            {{ $dateNaissance }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 2.5px 0; color: #5a6880; font-weight: 500; font-size: 14px;">Société :</td>
                                        <td style="padding: 2.5px 0; font-weight: 700; color: #045689; font-size: 14px;">
                                            {{ $entrepriseNom }}
                                        </td>
                                    </tr>
                                    @if($telephone)
                                    <tr>
                                        <td style="padding: 2.5px 0; color: #5a6880; font-weight: 500; font-size: 14px;">Téléphone :</td>
                                        <td style="padding: 2.5px 0; font-weight: 600; font-size: 14px; color: #333333;">
                                            {{ $telephone }}
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>

                            {{-- Pied de carte intérieur --}}
                            <div style="
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                padding-top: 5px;
                                margin-top: 3px;
                                border-top: 1px dashed #c8d6e8;
                                font-size: 11.5px;
                                color: #64748b;
                            ">
                                <span>N° Carte : <strong style="color: #045689; font-weight: 800;">{{ $numeroCarte }}</strong></span>
                                <span>Délivrée le : <strong style="color: #1a2333;">{{ $dateEmission }}</strong></span>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             VERSO (Template Officiel Mbaarum Koolute)
             Format exact 85.6 × 53.98 mm
        ══════════════════════════════════════════════════ --}}
        <div class="carte-card-wrapper w-100 d-flex flex-column align-items-center {{ $onlyRecto ? 'd-none' : '' }}" id="verso-wrapper-{{ $rid }}">
            <div class="carte-card-label d-flex justify-content-between align-items-center mb-2 px-1" style="width: 536px; max-width: 100%;">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">
                    <i class="bi bi-credit-card-2-back me-1"></i> VERSO — FACE ARRIÈRE
                </span>
                <span class="badge bg-light text-secondary border">Format standard CR80 (85.6 × 54 mm)</span>
            </div>

            <div id="verso-{{ $rid }}"
                 class="carte-container-card carte-box-print"
                 style="
                    width: 536px;
                    height: 338px;
                    border-radius: 12px;
                    font-family: 'Poppins', 'Segoe UI', system-ui, -apple-system, sans-serif;
                    box-sizing: border-box;
                    overflow: hidden;
                    position: relative;
                    background-color: #ffffff;
                    background-image: url('{{ asset('images/template_carte_verso.png') }}');
                    background-size: 100% 100%;
                    background-repeat: no-repeat;
                    background-position: center;
                    border: 1px solid #d0d7de;
                    box-shadow: 0 8px 24px rgba(4, 86, 137, 0.14);
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                ">

                {{-- Texte discret au verso en pied de carte --}}
                <div style="
                    position: absolute;
                    bottom: 8px;
                    left: 20px;
                    right: 20px;
                    text-align: center;
                    font-size: 11px;
                    color: #045689;
                    font-weight: 700;
                    letter-spacing: 0.3px;
                ">
                    Présentez cette carte aux prestataires agréés &bull;
                </div>

            </div>
        </div>

    </div>
</div>

@push('scripts')
@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
/**
 * Capture un élément DOM en PNG haute résolution (sans marge) et télécharge le fichier.
 */
function downloadCartePNG(containerId, filename) {
    const el = document.getElementById(containerId);
    if (!el) {
        alert('Élément de carte introuvable.');
        return;
    }

    const wrapper = el.closest('.carte-card-wrapper');
    let wasHidden = wrapper && wrapper.classList.contains('d-none');

    if (wasHidden) {
        wrapper.classList.remove('d-none');
        Object.assign(wrapper.style, { position: 'absolute', left: '-9999px', top: '-9999px' });
    }

    // Capture à échelle 3x pour un rendu ultra haute définition (impression carte plastique 300 DPI)
    html2canvas(el, {
        scale: 3,
        useCORS: true,
        allowTaint: true,
        backgroundColor: '#ffffff'
    })
    .then(canvas => {
        if (wasHidden && wrapper) {
            wrapper.classList.add('d-none');
            Object.assign(wrapper.style, { position: '', left: '', top: '' });
        }
        const a = document.createElement('a');
        a.download = filename + '.png';
        a.href = canvas.toDataURL('image/png', 1.0);
        a.click();
    })
    .catch(err => {
        if (wasHidden && wrapper) {
            wrapper.classList.add('d-none');
            Object.assign(wrapper.style, { position: '', left: '', top: '' });
        }
        console.error('Erreur PNG:', err);
        alert('Erreur lors de la création du fichier PNG.');
    });
}
</script>
@endonce

<script>
function downloadBothCartesPNG{{ $rid }}() {
    downloadCartePNG('recto-{{ $rid }}', 'carte_recto_{{ $matricule }}');
    setTimeout(() => {
        downloadCartePNG('verso-{{ $rid }}', 'carte_verso_{{ $matricule }}');
    }, 600);
}
</script>
@endpush

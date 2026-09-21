@props(['carte', 'salarie' => null, 'onlyRecto' => true])

@php
    $salarie        = $salarie ?? $carte->salarie;
    $entrepriseNom  = mb_strtoupper($salarie->entreprise->raison_sociale ?? $salarie->entreprise->nom_entreprise ?? 'SOCIETE');
    $matricule      = $salarie->matricule ?? $carte->matricule ?? 'XXXXX';
    $numeroCarte    = $carte->numero_carte ?? '—';
    $dateEmission   = $carte->date_emission ? \Carbon\Carbon::parse($carte->date_emission)->translatedFormat('d/m/Y') : '—';
    $nom            = mb_strtoupper($salarie->nom ?? 'XXXX');
    $prenom         = $salarie->prenom ?? 'XXXXXX';
    $dateNaissance  = $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->translatedFormat('d/m/Y') : '—';
    $statut         = $carte->statut ?? 'actif';
    $rid            = $carte->id_carte; // shorthand for unique DOM IDs
@endphp

<style>
@media print {
    body * { visibility: hidden; }
    .carte-assure-wrapper, .carte-assure-wrapper * { visibility: visible; }
    .carte-assure-wrapper {
        position: absolute; left: 0; top: 0;
        width: 100%; margin: 0; padding: 0;
    }
    .carte-assure-actions,
    .carte-card-label { display: none !important; }
    #verso-wrapper-{{ $rid }} { display: block !important; margin-top: 32px !important; }
}
</style>

<div class="carte-assure-wrapper d-flex flex-column align-items-center gap-4 py-3">

    {{-- ── Actions ── --}}
    <div class="carte-assure-actions d-flex flex-wrap justify-content-center gap-2">
        <button type="button" class="btn btn-success rounded-pill px-4 py-2 shadow-sm fw-semibold"
                onclick="downloadBothCartesPNG{{ $rid }}()">
            <i class="bi bi-download me-2"></i>PNG Recto-Verso
        </button>
        <a href="{{ route('cartes-assurees.download', $carte->id_carte) }}"
           class="btn btn-outline-danger rounded-pill px-4 py-2 shadow-sm fw-semibold" target="_blank">
            <i class="bi bi-file-earmark-pdf me-2"></i>Télécharger PDF
        </a>
        <button type="button" class="btn btn-outline-primary rounded-pill px-4 py-2 shadow-sm fw-semibold"
                onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Imprimer
        </button>
    </div>

    <div class="d-flex flex-column align-items-center gap-5 w-100">

        {{-- ══════════════════════════════════════════════════
             RECTO  (85 × 54 mm → 536 × 340 px @ 160 dpi)
        ══════════════════════════════════════════════════ --}}
        <div class="carte-card-wrapper">
            <div class="carte-card-label d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold">
                    <i class="bi bi-credit-card-2-front me-1"></i> CARTE PARTICIPANT — RECTO
                </span>
                <span class="badge bg-light text-secondary border">85 × 54 mm</span>
            </div>

            <div id="recto-{{ $rid }}"
                 style="
                    width:536px; height:340px; border-radius:14px;
                    font-family:'Poppins','Segoe UI',sans-serif;
                    box-sizing:border-box; overflow:hidden;
                    position:relative; background:#ffffff;
                    border:3px solid #0A4D8C;
                    box-shadow:0 6px 28px rgba(10,77,140,.18);
                ">

                {{-- ── Fond décoratif ── --}}
                {{-- Cercles géométriques en haut à droite --}}
                <div style="position:absolute;top:-48px;right:-48px;width:170px;height:170px;border-radius:50%;background:rgba(10,77,140,.07);z-index:0;"></div>
                <div style="position:absolute;top:-20px;right:-20px;width:100px;height:100px;border-radius:50%;background:rgba(10,77,140,.10);z-index:0;"></div>
                {{-- Vague verte en bas à gauche --}}
                <div style="position:absolute;bottom:-30px;left:-30px;width:130px;height:130px;border-radius:50%;background:rgba(6,107,39,.08);z-index:0;"></div>

                {{-- ── Barre supérieure ── --}}
                <div style="
                    background:linear-gradient(90deg,#0A4D8C 0%,#1672C8 100%);
                    padding:8px 18px 7px;
                    position:relative; z-index:2;
                    display:flex; align-items:center; justify-content:space-between;
                    -webkit-print-color-adjust:exact; print-color-adjust:exact;
                ">
                    <div>
                        <div style="font-size:9.5px;font-weight:800;color:#fff;text-transform:uppercase;letter-spacing:.4px;line-height:1.25;">
                            Institut de Prévoyance Maladie Inter‑Entreprises
                        </div>
                        <div style="font-size:8px;color:rgba(255,255,255,.80);font-weight:500;margin-top:1px;">
                            Mbaarum Koolute — Cité de l'Emergence ADDOHA, Imm. 7 App. N°4
                        </div>
                    </div>
                    {{-- Puce dorée (style carte bancaire) --}}
                    <div style="width:34px;height:24px;background:linear-gradient(135deg,#D4A017 0%,#F5D068 50%,#B8860B 100%);border-radius:4px;opacity:.9;flex-shrink:0;"></div>
                </div>

                {{-- ── Corps principal ── --}}
                <div style="display:flex;flex:1;padding:14px 18px 10px;position:relative;z-index:2;box-sizing:border-box;align-items:flex-start;gap:14px;height:calc(100% - 62px);">

                    {{-- Photo --}}
                    <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <div style="
                            width:86px;height:86px;border-radius:12px;
                            border:2.5px solid #0A4D8C;
                            overflow:hidden;background:#EEF4FB;
                            display:flex;align-items:center;justify-content:center;
                            box-shadow:0 2px 8px rgba(10,77,140,.15);
                        ">
                            @if($salarie->photo_url)
                                <img src="{{ $salarie->photo_url }}" alt="Photo"
                                     style="width:100%;height:100%;object-fit:cover;">
                            @else
                                <svg width="44" height="44" viewBox="0 0 24 24" fill="#90a4ae">
                                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                                </svg>
                            @endif
                        </div>

                        {{-- QR Code --}}
                        <div style="
                            width:72px;height:72px;
                            background:#fff;padding:3px;border-radius:6px;
                            border:1.5px solid #dde4ed;
                            display:flex;align-items:center;justify-content:center;
                            box-shadow:0 1px 4px rgba(0,0,0,.08);
                        ">
                            {!! $carte->qr_code !!}
                        </div>
                    </div>

                    {{-- Infos --}}
                    <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-start;gap:0;">

                        {{-- Badge Matricule --}}
                        <div style="
                            display:inline-flex;align-items:center;gap:6px;
                            background:linear-gradient(90deg,#076B27,#0E9C3A);
                            color:#fff;font-size:12px;font-weight:800;
                            padding:5px 12px;border-radius:6px;
                            letter-spacing:.4px;margin-bottom:10px;
                            -webkit-print-color-adjust:exact;print-color-adjust:exact;
                            box-shadow:0 2px 6px rgba(6,107,39,.3);
                            width:fit-content;
                        ">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="white" style="flex-shrink:0;">
                                <path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4z"/>
                            </svg>
                            {{ $matricule }}
                        </div>

                        {{-- Champs --}}
                        <table style="border-collapse:collapse;width:100%;font-size:11.5px;color:#1a2333;">
                            <tr>
                                <td style="padding:3px 0;color:#5a6880;font-weight:500;white-space:nowrap;padding-right:8px;width:90px;">Nom</td>
                                <td style="padding:3px 0;font-weight:800;font-size:13px;color:#0A4D8C;text-transform:uppercase;">{{ $nom }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:#5a6880;font-weight:500;padding-right:8px;">Prénom</td>
                                <td style="padding:3px 0;font-weight:600;color:#1a2333;">{{ $prenom }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:#5a6880;font-weight:500;padding-right:8px;">Né(e) le</td>
                                <td style="padding:3px 0;font-weight:600;color:#1a2333;">{{ $dateNaissance }}</td>
                            </tr>
                            <tr>
                                <td style="padding:3px 0;color:#5a6880;font-weight:500;padding-right:8px;">Société</td>
                                <td style="padding:3px 0;font-weight:700;color:#1a2333;font-size:10.5px;">{{ $entrepriseNom }}</td>
                            </tr>
                        </table>

                        {{-- Numéro de carte & Date --}}
                        <div style="
                            display:flex;align-items:center;justify-content:space-between;
                            margin-top:10px;padding-top:8px;
                            border-top:1px dashed #c8d6e8;
                            font-size:10px;color:#7a8aA0;
                        ">
                            <span style="font-weight:600;letter-spacing:.3px;">N° <span style="color:#0A4D8C;font-weight:800;">{{ $numeroCarte }}</span></span>
                            <span>Émise le <strong style="color:#0A4D8C;">{{ $dateEmission }}</strong></span>
                        </div>
                    </div>
                </div>

                {{-- ── Barre inférieure ── --}}
                <div style="
                    position:absolute;bottom:0;left:0;right:0;
                    background:linear-gradient(90deg,#076B27 0%,#0E9C3A 40%,#0A4D8C 100%);
                    height:12px;
                    -webkit-print-color-adjust:exact;print-color-adjust:exact;
                "></div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════
             VERSO
        ══════════════════════════════════════════════════ --}}
        <div class="carte-card-wrapper {{ $onlyRecto ? 'd-none' : '' }}" id="verso-wrapper-{{ $rid }}">
            <div class="carte-card-label d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold">
                    <i class="bi bi-credit-card-2-back me-1"></i> VERSO — Face Arrière
                </span>
                <span class="badge bg-light text-secondary border">85 × 54 mm</span>
            </div>

            <div id="verso-{{ $rid }}"
                 style="
                    width:536px;height:340px;border-radius:14px;
                    font-family:'Poppins','Segoe UI',sans-serif;
                    box-sizing:border-box;overflow:hidden;
                    position:relative;
                    background:linear-gradient(145deg,#0A4D8C 0%,#0C3A6B 60%,#06200E 100%);
                    border:3px solid #0A4D8C;
                    box-shadow:0 6px 28px rgba(10,77,140,.18);
                    -webkit-print-color-adjust:exact;print-color-adjust:exact;
                ">

                {{-- Cercles décoratifs --}}
                <div style="position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;background:rgba(255,255,255,.05);"></div>
                <div style="position:absolute;bottom:-50px;left:-50px;width:180px;height:180px;border-radius:50%;background:rgba(14,156,58,.10);"></div>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:240px;height:240px;border-radius:50%;border:1px solid rgba(255,255,255,.06);"></div>

                {{-- Bande magnétique (déco) --}}
                <div style="
                    background:rgba(0,0,0,.35);
                    height:40px;margin-top:32px;width:100%;
                    position:relative;z-index:2;
                    -webkit-print-color-adjust:exact;print-color-adjust:exact;
                "></div>

                {{-- Contenu central --}}
                <div style="
                    position:relative;z-index:3;
                    display:flex;flex-direction:column;align-items:center;justify-content:center;
                    padding:16px 24px 12px;text-align:center;
                ">
                    {{-- Logo / Branding --}}
                    <img src="{{ asset('logo.png') }}" alt="Logo IPM"
                         style="max-height:72px;width:auto;object-fit:contain;margin-bottom:8px;filter:brightness(0) invert(1);"
                         onerror="this.style.display='none';document.getElementById('verso-fallback-{{ $rid }}').style.display='flex';">

                    <div id="verso-fallback-{{ $rid }}" style="display:none;flex-direction:column;align-items:center;gap:4px;margin-bottom:8px;">
                        <svg width="42" height="42" viewBox="0 0 100 100" fill="none">
                            <path d="M50 10 C30 10 15 28 15 48 C15 68 50 92 50 92 C50 92 85 68 85 48 C85 28 70 10 50 10Z" fill="rgba(255,255,255,.15)"/>
                            <path d="M50 22 C37 22 28 33 28 45 C28 58 50 76 50 76 C50 76 72 58 72 45 C72 33 63 22 50 22Z" fill="#1EA3E4"/>
                            <circle cx="50" cy="40" r="7" fill="#fff"/>
                        </svg>
                        <div style="font-size:22px;font-weight:900;color:#fff;letter-spacing:1px;">
                            <span style="color:#1EA3E4;">MBAARUM</span>&nbsp;<span>KOOLUTE</span>
                        </div>
                    </div>

                    <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.85);letter-spacing:.3px;margin-bottom:14px;">
                        Institut de prévoyance maladie inter‑entreprises
                    </div>

                    {{-- Ligne de séparation --}}
                    <div style="width:80px;height:2px;background:linear-gradient(90deg,transparent,rgba(255,255,255,.4),transparent);margin-bottom:12px;"></div>

                    {{-- Informations utiles verso --}}
                    <div style="font-size:9.5px;color:rgba(255,255,255,.65);line-height:1.7;max-width:380px;">
                        <strong style="color:rgba(255,255,255,.9);">En cas d'urgence :</strong> Présentez cette carte à tout prestataire du réseau conventionné.<br>
                        <strong style="color:rgba(255,255,255,.9);">Statut :</strong>
                        @if($statut === 'actif')
                            <span style="color:#4ade80;font-weight:700;">● ACTIF</span>
                        @else
                            <span style="color:#f87171;font-weight:700;">● {{ mb_strtoupper($statut) }}</span>
                        @endif
                        &nbsp;|&nbsp; <strong style="color:rgba(255,255,255,.9);">Carte N° :</strong> {{ $numeroCarte }}
                    </div>
                </div>

                {{-- Barre verte inférieure --}}
                <div style="
                    position:absolute;bottom:0;left:0;right:0;
                    background:linear-gradient(90deg,#076B27,#0E9C3A);
                    height:12px;
                    -webkit-print-color-adjust:exact;print-color-adjust:exact;
                "></div>
            </div>
        </div>

    </div>{{-- fin flex flex-column --}}
</div>{{-- fin carte-assure-wrapper --}}

@push('scripts')
@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
/**
 * Capture un élément DOM en PNG haute résolution et déclenche le téléchargement.
 */
function downloadCartePNG(containerId, filename) {
    const el = document.getElementById(containerId);
    if (!el) { alert('Élément introuvable.'); return; }

    const wrapper = el.closest('.carte-card-wrapper');
    let wasHidden = wrapper?.classList.contains('d-none');

    if (wasHidden) {
        wrapper.classList.remove('d-none');
        Object.assign(wrapper.style, { position: 'absolute', left: '-9999px', top: '-9999px' });
    }

    html2canvas(el, { scale: 3, useCORS: true, allowTaint: true, backgroundColor: null })
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
@endpush

{{-- Fonctions spécifiques à cette instance de carte (évite les conflits entre plusieurs cartes sur la même page) --}}
<script>
function downloadBothCartesPNG{{ $rid }}() {
    downloadCartePNG('recto-{{ $rid }}', 'carte_recto_{{ $matricule }}');
    setTimeout(() => downloadCartePNG('verso-{{ $rid }}', 'carte_verso_{{ $matricule }}'), 700);
}
</script>

@props(['carte', 'salarie' => null, 'onlyRecto' => true])

@php
    $salarie = $salarie ?? $carte->salarie;
    $entrepriseNom = $salarie->entreprise->raison_sociale ?? $salarie->entreprise->nom_entreprise ?? 'SOCIETE';
    $matricule = $salarie->matricule ?? $carte->matricule ?? 'XXXXX';
    $nom = mb_strtoupper($salarie->nom ?? 'XXXX');
    $prenom = $salarie->prenom ?? 'XXXXXX';
    $telephone = $salarie->telephone ?? 'XXXXXX';
    $dateNaissance = $salarie->date_naissance ? \Carbon\Carbon::parse($salarie->date_naissance)->format('d/m/Y') : 'XXXX';
@endphp

<div class="carte-assure-wrapper d-flex flex-column align-items-center gap-3 py-2">
    <!-- Controls Header -->
    <div class="d-flex justify-content-center mb-2 w-100">
        <button type="button" class="btn btn-success rounded-pill px-4 py-2 shadow-sm fw-bold fs-6" onclick="downloadBothCartesPNG('recto-{{ $carte->id_carte }}', 'verso-{{ $carte->id_carte }}', '{{ $matricule }}')">
            <i class="bi bi-download me-2"></i>Télécharger Recto-Verso (PNG)
        </button>
    </div>

    <div class="d-flex flex-column align-items-center justify-content-center w-100">
        <!-- CARTE RECTO (Mis en avant) -->
        <div class="carte-card-wrapper">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold fs-6">
                    <i class="bi bi-credit-card-2-front me-1"></i> CARTE PARTICIPANT (RECTO)
                </span>
                <span class="badge bg-light text-dark border">85 x 54 mm</span>
            </div>

            <div id="recto-{{ $carte->id_carte }}" class="carte-box position-relative bg-white shadow overflow-hidden d-flex flex-column justify-content-between" style="width: 536px; height: 340px; border-radius: 10px; font-family: 'Poppins', 'Montserrat', 'Segoe UI', sans-serif; box-sizing: border-box; margin: 0; padding: 0; border: 3.5px solid #005689;">
                <!-- Top Header Banner (Bande bleue supérieure) -->
                <div style="background-color: #005689; color: #ffffff; text-align: center; padding: 8px 10px; z-index: 2; position: relative; -webkit-print-color-adjust: exact; print-color-adjust: exact; width: 100%; box-sizing: border-box;">
                    <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px; line-height: 1.2;">
                        INSTITUT DE PREVOYANCE MALADIE INTER–ENTREPRISE MBAARUM KOOLUTE
                    </div>
                    <div style="font-size: 8.5px; font-weight: 700; margin-top: 2px; opacity: 0.95;">
                        SIEGE : Cité de l’Emergence ADDOHA Immeuble 7 Appartement N°4
                    </div>
                </div>

                <!-- Body Grid -->
                <div style="display: flex; flex: 1; padding: 8px 16px; position: relative; z-index: 2; box-sizing: border-box; align-items: center;">
                    <!-- Left Side: Photo, Société, QR Code -->
                    <div style="width: 34%; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <!-- Photo circle with double blue/green ring -->
                        <div style="width: 84px; height: 84px; border-radius: 50%; border: 3.5px solid #005689; outline: 2px solid #076B27; outline-offset: -5px; overflow: hidden; background: #eef2f5; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.12);">
                            @if($salarie->photo)
                                <img src="{{ $salarie->photo->url }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <svg width="46" height="46" viewBox="0 0 24 24" fill="#90a4ae"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                            @endif
                        </div>

                        <!-- Société -->
                        <div style="font-size: 11px; font-weight: 800; color: #111111; text-transform: uppercase; margin-top: 5px; text-align: center; line-height: 1.1; max-width: 140px; word-break: break-word;">
                            {{ $entrepriseNom }}
                        </div>

                        <!-- QR Code -->
                        <div style="margin-top: 5px; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: #fff; padding: 2px; border-radius: 4px;">
                            {!! $carte->qr_code !!}
                        </div>
                    </div>

                    <!-- Right Side: Details -->
                    <div style="width: 66%; padding-left: 14px; box-sizing: border-box; display: flex; flex-direction: column; justify-content: center;">
                        <!-- Green Matricule Badge -->
                        <div style="background-color: #076B27; color: #ffffff; font-size: 13px; font-weight: 800; padding: 6px 14px; border-radius: 3px; display: inline-block; width: fit-content; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(0,0,0,0.15); -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            MATRICULE : {{ $matricule }}
                        </div>

                        <!-- Fields -->
                        <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 7px; font-size: 12.5px; color: #111;">
                            <div style="display: flex; align-items: center;">
                                <span style="font-weight: 500; width: 130px; color: #333;">Nom :</span>
                                <span style="font-weight: 700; text-transform: uppercase; font-size: 13.5px; color: #000;">{{ $nom }}</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span style="font-weight: 500; width: 130px; color: #333;">Prénom :</span>
                                <span style="font-weight: 600; color: #111;">{{ $prenom }}</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span style="font-weight: 500; width: 130px; color: #333;">Téléphone :</span>
                                <span style="font-weight: 600; color: #111;">{{ $telephone }}</span>
                            </div>
                            <div style="display: flex; align-items: center;">
                                <span style="font-weight: 500; width: 130px; color: #333;">Date de Naissance :</span>
                                <span style="font-weight: 600; color: #111;">{{ $dateNaissance }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Footer Banner (Bande bleue inférieure) -->
                <div style="background-color: #005689; height: 14px; width: 100%; z-index: 2; position: relative; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
            </div>
        </div>

        <!-- CARTE VERSO (Masquée à l'affichage si onlyRecto=true, disponible pour le téléchargement PNG) -->
        <div class="carte-card-wrapper {{ $onlyRecto ? 'd-none' : 'mt-4' }}" id="verso-wrapper-{{ $carte->id_carte }}">
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold fs-6">
                    <i class="bi bi-credit-card-2-back me-1"></i> VERSO (Face Arrière)
                </span>
                <span class="badge bg-light text-dark border">85 x 54 mm</span>
            </div>

            <div id="verso-{{ $carte->id_carte }}" class="carte-box position-relative bg-white shadow-sm overflow-hidden d-flex flex-column align-items-center justify-content-between" style="width: 536px; height: 340px; border-radius: 10px; font-family: 'Poppins', 'Montserrat', 'Segoe UI', sans-serif; box-sizing: border-box; margin: 0; padding: 0; border: 3.5px solid #005689;">
                <!-- Top Header Banner (Bande bleue supérieure Verso) -->
                <div style="background-color: #005689; height: 14px; width: 100%; z-index: 2; position: relative; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>

                <!-- Center Content: Logo & Branding -->
                <div style="text-align: center; z-index: 2; padding: 20px; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                    <img src="{{ asset('logo.png') }}" alt="Mbaarum Koolute Logo" style="max-height: 85px; width: auto; object-fit: contain; margin-bottom: 8px;" onerror="this.onerror=null; this.style.display='none'; document.getElementById('verso-fallback-logo-{{ $carte->id_carte }}').style.display='block';">
                    
                    <div id="verso-fallback-logo-{{ $carte->id_carte }}" style="display: none;">
                        <div style="margin-bottom: 6px;">
                            <svg width="50" height="50" viewBox="0 0 100 100" fill="none">
                                <path d="M50 15 C35 15 25 27 25 40 C25 55 50 78 50 78 C50 78 75 55 75 40 C75 27 65 15 50 15 Z" fill="#005689"/>
                                <path d="M50 24 C42 24 35 31 35 40 C35 50 50 66 50 66 C50 66 65 50 65 40 C65 31 58 24 50 24 Z" fill="#1EA3E4"/>
                                <circle cx="50" cy="36" r="5" fill="#ffffff"/>
                            </svg>
                        </div>
                        <div style="font-size: 28px; font-weight: 900; letter-spacing: 1px; line-height: 1;">
                            <span style="color: #1EA3E4;">MBAARUM</span> <span style="color: #005689;">KOOLUTE</span>
                        </div>
                    </div>

                    <div style="font-size: 14px; font-weight: 700; color: #005689; margin-top: 6px; letter-spacing: 0.2px;">
                        Institut de prévoyance maladie inter-entreprises
                    </div>
                </div>

                <!-- Bottom Footer Banner (Bande bleue inférieure Verso) -->
                <div style="background-color: #005689; height: 14px; width: 100%; z-index: 2; position: relative; -webkit-print-color-adjust: exact; print-color-adjust: exact;"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@once
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function downloadCartePNG(containerId, filename) {
        const el = document.getElementById(containerId);
        if (!el) {
            alert('Élément introuvable pour le téléchargement.');
            return;
        }

        // Support captured rendering even if wrapper is hidden (d-none)
        const wrapper = el.closest('.carte-card-wrapper');
        let wasHidden = false;
        if (wrapper && wrapper.classList.contains('d-none')) {
            wasHidden = true;
            wrapper.classList.remove('d-none');
            wrapper.style.position = 'absolute';
            wrapper.style.left = '-9999px';
            wrapper.style.top = '-9999px';
        }

        html2canvas(el, {
            scale: 3, // High-resolution canvas rendering
            useCORS: true,
            allowTaint: true,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            if (wasHidden && wrapper) {
                wrapper.classList.add('d-none');
                wrapper.style.position = '';
                wrapper.style.left = '';
                wrapper.style.top = '';
            }

            const link = document.createElement('a');
            link.download = filename + '.png';
            link.href = canvas.toDataURL('image/png', 1.0);
            link.click();
        }).catch(err => {
            if (wasHidden && wrapper) {
                wrapper.classList.add('d-none');
                wrapper.style.position = '';
                wrapper.style.left = '';
                wrapper.style.top = '';
            }
            console.error('Erreur lors du téléchargement PNG:', err);
            alert('Erreur lors de la création du fichier PNG.');
        });
    }

    function downloadBothCartesPNG(rectoId, versoId, matricule) {
        downloadCartePNG(rectoId, 'carte_recto_' + matricule);
        setTimeout(() => {
            downloadCartePNG(versoId, 'carte_verso_' + matricule);
        }, 600);
    }
</script>
@endonce
@endpush

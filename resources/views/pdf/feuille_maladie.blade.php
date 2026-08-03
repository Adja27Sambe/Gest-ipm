<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feuille de Malade</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header-table { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-text { font-size: 24px; font-weight: bold; color: #28a745; }
        .company-info { text-align: right; font-size: 12px; color: #555; }

        .title-box { background-color: #f4f6f9; border-left: 5px solid #28a745; padding: 15px; margin-bottom: 30px; }
        .title-box h2 { margin: 0 0 5px 0; color: #28a745; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .title-box .doc-number { font-size: 14px; font-weight: bold; color: #555; }

        .info-section { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .info-section th { background-color: #f8f9fa; color: #333; font-weight: bold; text-align: left; padding: 10px; border: 1px solid #ddd; width: 40%; }
        .info-section td { padding: 10px; border: 1px solid #ddd; width: 60%; }

        .section-title { font-size: 16px; color: #28a745; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px; }

        .footer { margin-top: 50px; width: 100%; text-align: center; font-size: 11px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { text-align: center; width: 33%; }
        .signature-line { margin-top: 50px; border-top: 1px dashed #000; width: 60%; display: inline-block; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
    @endphp
    <table class="header-table">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Logo IPM" style="max-height: 45px; object-fit: contain;">
                @else
                    <div class="logo-text">IPM Mbaarum Koolute</div>
                @endif

                <div style="font-size: 11px; color: #777; margin-top: 5px;">Institution de Prévoyance Maladie
                    <br>
                    INTER-ENTREPRISES <br>
                    IMMEUBLE 7 - CITÉ DE L'ÉMERGENCE ADDOHA<br>
                    TEL:33 822 37 34
                </div>
            </td>
            <td class="company-info">
                Date d'émission : <strong>{{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') }}</strong><br>
                Date limite de Validité : <strong>{{ $demande->feuilleMaladie->date_validite ? \Carbon\Carbon::parse($demande->feuilleMaladie->date_validite)->format('d/m/Y') : 'N/A' }}</strong><br>
            </td>
        </tr>
    </table>

    <div class="title-box">
        <h2>FEUILLE DE MALADIE</h2>
        <div class="doc-number">Numéro : {{ $demande->feuilleMaladie->numero_feuille ?? 'N/A' }}</div>
    </div>

    <div class="section-title">Informations du Bénéficiaire</div>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #ddd;">
        <tr>
            <td style="padding: 12px 15px; border-right: 1px solid #ddd; width: 28%; background-color: #f8f9fa; vertical-align: middle;">
                <span style="font-size: 10px; color: #888; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Matricule Salarié</span>
                <span style="font-size: 18px; font-weight: bold; color: #28a745; letter-spacing: 1px;">{{ $demande->salarie->matricule ?? 'N/A' }}</span>
            </td>
            <td style="padding: 12px 15px; border-right: 1px solid #ddd; width: 38%; vertical-align: middle;">
                <span style="font-size: 10px; color: #888; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Participant concerné</span>
                @if($demande->ayantDroit)
                    <strong style="font-size: 14px;">{{ $demande->ayantDroit->prenom }} {{ $demande->ayantDroit->nom }}</strong><br>
                    <span style="font-size: 11px; color: #666;">(Ayant-droit de {{ $demande->salarie->prenom }} {{ $demande->salarie->nom }})</span>
                @else
                    <strong style="font-size: 14px;">{{ $demande->salarie->prenom }} {{ $demande->salarie->nom }}</strong>
                    <span style="font-size: 11px; color: #666;">&nbsp;&nbsp;Lui-même</span>
                @endif
            </td>
            <td style="padding: 12px 15px; width: 34%; vertical-align: middle;">
                <span style="font-size: 10px; color: #888; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Entreprise</span>
                <strong style="font-size: 13px;">{{ $demande->salarie->entreprise->raison_sociale ?? 'Non spécifiée' }}</strong>
            </td>
        </tr>
    </table>

    <div class="section-title">Détails de la Consultation</div>
    <table class="info-section">
        <tr>
            <th>Praticien / Prestataire</th>
            <td><strong>{{ $demande->praticien->nom ?? 'Non spécifié' }}</strong></td>
        </tr>
    </table>

    <div class="section-title">Diagnostic</div>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td colspan="2" style="border: 1px solid #ddd; padding: 10px 12px; background-color: #f8f9fa; font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase; letter-spacing: 0.5px;">
                Diagnostic / Pathologie
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: 1px solid #ddd; border-top: none; padding: 15px; height: 60px; vertical-align: top; font-size: 13px;">
                {{ $demande->feuilleMaladie->diagnostic ?? '' }}
            </td>
        </tr>
        <tr></tr>
        <tr>

            <td style="border: 1px solid #ddd; border-top: none; padding: 10px 12px; background-color: #f8f9fa; font-weight: bold; font-size: 12px; color: #555; text-transform: uppercase; letter-spacing: 0.5px; width: 50%;">
                Montant total des soins (FCFA)
            </td>
            <td style="border: 1px solid #ddd; border-top: none; border-left: none; padding: 10px 12px; height: 35px; vertical-align: middle; width: 50%;">
                &nbsp;
            </td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <strong>Le Gérant / IPM</strong>
                <div class="signature-line"></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        <b>Cette demande est valable jusqu'à la fin de ce mois</b><br>
        Document généré informatiquement par Gest-IPM le {{ now()->format('d/m/Y à H:i') }}.
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bon de Commande</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header-table { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #0056b3; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-text { font-size: 24px; font-weight: bold; color: #0056b3; }
        .company-info { text-align: right; font-size: 12px; color: #555; }
        
        .title-box { background-color: #f4f6f9; border-left: 5px solid #0056b3; padding: 15px; margin-bottom: 30px; }
        .title-box h2 { margin: 0 0 5px 0; color: #0056b3; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .title-box .doc-number { font-size: 14px; font-weight: bold; color: #555; }
        
        .info-section { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .info-section th { background-color: #f8f9fa; color: #333; font-weight: bold; text-align: left; padding: 10px; border: 1px solid #ddd; width: 40%; }
        .info-section td { padding: 10px; border: 1px solid #ddd; width: 60%; }
        
        .section-title { font-size: 16px; color: #0056b3; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px; }
        
        .footer { margin-top: 50px; width: 100%; text-align: center; font-size: 11px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { text-align: center; width: 50%; }
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
                INTER-ENTREPRISES <BR>
            IMMEUBLE 7 - CITÉ DE L'ÉMERGENCE ADDOHA
        TEL:33 822 37 34</BR>
                </div>
            </td>
            <td class="company-info">
                Date d'émission : <strong>{{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') }}</strong><br>
                Date limite de Validité : <strong>{{ $demande->bonCommande->date_validite ? \Carbon\Carbon::parse($demande->bonCommande->date_validite)->format('d/m/Y') : 'N/A' }}</strong><br>
            </td>
        </tr>
    </table>
    
    <div class="title-box">
        <h2>BON DE COMMANDE</h2>
        <div class="doc-number">Numéro : {{ $demande->bonCommande->numero_bon ?? 'N/A' }}</div>
    </div>
    
    <div class="section-title">Informations du Bénéficiaire</div>
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; border: 1px solid #ddd;">
        <tr>
            <td style="padding: 12px 15px; border-right: 1px solid #ddd; width: 28%; background-color: #f8f9fa; vertical-align: middle;">
                <span style="font-size: 10px; color: #888; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Matricule Salarié</span>
                <span style="font-size: 18px; font-weight: bold; color: #0056b3; letter-spacing: 1px;">{{ $demande->salarie->matricule ?? 'N/A' }}</span>
            </td>
            <td style="padding: 12px 15px; border-right: 1px solid #ddd; width: 38%; vertical-align: middle;">
                <span style="font-size: 10px; color: #888; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Patient concerné</span>
                @if($demande->ayantDroit)
                    <strong style="font-size: 14px;">{{ $demande->ayantDroit->prenom }} {{ $demande->ayantDroit->nom }}</strong><br>
                    <span style="font-size: 11px; color: #666;">(Ayant-droit de {{ $demande->salarie->prenom }} {{ $demande->salarie->nom }})</span>
                @else
                    <strong style="font-size: 14px;">{{ $demande->salarie->prenom }} {{ $demande->salarie->nom }}</strong>
                @endif
            </td>
            <td style="padding: 12px 15px; width: 34%; vertical-align: middle;">
                <span style="font-size: 10px; color: #888; display: block; margin-bottom: 2px; text-transform: uppercase; letter-spacing: 0.5px;">Entreprise</span>
                <strong style="font-size: 13px;">{{ $demande->salarie->entreprise->raison_sociale ?? 'Non spécifiée' }}</strong>
            </td>
        </tr>
    </table>

    <div class="section-title">Détails de la Prise en Charge</div>
    <table class="info-section">
        <tr>
            <th>Praticien / Pharmacie / Opticien</th>
            <td><strong>{{ $demande->pharmacie->nom ?? 'Non spécifié' }}</strong></td>
        </tr>
        <tr>
            <th>Date de l'ordonnance</th>
            <td>{{ $demande->bonCommande->date_ordonnance ? \Carbon\Carbon::parse($demande->bonCommande->date_ordonnance)->format('d/m/Y') : 'Non spécifiée' }}</td>
        </tr>
        <tr>
            <th>Nombre d'articles prescrits</th>
            <td>{{ $demande->bonCommande->nombre_articles ?? '1' }}</td>
        </tr>

    </table>

    <div class="section-title">Articles Prescrits</div>
    @php $nbArticles = intval($demande->bonCommande->nombre_articles ?? 1); @endphp
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
        <thead>
            <tr>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f8f9fa; text-align: center; width: 5%;">#</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f8f9fa; text-align: left; width: 45%;">Désignation de l'article</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f8f9fa; text-align: center; width: 15%;">Quantité</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f8f9fa; text-align: right; width: 20%;">Prix Unitaire</th>
                <th style="border: 1px solid #ddd; padding: 8px; background-color: #f8f9fa; text-align: right; width: 15%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 1; $i <= $nbArticles; $i++)
            <tr style="height: 32px;">
                <td style="border: 1px solid #ddd; padding: 8px; text-align: center; color: #aaa;">{{ $i }}</td>
                <td style="border: 1px solid #ddd; padding: 8px;"></td>
                <td style="border: 1px solid #ddd; padding: 8px;"></td>
                <td style="border: 1px solid #ddd; padding: 8px;"></td>
                <td style="border: 1px solid #ddd; padding: 8px;"></td>
            </tr>
            @endfor
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td style="text-align: center; width: 100%;">
                <strong>Le Gérant IPM</strong>
                <div class="signature-line"></div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Document généré informatiquement par Gest-IPM le {{ now()->format('d/m/Y à H:i') }}.<br>
        Ce document est strictement personnel et confidentiel.
    </div>
</body>
</html>

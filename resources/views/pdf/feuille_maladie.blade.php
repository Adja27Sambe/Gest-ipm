<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Feuille de Maladie</title>
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
    <table class="header-table">
        <tr>
            <td>
                <div class="logo-text">GEST-IPM</div>
                <div style="font-size: 11px; color: #777;">Institution de Prévoyance Maladie</div>
            </td>
            <td class="company-info">
                Date d'émission : <strong>{{ \Carbon\Carbon::parse($demande->date_demande)->format('d/m/Y') }}</strong><br>
                Service Médical
            </td>
        </tr>
    </table>
    
    <div class="title-box">
        <h2>FEUILLE DE MALADIE</h2>
        <div class="doc-number">Numéro : {{ $demande->feuilleMaladie->numero_feuille ?? 'N/A' }}</div>
    </div>
    
    <div class="section-title">Informations du Bénéficiaire</div>
    <table class="info-section">
        <tr>
            <th>Patient concerné</th>
            <td>
                @if($demande->ayantDroit)
                    <strong>{{ $demande->ayantDroit->prenom }} {{ $demande->ayantDroit->nom }}</strong><br>
                    <span style="font-size: 11px; color: #666;">(Ayant-droit de {{ $demande->salarie->prenom }} {{ $demande->salarie->nom }})</span>
                @else
                    <strong>{{ $demande->salarie->prenom }} {{ $demande->salarie->nom }}</strong>
                @endif
            </td>
        </tr>
        <tr>
            <th>Matricule Salarié</th>
            <td>{{ $demande->salarie->matricule ?? 'Non spécifié' }}</td>
        </tr>
        <tr>
            <th>Entreprise</th>
            <td>{{ $demande->salarie->entreprise->raison_sociale ?? 'Non spécifiée' }}</td>
        </tr>
    </table>

    <div class="section-title">Détails de la Consultation</div>
    <table class="info-section">
        <tr>
            <th>Praticien / Prestataire</th>
            <td><strong>{{ $demande->prestataire->nom_raison_sociale ?? 'Non spécifié' }}</strong></td>
        </tr>
        <tr>
            <th>Diagnostic</th>
            <td>{{ $demande->feuilleMaladie->diagnostic ?? 'Non spécifié' }}</td>
        </tr>
        <tr>
            <th>Observations Médicales</th>
            <td>{{ $demande->feuilleMaladie->observations ?? 'Aucune observation' }}</td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>
                <strong>Le Patient / Bénéficiaire</strong>
                <div class="signature-line"></div>
            </td>
            <td>
                <strong>Le Praticien</strong>
                <div class="signature-line"></div>
            </td>
            <td>
                <strong>Le Médecin Conseil / IPM</strong>
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

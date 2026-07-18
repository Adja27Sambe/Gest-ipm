<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Lettre de Garantie</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.5; }
        .header-table { width: 100%; margin-bottom: 30px; border-bottom: 2px solid #dc3545; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .logo-text { font-size: 24px; font-weight: bold; color: #dc3545; }
        .company-info { text-align: right; font-size: 12px; color: #555; }
        
        .title-box { background-color: #f4f6f9; border-left: 5px solid #dc3545; padding: 15px; margin-bottom: 30px; }
        .title-box h2 { margin: 0 0 5px 0; color: #dc3545; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .title-box .doc-number { font-size: 14px; font-weight: bold; color: #555; }
        
        .info-section { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .info-section th { background-color: #f8f9fa; color: #333; font-weight: bold; text-align: left; padding: 10px; border: 1px solid #ddd; width: 40%; }
        .info-section td { padding: 10px; border: 1px solid #ddd; width: 60%; }
        
        .section-title { font-size: 16px; color: #dc3545; border-bottom: 1px solid #eee; padding-bottom: 5px; margin-bottom: 15px; margin-top: 30px; }
        
        .footer { margin-top: 50px; width: 100%; text-align: center; font-size: 11px; color: #777; border-top: 1px solid #eee; padding-top: 10px; }
        .signatures { width: 100%; margin-top: 40px; }
        .signatures td { text-align: center; width: 50%; }
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
        <h2>LETTRE DE GARANTIE</h2>
        <div class="doc-number">Numéro : {{ $demande->lettreGarantie->numero_lettre ?? 'N/A' }}</div>
    </div>
    
    <div class="section-title">Informations du Patient Garanti</div>
    <table class="info-section">
        <tr>
            <th>Identité</th>
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
            <th>Date de naissance</th>
            <td>
                @if($demande->ayantDroit)
                    {{ $demande->ayantDroit->date_naissance ? \Carbon\Carbon::parse($demande->ayantDroit->date_naissance)->format('d/m/Y') : 'Non spécifiée' }}
                @else
                    {{ $demande->salarie->date_naissance ? \Carbon\Carbon::parse($demande->salarie->date_naissance)->format('d/m/Y') : 'Non spécifiée' }}
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

    <div class="section-title">Détails de la Garantie</div>
    <table class="info-section">
        <tr>
            <th>Établissement / Praticien</th>
            <td><strong>{{ $demande->prestataire->nom_raison_sociale ?? 'Non spécifié' }}</strong></td>
        </tr>
        <tr>
            <th>Acte Garanti</th>
            <td><strong>{{ $demande->lettreGarantie->choix_acte ?? 'Non spécifié' }}</strong></td>
        </tr>
        <tr>
            <th>Taux de Prise en Charge</th>
            <td><strong>{{ $demande->lettreGarantie->taux_prise_charge ?? '0' }}%</strong></td>
        </tr>
        <tr>
            <th>Date limite de Validité</th>
            <td>{{ $demande->lettreGarantie->date_validite ? \Carbon\Carbon::parse($demande->lettreGarantie->date_validite)->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <th>Motif de la garantie</th>
            <td>{{ $demande->motif ?? 'Non spécifié' }}</td>
        </tr>
        <tr>
            <th>Observations Complémentaires</th>
            <td>{{ $demande->lettreGarantie->observations ?? 'Aucune' }}</td>
        </tr>
    </table>

    <p style="font-size: 12px; margin-bottom: 20px;">
        <em>L'IPM s'engage à prendre en charge les frais liés à l'acte mentionné ci-dessus pour le patient désigné, à hauteur du taux indiqué, sous réserve de la réalisation effective de l'acte avant la date de fin de validité.</em>
    </p>

    <table class="signatures">
        <tr>
            <td>
                <strong>Le Directeur de l'IPM</strong>
                <div class="signature-line"></div>
            </td>
            <td>
                <strong>Le Médecin Conseil</strong>
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

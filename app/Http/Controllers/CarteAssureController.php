<?php

namespace App\Http\Controllers;

use App\Models\CarteAssure;
use App\Models\Salarie;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CarteAssureController extends Controller
{
    /**
     * Générer une nouvelle carte d'assuré pour le salarié donné.
     */
    public function generate(Salarie $salarie)
    {
        // Vérifier si le salarié a déjà une carte
        if ($salarie->carteAssure()->exists()) {
            return back()->with('error', 'Ce salarié possède déjà une carte d\'assuré.');
        }

        // Génération d'un numéro de carte unique : IPM-YYYYMMDD-RANDOM4
        $numeroCarte = 'IPM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        
        // S'assurer que le numéro est unique
        while (CarteAssure::where('numero_carte', $numeroCarte)->exists()) {
            $numeroCarte = 'IPM-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        }

        // Générer le contenu du QR Code : lien vers le dossier du participant
        $qrContent = route('salaries.show', $salarie);

        // Générer le QR code en SVG
        $qrCodeSvg = (string) QrCode::format('svg')->size(150)->generate($qrContent);

        // Créer la carte
        $carte = new CarteAssure([
            'numero_carte' => $numeroCarte,
            'matricule' => $salarie->matricule,
            'date_emission' => Carbon::now(),
            'statut' => CarteAssure::STATUT_ACTIF,
            'qr_code' => $qrCodeSvg,
        ]);

        $salarie->carteAssure()->save($carte);

        return back()->with('success', 'La carte d\'assuré a été générée avec succès.');
    }

    /**
     * Télécharger la carte au format PDF.
     */
    public function downloadPdf(CarteAssure $carte)
    {
        $carte->load('salarie.entreprise');
        
        $pdf = Pdf::loadView('pdf.carte_assure', compact('carte'));
        
        // Format standard ISO/IEC 7810 ID-1 / CR80 (85.6mm x 53.98mm = 242.65pt x 153.01pt)
        $pdf->setPaper([0, 0, 242.65, 153.01], 'landscape');

        return $pdf->download('carte_assure_' . $carte->numero_carte . '.pdf');
    }

    /**
     * Affiche la carte d'assuré (Recto-Verso) interactive.
     */
    public function show(CarteAssure $carte)
    {
        $carte->load(['salarie.entreprise']);
        return view('salaries.carte', compact('carte'));
    }
}

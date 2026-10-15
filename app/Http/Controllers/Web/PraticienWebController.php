<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Praticien;
use App\Http\Requests\StorePraticienRequest;
use App\Http\Requests\UpdatePraticienRequest;
use Illuminate\Http\Request;

class PraticienWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Praticien::with(['conventions.piecesJointes', 'piecesJointes']);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('NOMPRAT', 'like', "%{$search}%")
                  ->orWhere('CODEPRAT', 'like', "%{$search}%")
                  ->orWhere('SPECIALITE', 'like', "%{$search}%")
                  ->orWhere('TELPRAT', 'like', "%{$search}%")
                  ->orWhere('COMPTE_COMPTABLE', 'like', "%{$search}%");
            });
        }

        if ($request->filled('convention')) {
            if ($request->convention === '1') {
                $query->where(function($q) {
                    $q->where('A_CONVENTION', 1)
                      ->orWhereHas('conventions', fn($cq) => $cq->where('statut', 'active'));
                });
            } elseif ($request->convention === '0') {
                $query->where(function($q) {
                    $q->where('A_CONVENTION', 0)
                      ->whereDoesntHave('conventions', fn($cq) => $cq->where('statut', 'active'));
                });
            }
        }

        $perPage = $request->input('per_page', 10);
        $praticiens = $query->latest('PRCLEUNIK')->paginate($perPage)->withQueryString();
        
        $nextAutoCode = Praticien::generateNextCodePraticien();

        return view('praticiens.index', compact('praticiens', 'nextAutoCode'));
    }

    public function store(StorePraticienRequest $request)
    {
        $data = $request->validated();
        if (empty($data['code_praticien'])) {
            $data['code_praticien'] = Praticien::generateNextCodePraticien();
        }
        if (empty($data['date_enregistrement'])) {
            $data['date_enregistrement'] = now()->toDateString();
        }

        Praticien::create($data);
        return redirect()->route('praticiens.index')->with('success', 'Praticien ajouté avec succès avec le code ' . $data['code_praticien']);
    }

    public function update(UpdatePraticienRequest $request, Praticien $praticien)
    {
        $data = $request->validated();
        $praticien->update($data);
        return redirect()->route('praticiens.index')->with('success', 'Praticien mis à jour avec succès.');
    }

    public function destroy(Praticien $praticien)
    {
        $praticien->delete();
        return redirect()->route('praticiens.index')->with('success', 'Praticien supprimé avec succès.');
    }
}

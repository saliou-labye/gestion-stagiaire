<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stagiaire;
use App\Models\Evaluation;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStagiaires = Stagiaire::count();

        $stagiairesEnCours = Stagiaire::where(
            'statut',
            'en_cours'
        )->count();

        $stagiairesTermines = Stagiaire::where(
            'statut',
            'termine'
        )->count();

        $moyenneTechnique = Evaluation::avg('note_technique') ?? 0;

        $moyenneComportement = Evaluation::avg('note_comportement') ?? 0;

        $moyenneGenerale = (
            $moyenneTechnique + $moyenneComportement
        ) / 2;

        return response()->json([
            'total_stagiaires' => $totalStagiaires,
            'stagiaires_en_cours' => $stagiairesEnCours,
            'stagiaires_termines' => $stagiairesTermines,
            'moyenne_technique' => round($moyenneTechnique, 2),
            'moyenne_comportement' => round($moyenneComportement, 2),
            'moyenne_generale' => round($moyenneGenerale, 2),
        ]);
    }
}

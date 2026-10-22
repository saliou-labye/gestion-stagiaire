<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encadreur;
use App\Models\Stagiaire;
use Illuminate\Http\Request;

/**
 * « Mon espace » : consultation en lecture seule des données du compte connecté.
 */
class MeController extends Controller
{
    // Fiche du stagiaire connecté, ses affectations et ses évaluations
    public function stagiaire(Request $request)
    {
        $stagiaire = Stagiaire::with([
            'affectations.encadreur',
            'affectations.service',
            'evaluations',
        ])->findOrFail($request->user()->stagiaire_id);

        return response()->json($stagiaire);
    }

    // Fiche de l'encadreur connecté et les stagiaires qui lui sont attribués
    public function encadreur(Request $request)
    {
        $encadreur = Encadreur::with([
            'affectations.stagiaire.evaluations',
            'affectations.service',
        ])->findOrFail($request->user()->encadreur_id);

        return response()->json($encadreur);
    }
}

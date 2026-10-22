<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affectation;
use Illuminate\Http\Request;

class AffectationController extends Controller
{
    // Afficher toutes les affectations
    public function index()
    {
        $affectations = Affectation::with([
            'stagiaire',
            'encadreur',
            'service',
        ])->latest()->get();

        return response()->json($affectations);
    }

    // Créer une affectation
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'stagiaire_id' => [
                'required',
                'integer',
                'exists:stagiaires,id',
            ],
            'encadreur_id' => [
                'required',
                'integer',
                'exists:encadreurs,id',
            ],
            'service_id' => [
                'required',
                'integer',
                'exists:services,id',
            ],
            'date_affectation' => [
                'required',
                'date',
            ],
            'observations' => [
                'nullable',
                'string',
            ],
        ]);

        $affectation = Affectation::create($donnees);

        $affectation->load([
            'stagiaire',
            'encadreur',
            'service',
        ]);

        return response()->json([
            'message' => 'Affectation créée avec succès.',
            'data' => $affectation,
        ], 201);
    }

    // Afficher une affectation précise
    public function show(string $id)
    {
        $affectation = Affectation::with([
            'stagiaire',
            'encadreur',
            'service',
        ])->findOrFail($id);

        return response()->json($affectation);
    }

    // Modifier une affectation
    public function update(Request $request, string $id)
    {
        $affectation = Affectation::findOrFail($id);

        $donnees = $request->validate([
    'stagiaire_id' => 'required|exists:stagiaires,id',
    'encadreur_id' => 'required|exists:encadreurs,id',
    'service_id' => 'required|exists:services,id',
    'date_affectation' => 'required|date',
    'observations' => 'nullable|string',
]);

        $affectation->update($donnees);

        $affectation->load([
            'stagiaire',
            'encadreur',
            'service',
        ]);

        return response()->json([
            'message' => 'Affectation modifiée avec succès.',
            'data' => $affectation,
        ]);
    }

    // Supprimer une affectation
    public function destroy(string $id)
    {
        $affectation = Affectation::findOrFail($id);

        $affectation->delete();

        return response()->json([
            'message' => 'Affectation supprimée avec succès.',
        ]);
    }
}

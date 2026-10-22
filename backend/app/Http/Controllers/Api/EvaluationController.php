<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    // Afficher toutes les évaluations
    public function index()
    {
        $evaluations = Evaluation::with('stagiaire')
            ->latest()
            ->get();

        return response()->json($evaluations);
    }

    // Créer une nouvelle évaluation
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'stagiaire_id' => [
                'required',
                'integer',
                'exists:stagiaires,id',
            ],
            'note_technique' => [
                'required',
                'numeric',
                'between:0,20',
            ],
            'note_comportement' => [
                'required',
                'numeric',
                'between:0,20',
            ],
            'commentaire' => [
                'nullable',
                'string',
            ],
            'date_evaluation' => [
                'required',
                'date',
            ],
        ]);

        $evaluation = Evaluation::create($donnees);
        $evaluation->load('stagiaire');

        return response()->json([
            'message' => 'Évaluation créée avec succès.',
            'data' => $evaluation,
        ], 201);
    }

    // Afficher une évaluation précise
    public function show(string $id)
    {
        $evaluation = Evaluation::with('stagiaire')
            ->findOrFail($id);

        return response()->json($evaluation);
    }

    // Modifier une évaluation
    public function update(Request $request, string $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $donnees = $request->validate([
            'stagiaire_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:stagiaires,id',
            ],
            'note_technique' => [
                'sometimes',
                'required',
                'numeric',
                'between:0,20',
            ],
            'note_comportement' => [
                'sometimes',
                'required',
                'numeric',
                'between:0,20',
            ],
            'commentaire' => [
                'sometimes',
                'nullable',
                'string',
            ],
            'date_evaluation' => [
                'sometimes',
                'required',
                'date',
            ],
        ]);

        $evaluation->update($donnees);
        $evaluation->load('stagiaire');

        return response()->json([
            'message' => 'Évaluation modifiée avec succès.',
            'data' => $evaluation,
        ]);
    }

    // Supprimer une évaluation
    public function destroy(string $id)
    {
        $evaluation = Evaluation::findOrFail($id);

        $evaluation->delete();

        return response()->json([
            'message' => 'Évaluation supprimée avec succès.',
        ]);
    }
}

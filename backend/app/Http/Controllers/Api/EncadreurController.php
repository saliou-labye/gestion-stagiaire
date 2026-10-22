<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Encadreur;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\AccountService;

class EncadreurController extends Controller
{
    // Afficher tous les encadreurs
    public function index()
    {
        return response()->json(
            Encadreur::latest()->get()
        );
    }

    // Enregistrer un nouvel encadreur
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:encadreurs,email'],
            'specialite' => ['required', 'string', 'max:255'],
            // Mot de passe du compte de connexion de l'encadreur (optionnel)
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $encadreur = DB::transaction(function () use ($donnees) {
            $password = $donnees['password'] ?? null;
            unset($donnees['password']);

            $encadreur = Encadreur::create($donnees);
            AccountService::sync($encadreur, 'encadreur', $password);

            return $encadreur;
        });

        return response()->json([
            'message' => 'Encadreur créé avec succès.',
            'data' => $encadreur,
        ], 201);
    }

    // Afficher un encadreur précis
    public function show(string $id)
    {
        $encadreur = Encadreur::with('affectations.stagiaire')
            ->findOrFail($id);

        return response()->json($encadreur);
    }

    // Modifier un encadreur
    public function update(Request $request, string $id)
    {
        $encadreur = Encadreur::findOrFail($id);

        $donnees = $request->validate([
            'nom' => ['sometimes', 'required', 'string', 'max:100'],
            'prenom' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('encadreurs', 'email')->ignore($encadreur->id),
            ],
            'specialite' => ['sometimes', 'required', 'string', 'max:255'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($donnees, $encadreur) {
            $password = $donnees['password'] ?? null;
            unset($donnees['password']);

            $encadreur->update($donnees);
            AccountService::sync($encadreur->fresh(), 'encadreur', $password);
        });
        $encadreur = $encadreur->fresh();

        return response()->json([
            'message' => 'Encadreur modifié avec succès.',
            'data' => $encadreur,
        ]);
    }

    // Supprimer un encadreur
    public function destroy(string $id)
    {
        $encadreur = Encadreur::findOrFail($id);

        $encadreur->delete();

        return response()->json([
            'message' => 'Encadreur supprimé avec succès.',
        ]);
    }
}

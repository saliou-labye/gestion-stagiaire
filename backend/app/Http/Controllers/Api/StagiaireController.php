<?php
namespace App\Http\Controllers\Api;

use App\Models\Stagiaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Services\AccountService;
use App\Http\Controllers\Controller;



class StagiaireController extends Controller
{
    // Afficher la liste des stagiaires avec recherche et filtres
    public function index(Request $request)
    {
        $query = Stagiaire::query();

        if ($request->filled('recherche')) {
            $recherche = $request->recherche;

            $query->where(function ($q) use ($recherche) {
                $q->where('nom', 'ilike', "%{$recherche}%")
                  ->orWhere('prenom', 'ilike', "%{$recherche}%")
                  ->orWhere('email', 'ilike', "%{$recherche}%");
            });
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('service_id')) {
            $query->whereHas('affectations', function ($q) use ($request) {
                $q->where('service_id', $request->service_id);
            });
        }

        return response()->json($query->latest()->get());
    }

    // Afficher un stagiaire
    public function show(string $id)
    {
        $stagiaire = Stagiaire::with([
            'affectations.encadreur',
            'affectations.service',
            'evaluations',
        ])->findOrFail($id);

        return response()->json($stagiaire);
    }

    // Ajouter un stagiaire
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:stagiaires,email'],
            'telephone' => ['nullable', 'string', 'max:30'],
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'statut' => [
                'required',
                Rule::in(['en_attente', 'en_cours', 'termine']),
            ],
            // Mot de passe du compte de connexion du stagiaire (optionnel)
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $stagiaire = DB::transaction(function () use ($donnees) {
            $password = $donnees['password'] ?? null;
            unset($donnees['password']);

            $stagiaire = Stagiaire::create($donnees);
            AccountService::sync($stagiaire, 'stagiaire', $password);

            return $stagiaire;
        });

        return response()->json($stagiaire, 201);
    }

    // Modifier un stagiaire
    public function update(Request $request, string $id)
    {
        $stagiaire = Stagiaire::findOrFail($id);

        $donnees = $request->validate([
            'nom' => ['sometimes', 'required', 'string', 'max:100'],
            'prenom' => ['sometimes', 'required', 'string', 'max:100'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('stagiaires', 'email')->ignore($stagiaire->id),
            ],
            'telephone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'date_debut' => ['sometimes', 'required', 'date'],
            'date_fin' => ['sometimes', 'required', 'date', 'after_or_equal:date_debut'],
            'statut' => [
                'sometimes',
                'required',
                Rule::in(['en_attente', 'en_cours', 'termine']),
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
        ]);

        DB::transaction(function () use ($donnees, $stagiaire) {
            $password = $donnees['password'] ?? null;
            unset($donnees['password']);

            $stagiaire->update($donnees);
            AccountService::sync($stagiaire->fresh(), 'stagiaire', $password);
        });

        return response()->json($stagiaire->fresh());
    }

    // Supprimer un stagiaire
    public function destroy(string $id)
    {
        $stagiaire = Stagiaire::findOrFail($id);
        $stagiaire->delete();

        return response()->json([
            'message' => 'Stagiaire supprimé avec succès.',
        ]);
    }
}

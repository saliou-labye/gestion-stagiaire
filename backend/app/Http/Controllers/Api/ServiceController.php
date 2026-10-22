<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    // Afficher tous les services
    public function index()
    {
        return response()->json(
            Service::withCount('affectations')->latest()->get()
        );
    }

    // Créer un nouveau service
    public function store(Request $request)
    {
        $donnees = $request->validate([
            'nom' => ['required', 'string', 'max:255', 'unique:services,nom'],
            'description' => ['nullable', 'string'],
        ]);

        $service = Service::create($donnees);

        return response()->json([
            'message' => 'Service créé avec succès.',
            'data' => $service,
        ], 201);
    }

    // Afficher un service précis
    public function show(string $id)
    {
        $service = Service::with('affectations.stagiaire')
            ->withCount('affectations')
            ->findOrFail($id);

        return response()->json($service);
    }

    // Modifier un service
    public function update(Request $request, string $id)
    {
        $service = Service::findOrFail($id);

        $donnees = $request->validate([
            'nom' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('services', 'nom')->ignore($service->id),
            ],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $service->update($donnees);

        return response()->json([
            'message' => 'Service modifié avec succès.',
            'data' => $service,
        ]);
    }

    // Supprimer un service
    public function destroy(string $id)
    {
        $service = Service::findOrFail($id);

        $service->delete();

        return response()->json([
            'message' => 'Service supprimé avec succès.',
        ]);
    }
}

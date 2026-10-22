<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Crée ou met à jour le compte de connexion associé à une fiche stagiaire / encadreur.
 */
class AccountService
{
    /**
     * @param Model       $profil   Stagiaire ou Encadreur
     * @param string      $role     'stagiaire' ou 'encadreur'
     * @param string|null $password Mot de passe en clair (haché par le cast du modèle User)
     */
    public static function sync(Model $profil, string $role, ?string $password): void
    {
        $cle = $role . '_id';
        $user = User::where($cle, $profil->id)->first();

        // Pas de compte et pas de mot de passe fourni : rien à faire
        if (! $user && ! $password) {
            return;
        }

        $emailPris = User::where('email', $profil->email)
            ->when($user, fn ($q) => $q->where('id', '!=', $user->id))
            ->exists();

        if ($emailPris) {
            throw ValidationException::withMessages([
                'email' => ['Un compte de connexion existe déjà avec cette adresse email.'],
            ]);
        }

        $donnees = [
            'name' => trim($profil->prenom . ' ' . $profil->nom),
            'email' => $profil->email,
            'role' => $role,
            $cle => $profil->id,
        ];

        if ($password) {
            $donnees['password'] = $password;
        }

        $user ? $user->update($donnees) : User::create($donnees);
    }
}

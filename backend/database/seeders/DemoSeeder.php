<?php

namespace Database\Seeders;

use App\Models\Affectation;
use App\Models\Encadreur;
use App\Models\Evaluation;
use App\Models\Service;
use App\Models\Stagiaire;
use App\Services\AccountService;
use Illuminate\Database\Seeder;

/**
 * Données de démonstration (idempotent : relançable sans doublons).
 * Usage : php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $services = collect([
            ['Ressources Humaines', 'Recrutement, paie et gestion du personnel.'],
            ['Informatique', 'Développement, infrastructure et support.'],
            ['Comptabilité', 'Finances, facturation et contrôle de gestion.'],
            ['Marketing', 'Communication, marque et acquisition.'],
        ])->map(fn ($s) => Service::firstOrCreate(['nom' => $s[0]], ['description' => $s[1]]));

        $encadreurs = collect([
            ['Mensah', 'Koffi', 'koffi.mensah@demo.test', 'Gestion RH'],
            ['Diop', 'Amina', 'amina.diop@demo.test', 'Développement web'],
            ['Dimon', 'Benue', 'benue.dimon@demo.test', 'Réseaux & systèmes'],
            ['Agbo', 'Sena', 'sena.agbo@demo.test', 'Comptabilité générale'],
        ])->map(fn ($e) => Encadreur::firstOrCreate(
            ['email' => $e[2]],
            ['nom' => $e[0], 'prenom' => $e[1], 'specialite' => $e[3]]
        ));

        $stagiaires = collect([
            ['Diallo', 'Amadou', 'amadou.diallo@demo.test', '+228 90 00 00 01', '2026-07-01', '2026-08-15', 'en_attente'],
            ['Sylla', 'Fatima', 'fatima.sylla@demo.test', '+228 90 00 00 02', '2026-05-15', '2026-11-15', 'en_cours'],
            ['Amadou', 'Africoa', 'africoa.amadou@demo.test', '+228 90 00 00 03', '2026-06-01', '2026-08-15', 'termine'],
            ['Diallo', 'Fatima', 'fatima.diallo@demo.test', '+228 90 00 00 04', '2026-06-10', '2026-09-10', 'termine'],
            ['Agenka', 'Ezekiel', 'ezekiel.agenka@demo.test', '+228 90 00 00 05', '2026-09-01', '2026-12-20', 'en_cours'],
            ['Kpodar', 'Mawuli', 'mawuli.kpodar@demo.test', null, '2026-10-05', '2027-01-30', 'en_cours'],
        ])->map(fn ($s) => Stagiaire::firstOrCreate(
            ['email' => $s[2]],
            ['nom' => $s[0], 'prenom' => $s[1], 'telephone' => $s[3], 'date_debut' => $s[4], 'date_fin' => $s[5], 'statut' => $s[6]]
        ));

        // [index stagiaire, index encadreur, index service, date, observations]
        foreach ([
            [0, 0, 0, '2026-07-01', 'Découverte du service RH.'],
            [1, 1, 1, '2026-05-15', 'Projet de refonte du site interne.'],
            [2, 2, 1, '2026-06-01', 'Support et maintenance du parc.'],
            [3, 0, 0, '2026-06-10', null],
            [4, 1, 1, '2026-09-01', 'Développement API.'],
            [5, 3, 2, '2026-10-05', null],
        ] as [$s, $e, $v, $date, $obs]) {
            Affectation::firstOrCreate(
                ['stagiaire_id' => $stagiaires[$s]->id, 'encadreur_id' => $encadreurs[$e]->id, 'service_id' => $services[$v]->id],
                ['date_affectation' => $date, 'observations' => $obs]
            );
        }

        foreach ([
            [1, 16, 17.5, 'Très bonne progression technique.', '2026-08-01'],
            [2, 14, 16, 'Sérieux et autonome.', '2026-08-10'],
            [3, 18, 17, 'Excellent travail d\'équipe.', '2026-09-05'],
            [4, 15, 18, 'Très motivé.', '2026-10-01'],
        ] as [$s, $tech, $comp, $com, $date]) {
            Evaluation::firstOrCreate(
                ['stagiaire_id' => $stagiaires[$s]->id, 'date_evaluation' => $date],
                ['note_technique' => $tech, 'note_comportement' => $comp, 'commentaire' => $com]
            );
        }

        // Comptes de connexion de démonstration (mot de passe : Demo@12345)
        // Ne jamais utiliser ce mot de passe en production.
        foreach ([1, 2] as $i) {
            AccountService::sync($stagiaires[$i], 'stagiaire', 'Demo@12345');
        }
        foreach ([0, 1] as $i) {
            AccountService::sync($encadreurs[$i], 'encadreur', 'Demo@12345');
        }
    }
}

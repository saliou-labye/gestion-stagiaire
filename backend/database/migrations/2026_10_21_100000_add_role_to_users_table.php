<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Les comptes existants (administrateurs) gardent le rôle « admin »
            $table->string('role', 20)->default('admin')->after('password');

            // Lien optionnel entre un compte de connexion et sa fiche métier.
            // cascadeOnDelete : supprimer la fiche supprime aussi le compte associé.
            $table->foreignId('stagiaire_id')->nullable()->unique()
                ->constrained('stagiaires')->cascadeOnDelete();
            $table->foreignId('encadreur_id')->nullable()->unique()
                ->constrained('encadreurs')->cascadeOnDelete();
        });

        DB::statement("
            ALTER TABLE users
            ADD CONSTRAINT users_role_check
            CHECK (role IN ('admin', 'encadreur', 'stagiaire'))
        ");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check');

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('stagiaire_id');
            $table->dropConstrainedForeignId('encadreur_id');
            $table->dropColumn('role');
        });
    }
};

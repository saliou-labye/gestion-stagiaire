
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stagiaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('prenom', 100);
            $table->string('email')->unique();
            $table->string('telephone', 30)->nullable();
            $table->date('date_debut');
            $table->date('date_fin');
            $table->string('statut', 20)->default('en_attente');
            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE stagiaires
            ADD CONSTRAINT stagiaires_statut_check
            CHECK (statut IN ('en_attente', 'en_cours', 'termine'))
        ");

        DB::statement("
            ALTER TABLE stagiaires
            ADD CONSTRAINT stagiaires_dates_check
            CHECK (date_fin >= date_debut)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('stagiaires');
    }
};

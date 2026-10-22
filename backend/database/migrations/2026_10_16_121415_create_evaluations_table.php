
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stagiaire_id')
                ->constrained('stagiaires')
                ->cascadeOnDelete();

            $table->decimal('note_technique', 4, 2);
            $table->decimal('note_comportement', 4, 2);

            $table->text('commentaire')->nullable();
            $table->date('date_evaluation');

            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT evaluations_note_technique_check
            CHECK (note_technique BETWEEN 0 AND 20)
        ");

        DB::statement("
            ALTER TABLE evaluations
            ADD CONSTRAINT evaluations_note_comportement_check
            CHECK (note_comportement BETWEEN 0 AND 20)
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};

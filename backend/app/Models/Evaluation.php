<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stagiaire_id',
        'note_technique',
        'note_comportement',
        'commentaire',
        'date_evaluation',
    ];

    protected function casts(): array
    {
        return [
            'note_technique' => 'decimal:2',
            'note_comportement' => 'decimal:2',
            'date_evaluation' => 'date',
        ];
    }

    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(Stagiaire::class);
    }
}

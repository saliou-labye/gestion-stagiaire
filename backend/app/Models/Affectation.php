<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Affectation extends Model
{
    use HasFactory;

    protected $fillable = [
        'stagiaire_id',
        'encadreur_id',
        'service_id',
        'date_affectation',
        'observations',
    ];

    protected function casts(): array
    {
        return [
            'date_affectation' => 'date',
        ];
    }

    public function stagiaire(): BelongsTo
    {
        return $this->belongsTo(Stagiaire::class);
    }

    public function encadreur(): BelongsTo
    {
        return $this->belongsTo(Encadreur::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

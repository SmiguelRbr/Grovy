<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Measurement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'peso',
        'recorded_at', // Data do registo
        'waist_cm',    // Cintura
        'hips_cm',     // Quadril
        'chest_cm',    // Peitoral
        'photo_front_path',
        'photo_side_path',
        'photo_back_path',
        'notes',
        'images' 
    ];

 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
        ];
    }
}
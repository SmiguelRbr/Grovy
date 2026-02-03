<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDetail extends Model
{
    protected $fillable = [
        'nascimento',
        'genero',
        'altura',
        'peso',
        'objetivo'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

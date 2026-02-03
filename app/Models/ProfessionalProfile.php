<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalProfile extends Model
{
    protected $fillable = [
        'CRN/CREF',
        'bio',
        'aprovado',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}

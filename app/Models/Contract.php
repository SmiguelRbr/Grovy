<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = [
        'student_id',
        'professional_id',
        'status'
    ];

    // Relação com o Aluno
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    // Relação com o Profissional
    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
}

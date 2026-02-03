<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'professional_id',
        'student_id',
        'title',
        'type',
        'content',
        'description',
        'active',
        'expires_at'
    ];

    // O Laravel converte o JSON do banco para Array PHP automaticamente
    protected $casts = [
        'content' => 'array',
        'active' => 'boolean',
        'expires_at' => 'date'
    ];

    public function professional()
    {
        return $this->belongsTo(User::class, 'professional_id');
    }
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}

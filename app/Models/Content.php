<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'body',
        'image_path'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

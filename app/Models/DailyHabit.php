<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyHabit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date', 'water_ml', 'calories_kcal', 'sleep_hours'
    ];
}
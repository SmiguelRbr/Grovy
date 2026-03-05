<?php

namespace App\Http\Controllers;

use App\Models\Measurement; 
use Illuminate\Http\Request;

class PatientDetailController extends Controller
{
    public function store(Request $request)
    {
        
        // Ensure the birthdate is not in the future and that the user is at least 16 years old.
        // We compute the latest acceptable birthdate (today minus 16 years).
        $minBirthdate = now()->copy()->subYears(16)->toDateString();

        $data = $request->validate([
            'nascimento' => [
                'required',
                'date',
                'before_or_equal:today',             // not in the future
                "before_or_equal:$minBirthdate"    // at least 16 years old
            ],
            'genero' => 'required|string',
            'altura' => 'required|numeric',
            'peso' => 'required|numeric|max:500', 
            'objetivo' => 'required|string'
        ], [
            'before_or_equal' => 'Você deve ter no mínimo 16 anos',
            'max' => 'Peso inválido',
        ]);

        $user = $request->user();

  
        $detail = $user->patient_details()->updateOrCreate(
            ['user_id' => $user->id],
            $data
        );

        if ($user->measurements()->count() === 0) {
            Measurement::create([
                'user_id' => $user->id,
                'peso' => $data['peso'],
                'recorded_at' => now(), // Data de hoje
                'notes' => 'Peso inicial registrado no cadastro.'
            ]);
        }

        return response()->json(['message' => 'Perfil configurado com sucesso!', 'data' => $detail]);
    }
}

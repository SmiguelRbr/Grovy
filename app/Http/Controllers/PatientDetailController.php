<?php

namespace App\Http\Controllers;

use App\Models\Measurement; 
use Illuminate\Http\Request;

class PatientDetailController extends Controller
{
    public function store(Request $request)
    {
        
        // Ensure the birthdate is not in the future, at least 16 years old and at most 100 years old.
        $minBirthdate = now()->copy()->subYears(16)->toDateString();
        $maxBirthdate = now()->copy()->subYears(100)->toDateString();

        $data = $request->validate([
            'nascimento' => [
                'required',
                'date',
                'before_or_equal:today',             // not in the future
                "before_or_equal:$minBirthdate",    // at least 16 years old
                "after_or_equal:$maxBirthdate"      // no more than 100 years old
            ],
            'genero' => 'required|string',
            'altura' => 'required|numeric',
            'peso' => 'required|numeric|max:500', 
            'objetivo' => 'required|string'
        ], [
            'nascimento.before_or_equal' => 'Você deve ter entre 16 e 100 anos.',
            'nascimento.after_or_equal' => 'Você deve ter entre 16 e 100 anos.',
            'nascimento.date' => 'Data de nascimento inválida.',
            'nascimento.required' => 'A data de nascimento é obrigatória.',
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

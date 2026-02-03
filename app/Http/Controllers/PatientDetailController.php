<?php

namespace App\Http\Controllers;

use App\Models\Measurement; 
use Illuminate\Http\Request;

class PatientDetailController extends Controller
{
    public function store(Request $request)
    {
        
        $data = $request->validate([
            'nascimento' => 'required|date',
            'genero' => 'required|string',
            'altura' => 'required|numeric',
            'peso' => 'required|numeric', 
            'objetivo' => 'required|string'
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
                'images' => '[]', // JSON vazio ou null se preferir
                'notes' => 'Peso inicial registrado no cadastro.'
            ]);
        }

        return response()->json(['message' => 'Perfil configurado com sucesso!', 'data' => $detail]);
    }
}

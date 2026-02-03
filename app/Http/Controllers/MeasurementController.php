<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    public function index(Request $request)
    {
        $measurements = $request->user()->measurements()
            ->orderBy('recorded_at', 'desc')
            ->get();

        return response()->json($measurements);
    }

   
    public function store(Request $request)
    {
      
        $data = $request->validate([
            'peso' => 'required|numeric',
            'recorded_at' => 'required|date',
           
            'waist_cm' => 'nullable|numeric',
            'hips_cm' => 'nullable|numeric',
            'chest_cm' => 'nullable|numeric',
            'notes' => 'nullable|string',
          
            'photo_front' => 'nullable|image|max:5120', 
            'photo_side' => 'nullable|image|max:5120',
            'photo_back' => 'nullable|image|max:5120',
        ]);

        
        $photos = ['photo_front' => 'photo_front_path', 'photo_side' => 'photo_side_path', 'photo_back' => 'photo_back_path'];

        foreach ($photos as $inputName => $dbColumn) {
            if ($request->hasFile($inputName)) {
               
                $path = $request->file($inputName)->store('measurements', 'public');
                $data[$dbColumn] = $path;
            }
          
            unset($data[$inputName]);
        }

       
        $data['user_id'] = $request->user()->id;

        
        $measurement = Measurement::create($data);

        // --- PULO DO GATO ---
        // Atualiza também o "peso atual" na tabela patient_details
        // Assim o perfil do usuário fica sempre atualizado com a última pesagem
        $request->user()->patient_details()->update([
            'peso' => $data['peso']
        ]);

        return response()->json(['message' => 'Registrado com sucesso!', 'data' => $measurement], 201);
    }
}

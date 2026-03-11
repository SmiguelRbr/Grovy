<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use Illuminate\Http\Request;

class MeasurementController extends Controller
{
    public function index(Request $request)
    {
        $measurements = $request->user()->measurements()
            ->latest()
            ->limit(7)
            ->get();

        return response()->json($measurements);
    }

   
    public function store(Request $request)
    {
      
        $data = $request->validate([
            'peso' => 'required|numeric|max:500',
           
            'waist_cm' => 'nullable|numeric|max:500',
            'hips_cm' => 'nullable|numeric|max:500',
            'chest_cm' => 'nullable|numeric|max:500',
            'notes' => 'nullable|string|max:500',
          
            'photo_front' => 'nullable|image', 
            'photo_side' => 'nullable|image',
            'photo_back' => 'nullable|image',
        ], [
            'max' => 'O numero maximo de numero ou caracteres foi ultrapassado no campo :attribute'
        ]);

        $data['user_id'] = $request->user()->id;
        $data['recorded_at'] = now();
        
        $photos = ['photo_front' => 'photo_front_path', 'photo_side' => 'photo_side_path', 'photo_back' => 'photo_back_path'];

        foreach ($photos as $inputName => $dbColumn) {
            if ($request->hasFile($inputName)) {
               
                $path = $request->file($inputName)->store('measurements', 'public');
                $data[$dbColumn] = $path;
            }
          
            unset($data[$inputName]);
        }

       
        

        
        $measurement = Measurement::create($data);

        $request->user()->patient_details()->update([
            'peso' => $data['peso']
        ]);

        return response()->json(['message' => 'Registrado com sucesso!', 'data' => $measurement], 201);
    }
}

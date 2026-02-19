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
           
            'waist_cm' => 'nullable|numeric',
            'hips_cm' => 'nullable|numeric',
            'chest_cm' => 'nullable|numeric',
            'notes' => 'nullable|string',
          
            'photo_front' => 'nullable|image|max:5120', 
            'photo_side' => 'nullable|image|max:5120',
            'photo_back' => 'nullable|image|max:5120',
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

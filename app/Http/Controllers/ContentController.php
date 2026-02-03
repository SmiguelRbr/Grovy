<?php

// app/Http/Controllers/ContentController.php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'category' => 'required|string',
            'body' => 'required|string',
        ]);

        $content = $request->user()->contents()->create($data);

        return response()->json(['message' => 'Conteúdo publicado no seu perfil!', 'data' => $content]);
    }

    
    public function getByProfessional($professionalId)
    {
        $contents = Content::where('user_id', $professionalId)
            ->latest()
            ->get();
            
        return response()->json($contents);
    }
}
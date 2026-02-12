<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfessionalProfileController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'CRN/CREF' => 'required|string', // Ajuste o nome conforme seu banco
            'bio' => 'required|string|max:500',
        ]);

        $profile = $request->user()->professional_profile()->updateOrCreate(['user_id' => $request->user()->id], $data);

        return response()->json(['message' => 'Perfil profissional salvo! Aguarde aprovação.', 'data' => $profile]);
    }
}

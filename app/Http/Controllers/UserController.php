<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $validator = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|string|confirmed',
            'password_confirmation' => 'required|same:password',
            'profile_image' => 'nullable|image|mimes:png,jpg,jpeg'
        ], [
            'required' => 'O campo :attribute é obrigatorio',
            'email.unique' => 'Email já cadastrado',
            'email.email' => 'Coloque um formato de email válido',
            'confirmed' => 'Senhas não coicidem',
            'image' => 'Por favor coloque uma imagem',
            'mimes' => 'Este formato de imagem não é permitido',
        ]);

        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_image')->store('profiles', 'public');

            $data['profile_path'] = $path;
        }

        unset($data['profile_image']);
        unset($validator['password_confirmation']);


        User::create($validator);

        return response()->json([
            'Usuario criado com sucesso'
        ], 200);
    }

    public function login(Request $request)
    {
        $credenciais = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'required' => 'O campo :attribute é obrigatorio',
            'email.email' => 'Coloque um formato de email válido',
        ]);


        if (!Auth::attempt($credenciais)) {
            return response()->json([
                'message' => 'Credenciais inválidas'
            ], 403);
        }

        $token = $request->user()->createToken('Auth_Token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'message' => 'Usuario cadastrado com sucesso',
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();


        return response()->json([
            'message' => 'Logout realizado com sucesso.'
        ], 200);
    }

    public function indexProfessionals()
    {
        $profissionais = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['nutricionista', 'personal']);
        })
            ->whereHas('professional_profile', function ($query) {
                $query->where('aprovado', true);
            })

            ->with(['role', 'professional_profile'])
            ->get();

        return response()->json($profissionais);
    }

    public function indexPatients()
    {
        $pacientes = User::whereHas('role', function ($query) {
            $query->where('name', 'paciente');
        })
            ->with(['role', 'patient_details'])
            ->get();

        return response()->json($pacientes);
    }

    public function showProfessional($id)
    {
        $profissional = User::with(['role', 'professional_profile'])
            ->where('id', $id)
            // Garante que é um profissional
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['nutricionista', 'personal']);
            })
            ->firstOrFail();

        return response()->json($profissional);
    }

    public function showPatient($id)
    {
        $paciente = User::with(['role', 'patient_details', 'measurements'])
            ->where('id', $id)
            ->whereHas('role', function ($q) {
                $q->where('name', 'paciente');
            })
            ->firstOrFail();

        return response()->json($paciente);
    }
}

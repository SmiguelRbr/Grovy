<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PatientDashboardController extends Controller
{
    public function getEvolutionData(Request $request)
    {
        $user = $request->user();

        $historico = $user->measurements()
            ->select('recorded_at', 'peso')
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Se o usuário acabou de criar a conta e não tem nada (erro de sistema)
        if ($historico->isEmpty()) {
            return response()->json([
                'overview' => ['peso_atual' => 0, 'inicio' => 0, 'diferenca' => 0],
                'chart' => ['labels' => [], 'data' => []]
            ]);
        }

        $pesoInicial = $historico->first()->peso;
        $pesoAtual = $historico->last()->peso;

        // Se só tiver 1 registro (acabou de entrar), a diferença é 0
        $diferenca = $pesoAtual - $pesoInicial;

        return response()->json([
            'overview' => [
                'peso_atual' => $pesoAtual,
                'inicio' => $pesoInicial,
                'diferenca' => $diferenca,
                'registros_total' => $historico->count()
            ],
            'chart' => [
                'labels' => $historico->pluck('recorded_at')->map(fn($date) => date('d/m', strtotime($date))),
                'data' => $historico->pluck('peso'),
            ]
        ]);
    }


    public function getRecommendedProfessionals(Request $request)
    {
        // Regra: Trazer Nutris ou Personais APROVADOS e Aleatórios
        $recomendados = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['nutricionista', 'personal']);
        })
            ->whereHas('professional_profile', function ($q) {
                $q->where('aprovado', true);
            })
            // Trazemos os dados do perfil para mostrar a Foto e a Bio no card
            ->with(['professional_profile', 'role'])
            ->inRandomOrder() // Cada F5 mostra gente nova
            ->take(5) // Limite de 3 para não poluir a dashboard
            ->get();

        return response()->json($recomendados);
    }
}

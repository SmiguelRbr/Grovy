<?php

// app/Http/Controllers/PlanController.php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;

class PlanController extends Controller
{

    public function store(Request $request)
    {
        // 1. Validação básica dos dados recebidos
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'title' => 'required|string|max:100',
            'type' => 'required|in:dieta,treino,rotina',
            'description' => 'nullable|string',
            'content' => 'required|array',
            'expires_at' => 'nullable|date'
        ]);

        $professionalId = $request->user()->id;
        $studentId = $data['student_id'];

        // 2. Verificação pelo CONTRACT (O ponto chave)
        $hasActiveContract = Contract::where('professional_id', $professionalId)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->exists();

        if (!$hasActiveContract) {
            return response()->json([
                'error' => 'Vínculo inválido.',
                'message' => 'Você só pode enviar planos para alunos com contrato ativo.'
            ], 403);
        }

        // 3. Preparação e Criação
        $data['professional_id'] = $professionalId;

        $plan = Plan::create($data);

        return response()->json([
            'message' => 'Plano enviado com sucesso!',
            'plan' => $plan
        ], 201);
    }

    /**
     * Listar planos de um Aluno específico (Visão do Profissional)
     */
    public function indexByStudent(Request $request, $studentId)
    {
        $plans = Plan::where('student_id', $studentId)
            ->where('professional_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($plans);
    }

    public function showMyActivePlan(Request $request)
    {
        $plan = Plan::where('student_id', $request->user()->id)
            ->where('active', true)
            ->with('professional') // Para saber quem mandou
            ->latest()
            ->first();

        if (!$plan) {
            return response()->json(['message' => 'Você ainda não tem um plano ativo.'], 404);
        }

        return response()->json($plan);
    }

    // Rota: GET /my-plans-history (Mostra o histórico de tudo que já teve)
    public function indexMyPlans(Request $request)
    {
        $plans = Plan::where('student_id', $request->user()->id)
            ->with('professional')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($plans);
    }
}

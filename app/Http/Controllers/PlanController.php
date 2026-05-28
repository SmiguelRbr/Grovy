<?php

// app/Http/Controllers/PlanController.php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai as LaravelAi; // Importamos a classe principal do Laravel AI SDK

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

        // === TRAVA DE SEGURANÇA INTEGRADA ===
        $user = $request->user();
        // Carrega o nome da role através da relação (ajusta se no teu model for diferente)
        $roleName = $user->role->name;

        if ($roleName === 'personal' && $data['type'] === 'dieta') {
            return response()->json([
                'error' => 'Acesso proibido.',
                'message' => 'Um Personal Trainer não pode prescrever planos de dieta.'
            ], 403);
        }

        if ($roleName === 'nutricionista' && $data['type'] === 'treino') {
            return response()->json([
                'error' => 'Acesso proibido.',
                'message' => 'Um Nutricionista não pode prescrever planos de treino.'
            ], 403);
        }
        // ===================================

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

    /**
     * Rejeitar um plano (Visão do Aluno)
     */
    public function reject(Request $request, $planId)
    {
        $plan = Plan::where('id', $planId)
            ->where('student_id', $request->user()->id)
            ->first();

        if (!$plan) {
            return response()->json(['error' => 'Plano não encontrado ou você não tem permissão para rejeitá-lo.'], 404);
        }

        if (!$plan->active) {
            return response()->json(['error' => 'Este plano já foi rejeitado ou não está ativo.'], 400);
        }

        $plan->update(['active' => false]);

        return response()->json(['message' => 'Plano rejeitado com sucesso!']);
    }

    /**
     * Copiloto de Prescrição via IA (Gemini) usando Agents
     */
    public function generatePlanWithAI(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
            'type' => 'required|in:dieta,treino,rotina'
        ]);

        $promptProfissional = $request->input('prompt');
        $tipo = $request->input('type');

        $systemPrompt = "És um assistente especializado em criar planos de {$tipo}. "
            . "REGRA ABSOLUTA: A tua resposta deve ser ÚNICA e EXCLUSIVAMENTE um array JSON válido. "
            . "NÃO uses blocos de código markdown (```json). NÃO uses aspas duplas dentro dos valores de texto (usa aspas simples se precisares). "
            . "NÃO deixes vírgulas sobrando no final do array.\n"
            . "Estrutura OBRIGATÓRIA (sempre estas chaves exatas):\n"
            . "[{\"refeicao\": \"Treino A (Peito/Tríceps)\", \"alimentos\": \"Supino Reto 4x10, Crucifixo 3x12\"}]\n"
            . "Se a instrução do utilizador não fizer sentido, gera um plano genérico de '{$tipo}'.";

        try {
            // --- A MARRETA DA FORÇA BRUTA ---
            // 1. Injetamos o Gemini como padrão na memória do Laravel na hora H!
            \Illuminate\Support\Facades\Config::set('ai.default', 'gemini');

            $agent = new class($systemPrompt) implements \Laravel\Ai\Contracts\Agent {
                use \Laravel\Ai\Promptable;

                // 2. Obrigamos o próprio Agente a assumir que o provedor é o Gemini!
                public string $provider = 'gemini';

                public function __construct(private string $regras) {}

                public function instructions(): string
                {
                    return $this->regras;
                }
            };

            // Disparamos a IA com o texto
            $resposta = retry(3, function () use ($agent, $promptProfissional) {
                return $agent->prompt($promptProfissional);
            }, 2000);

            $textoCru = (string) $resposta;

            // Limpeza do markdown
            $textoLimpo = str_replace(['```json', '```'], '', $textoCru);
            $textoLimpo = trim($textoLimpo);

            $inicio = strpos($textoLimpo, '[');
            $fim = strrpos($textoLimpo, ']');

            if ($inicio === false || $fim === false) {
                throw new \Exception('O modelo não gerou um array identificável.');
            }

            $jsonString = substr($textoLimpo, $inicio, $fim - $inicio + 1);
            $conteudoGerado = json_decode($jsonString, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($conteudoGerado)) {
                throw new \Exception('O JSON extraído é inválido: ' . json_last_error_msg());
            }

            return response()->json([
                'success' => true,
                'content' => $conteudoGerado
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erro no Copiloto IA (Agent): ' . $e->getMessage() . ' | Texto da IA: ' . ($textoCru ?? 'Nenhum'));

            return response()->json([
                'error' => 'A Inteligência Artificial encontrou uma falha de formatação. Tenta de novo com outras palavras.'
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Ai\Ai as LaravelAi;


class PatientDashboardController extends Controller
{
    public function getEvolutionData(Request $request)
    {
        $user = $request->user();

        // Procura o histórico de pesos do aluno do mais antigo para o mais recente
        $historico = $user->measurements()
            ->select('recorded_at', 'peso')
            ->orderBy('recorded_at', 'asc')
            ->get();

        // Se o utilizador não tiver nenhuma medição registada
        if ($historico->isEmpty()) {
            return response()->json([
                'overview' => [
                    'peso_atual' => 0, 
                    'inicio' => 0, 
                    'diferenca' => 0,
                    'insight_ia' => 'Bem-vindo ao Grovy! Regista a tua primeira avaliação física para a IA começar a analisar o teu progresso.'
                ],
                'chart' => ['labels' => [], 'data' => []]
            ]);
        }

        $pesoInicial = $historico->first()->peso;
        $pesoAtual = $historico->last()->peso;
        $diferenca = $pesoAtual - $pesoInicial;

        // --- INTEGRAÇÃO COM GEMINI VIA LARAVEL AI ---
        $insightIa = 'Continua focado no teu plano de treinos e alimentação!'; // Frase padrão caso a API falhe
        
        try {
            // Extraímos apenas os valores de peso num array numérico (ex: [85, 84.2, 83.5])
            $listaPesos = $historico->pluck('peso')->toArray();
            $objetivo = $user->patient_details->objetivo ?? 'melhorar a condição física';

            $prompt = "Atua como um treinador pessoal e nutricionista de alta performance. O teu aluno tem o objetivo de '{$objetivo}'. "
                    . "O histórico recente de peso dele foi: " . implode('kg -> ', $listaPesos) . "kg. "
                    . "Gera uma frase curta (máximo 2 linhas), motivacional e muito direta, analisando se ele está no caminho certo. "
                    . "Trata o aluno por 'tu', sê empático e dinâmico.";

            // SINTAXE ATUALIZADA DO LARAVEL AI SDK
            // Passamos o texto e chamamos o generate()
            $resposta = LaravelAi::text($prompt)->generate();
            
            // Retiramos o conteúdo da resposta
            $insightIa = $resposta->text(); 
            
        } catch (\Throwable $e) {
            // Usando \Throwable, se a IA falhar (falta de net, pacote errado, etc), 
            // o teu TCC NUNCA quebra. Ele simplesmente mostra a frase padrão!
            Log::error('Erro na IA do Dashboard: ' . $e->getMessage());
        }
        // --------------------------------------------

        return response()->json([
            'overview' => [
                'peso_atual' => $pesoAtual,
                'inicio' => $pesoInicial,
                'diferenca' => $diferenca,
                'registros_total' => $historico->count(),
                'insight_ia' => $insightIa // Enviamos o conselho da IA para o ecrã do aluno
            ],
            'chart' => [
                'labels' => $historico->pluck('recorded_at')->map(fn($date) => date('d/m', strtotime($date))),
                'data' => $historico->pluck('peso'),
            ]
        ]);
    }

    public function getRecommendedProfessionals(Request $request)
    {
        $recomendados = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['nutricionista', 'personal']);
        })
        ->whereHas('professional_profile', function ($q) {
            $q->where('aprovado', true);
        })
        ->with(['professional_profile', 'role'])
        ->inRandomOrder()
        ->take(5)
        ->get();

        return response()->json($recomendados);
    }
}
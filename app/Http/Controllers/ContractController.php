<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use Illuminate\Http\Request;

class ContractController extends Controller
{
    // Aluno solicita contratação (Envia o ID do profissional)
    public function store(Request $request)
    {
        $data = $request->validate([
            'professional_id' => 'required|exists:users,id'
        ]);

        $studentId = $request->user()->id;

        // Verifica se já existe solicitação pendente ou ativa para evitar duplicidade
        $exists = Contract::where('student_id', $studentId)
            ->where('professional_id', $data['professional_id'])
            ->whereIn('status', ['pending', 'active'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Você já possui um vínculo ou solicitação pendente com este profissional.'], 409);
        }

        Contract::create([
            'student_id' => $studentId,
            'professional_id' => $data['professional_id'],
            'status' => 'pending'
        ]);

        return response()->json(['message' => 'Solicitação enviada com sucesso! Aguarde o aceite.'], 201);
    }

    // Profissional vê lista de solicitações pendentes (GET)
    public function indexRequests(Request $request)
    {
        $requests = Contract::where('professional_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('student.patient_details') // Traz dados do aluno para o Nutri decidir
            ->get();

        return response()->json($requests);
    }

    // Profissional Aceita o contrato
    public function accept(Request $request, $id)
    {
        $contract = Contract::where('id', $id)
            ->where('professional_id', $request->user()->id) // Segurança: só o dono aceita
            ->firstOrFail();

        $contract->update(['status' => 'active']);

        return response()->json(['message' => 'Aluno aceito! Agora você pode criar planos para ele.']);
    }

    // Profissional Recusa o contrato
    public function reject(Request $request, $id)
    {
        $contract = Contract::where('id', $id)
            ->where('professional_id', $request->user()->id)
            ->firstOrFail();

        // Podemos deletar ou marcar como rejected. Marcar mantém histórico.
        $contract->update(['status' => 'rejected']);

        return response()->json(['message' => 'Solicitação recusada.']);
    }
}

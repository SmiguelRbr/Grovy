<?php

namespace App\Http\Controllers;

use App\Models\DailyHabit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DailyHabitController extends Controller
{
    /**
     * Retorna os hábitos do dia atual para preencher o Dashboard
     */
    public function getToday(Request $request)
    {
        $hoje = Carbon::today()->toDateString();
        $user = $request->user();
        
        // firstOrCreate: Se não existir um registo para hoje, cria um a zeros.
        $habit = DailyHabit::firstOrCreate(
            ['user_id' => $user->id, 'date' => $hoje],
            ['water_ml' => 0, 'calories_kcal' => 0, 'sleep_hours' => 0]
        );

        return response()->json($habit);
    }

    /**
     * Atualiza os hábitos diários (Quick Check-in)
     */
    public function quickSave(Request $request)
    {
        // Valida apenas os campos que chegarem
        $data = $request->validate([
            'water_ml' => 'nullable|integer|min:0',
            'calories_kcal' => 'nullable|integer|min:0',
            'sleep_hours' => 'nullable|numeric|min:0|max:24',
        ]);

        $hoje = Carbon::today()->toDateString();
        $user = $request->user();

        // Garante que temos o registo de hoje
        $habit = DailyHabit::firstOrCreate(
            ['user_id' => $user->id, 'date' => $hoje],
            ['water_ml' => 0, 'calories_kcal' => 0, 'sleep_hours' => 0]
        );

        // Atualiza apenas os campos que foram enviados na requisição
        if ($request->has('water_ml')) {
            $habit->water_ml = $data['water_ml'];
        }
        if ($request->has('calories_kcal')) {
            $habit->calories_kcal = $data['calories_kcal'];
        }
        if ($request->has('sleep_hours')) {
            $habit->sleep_hours = $data['sleep_hours'];
        }
        
        $habit->save();

        return response()->json([
            'message' => 'Hábitos atualizados com sucesso!', 
            'data' => $habit
        ]);
    }
}
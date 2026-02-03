<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientDetailController;
use App\Http\Controllers\ProfessionalProfileController;
use App\Http\Controllers\PatientDashboardController;
use App\Http\Controllers\MeasurementController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\ContentController;
use App\Http\Middleware\CheckRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- ROTAS PÚBLICAS (GUEST) ---
// Acesso liberado para quem não está logado
Route::middleware('guest')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
});


// --- ROTAS PROTEGIDAS (AUTH) ---
// Acesso apenas com Token válido
Route::middleware('auth:sanctum')->group(function () {

    // Logout do Sistema
    Route::post('/logout', [UserController::class, 'logout']);

    // Cadastro de Detalhes do Perfil (Onboarding)
    Route::post('/perfil/paciente', [PatientDetailController::class, 'store']);
    Route::post('/perfil/profissional', [ProfessionalProfileController::class, 'store']);

    // Marketplace e Visualização de Profissionais
    // Disponível para todos os usuários logados
    Route::get('/profissionais', [UserController::class, 'indexProfessionals']);
    Route::get('/profissionais/{id}', [UserController::class, 'showProfessional']);
    Route::get('/profissionais/{id}/contents', [ContentController::class, 'getByProfessional']); // Conteúdos/Dicas do perfil


    // --- ÁREA DO PACIENTE ---
    // Apenas usuários com role 'paciente' acessam
    Route::middleware([CheckRole::class . ':paciente'])->group(function () {

        // Dashboard e Recomendações
        Route::prefix('dashboard')->group(function () {
            Route::get('/evolution', [PatientDashboardController::class, 'getEvolutionData']);
            Route::get('/recommendations', [PatientDashboardController::class, 'getRecommendedProfessionals']);
        });

        // Gestão de Medidas (Peso e Fotos)
        Route::get('/measurements', [MeasurementController::class, 'index']);
        Route::post('/measurements', [MeasurementController::class, 'store']);

        // Contratos (Solicitar acompanhamento)
        Route::post('/contracts', [ContractController::class, 'store']);

        // Visualizar Plano Ativo (Dieta/Treino recebido)
        Route::get('/my-plan', [PlanController::class, 'showMyActivePlan']);
    });


    // --- ÁREA DO PROFISSIONAL ---
    // Apenas nutricionistas e personais acessam
    Route::middleware([CheckRole::class . ':nutricionista,personal'])->group(function () {

        // Gestão de Contratos (Solicitações pendentes)
        Route::get('/contracts/requests', [ContractController::class, 'indexRequests']);
        Route::patch('/contracts/{id}/accept', [ContractController::class, 'accept']);
        Route::patch('/contracts/{id}/reject', [ContractController::class, 'reject']);

        // Carteira de Clientes (Apenas alunos com contrato Ativo)
        Route::get('/pacientes', [UserController::class, 'indexPatients']);

        // Detalhes do Aluno Específico (Para análise)
        Route::get('/pacientes/{id}', [UserController::class, 'showPatient']);
        Route::get('/pacientes/{id}/plans', [PlanController::class, 'indexByStudent']); // Histórico de planos deste aluno

        // Criação de Planos e Conteúdos
        Route::post('/plans', [PlanController::class, 'store']); // Enviar dieta/treino
        Route::post('/contents', [ContentController::class, 'store']); // Publicar dica no perfil
    });
});

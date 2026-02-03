<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if(!$user || $user->role->name === null){
            return response()->json([
                'error' => 'Você ainda não terminou de se cadastrar',
            ], 403);
        }

        if (!$user || $user->role->name !== $role) {
            return response()->json([
                'error' => 'Você não tem permissão para acessar esta rota'
            ], 401);
        }
        return $next($request);
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseService extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Resposta de Sucesso
         * Uso: Response::success($data, $message, $code)
         */
        Response::macro('success', function ($data = [], $message = 'Operação realizada com sucesso', $code = 200) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $data
            ], $code);
        });

        /**
         * Resposta de Erro
         * Uso: Response::error($message, $code)
         */
        Response::macro('error', function ($message = 'Erro na operação', $code = 400) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'message' => $message
                ]
            ], $code);
        });

    }
}

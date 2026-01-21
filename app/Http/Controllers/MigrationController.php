<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MigrationController extends Controller
{
    public function runMigrations(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators are authorized to run migrations',
                ], 403);
            }
            
            Artisan::call('migrate', ['--force' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Migrations completed successfully',
                'output'  => Artisan::output(),
            ]);
        } catch (Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Migration failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function checkMigrationStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user || $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators are authorized to check migration status',
                ], 403);
            }
            Artisan::call('migrate:status');
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'status'  => $output,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

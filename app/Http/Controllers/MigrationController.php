<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class MigrationController extends Controller
{
    public function runMigrations(Request $request)
    {
        try {
            Artisan::call('migrate', ['--force' => true]);
            
            return response()->json([
                'success' => true,
                'message' => 'Migrations completed successfully',
                'output' => Artisan::output()
            ]);
        } catch (\Exception $e) {
            Log::error('Migration failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Migration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkMigrationStatus()
    {
        try {
            Artisan::call('migrate:status');
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'status' => $output
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

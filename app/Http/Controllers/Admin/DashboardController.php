<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $userCount     = User::where('role', 'user')->count();
            $propertyCount = Property::count();
            $ownerCount    = User::where('role', 'owner')->count();
            $response      = ['status' => true, 'message' => '', 'data' => compact('userCount', 'propertyCount', 'ownerCount')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}

<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\Property;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $propertyId = Property::where('ownerId', Auth::id())->pluck('id')->toArray();
            $totalBookingCount = Bookings::whereIn('propertyId', $propertyId)->count();
            $response = ['status' => true, 'message' => '', 'data' => compact('totalBookingCount')];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function ownerDetails(): JsonResponse
    {
        try {
            $owner = User::where('id', Auth::id())->first();
            $response = ['status' => true, 'message' => '', 'data' => $owner];

            return response()->json($response);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}

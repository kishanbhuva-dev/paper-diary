<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $propertyId        = Property::where('ownerId', Auth::id())->pluck('id')->toArray();
            $totalBookingCount = Booking::whereIn('propertyId', $propertyId)->count();
            $response          = ['status' => true, 'message' => '', 'data' => compact('totalBookingCount')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}

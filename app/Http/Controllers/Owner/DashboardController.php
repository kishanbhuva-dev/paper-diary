<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Property;
use App\Models\Booking;

class DashboardController extends Controller
{
    public function index(){
        try {
            $propertyId = Property::where('ownerId',Auth::id())->pluck('id')->toArray();
            $totalBookingCount = Booking::whereIn('propertyId',$propertyId)->count();
            $response = ['status'=>true,'message'=>'','data'=>compact('totalBookingCount')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>[]]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;

class PropertyController extends Controller
{
    public function index(Request $request){
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            
            $property = Property::selectRaw('id,ownerId,propertyName,status')
                ->with(['owner'=>function($query){
                    $query->select('id','firstName','lastName','email','phone','telephone');
                },'bookings'=>function($query){
                    $query->selectRaw('propertyId, (SELECT SUM(price) FROM `booking_orders` WHERE status="confirm") AS totalRevenue,(SELECT SUM(price) FROM `booking_orders` WHERE status !="confirm") as lostAmount')->groupBy('propertyId');
                }]);
            
            if($search) {
                $property->whereHas('owner', function($query) use ($search) {
                    $query->where('firstName', 'LIKE', "%{$search}%")
                          ->orWhere('lastName', 'LIKE', "%{$search}%")
                          ->orWhere('email', 'LIKE', "%{$search}%")
                          ->orWhere('phone', 'LIKE', "%{$search}%")
                          ->orWhere('telephone', 'LIKE', "%{$search}%");
                })->orWhere('propertyName', 'LIKE', "%{$search}%");
            }
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'asc';
            $property->orderBy($sortBy, $sortOrder);
            $properties = $property->paginate($perPage);
            $response = ['status'=>true , 'message'=>'','data'=>$properties];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false , 'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Validator;

class PropertyController extends Controller
{
    public function index(Request $request){
        try {
            $search = $request->search;
            $perPage = $request->perPage ?? 10;
            
            $property = Property::selectRaw('id,ownerId,propertyName,status, 
                (SELECT COALESCE(SUM(price), 0) FROM `booking_orders` WHERE propertyId = property.id AND status = "confirm") AS totalRevenue,
                (SELECT COALESCE(SUM(price), 0) FROM `booking_orders` WHERE propertyId = property.id AND status != "confirm") AS lostAmount')
                ->with(['owner'=>function($query){
                    $query->select('id','firstName','lastName','email','phone','telephone');
                }]);
            
            if($search) {
                $property->where(function($query) use ($search) {
                    $query->whereHas('owner', function($ownerQuery) use ($search) {
                        $ownerQuery->where('firstName', 'LIKE', "%{$search}%")
                              ->orWhere('lastName', 'LIKE', "%{$search}%")
                              ->orWhere('email', 'LIKE', "%{$search}%")
                              ->orWhere('phone', 'LIKE', "%{$search}%")
                              ->orWhere('telephone', 'LIKE', "%{$search}%");
                    })->orWhere('propertyName', 'LIKE', "%{$search}%")
                          ->orWhereRaw('(SELECT COALESCE(SUM(price), 0) FROM `booking_orders` WHERE propertyId = property.id AND status = "confirm") LIKE ?', ["%{$search}%"])
                          ->orWhereRaw('(SELECT COALESCE(SUM(price), 0) FROM `booking_orders` WHERE propertyId = property.id AND status != "confirm") LIKE ?', ["%{$search}%"]);
                });
            }
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'asc';
            
            if ($sortBy === 'ownerName') {
                $property->join('users', 'property.ownerId', '=', 'users.id')
                        ->orderByRaw("CONCAT(users.firstName, ' ', users.lastName) {$sortOrder}")
                        ->select('property.*');
            } elseif ($sortBy === 'ownerEmail') {
                $property->join('users', 'property.ownerId', '=', 'users.id')
                        ->orderBy('users.email', $sortOrder)
                        ->select('property.*');
            } elseif ($sortBy === 'ownerPhone') {
                $property->join('users', 'property.ownerId', '=', 'users.id')
                        ->orderBy('users.phone', $sortOrder)
                        ->select('property.*');
            } elseif ($sortBy === 'ownerTelephone') {
                $property->join('users', 'property.ownerId', '=', 'users.id')
                        ->orderBy('users.telephone', $sortOrder)
                        ->select('property.*');
            } else {
                $property->orderBy($sortBy, $sortOrder);
            }
            $property = $property->paginate($perPage)->through(function ($property) {
                return [
                    'id' => $property->id,
                    'ownerId' => $property->ownerId,
                    'propertyName' => $property->propertyName,
                    'status' => $property->status,
                    'totalRevenue' => $property->totalRevenue,
                    'lostAmount' => $property->lostAmount,
                    'ownerName' => $property->owner->firstName . ' ' . $property->owner->lastName,
                    'ownerEmail' => $property->owner->email,
                    'ownerPhone' => $property->owner->phone,
                    'ownerTelephone' => $property->owner->telephone
                ];
            });
            $response = ['status'=>true , 'message'=>'','data'=>$property];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false , 'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
    public function update(Request $request,$id){
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|exists:property,id',
                'status' => 'required|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $property = Property::where('id', $id)->first();
            $property->status = $request->status;
            $property->save();
            return response()->json(['status' => true, 'message' => 'Property status changed successfully', 'data' => $property]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;

class PropertyController extends Controller
{
    public function index(){
        try {
            $property = Property::selectRaw('ownerId,propertyName,status')->with('owner:id,firstName,lastName,email')->get();
            $response = ['status'=>true , 'message'=>'','data'=>$property];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false , 'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
}

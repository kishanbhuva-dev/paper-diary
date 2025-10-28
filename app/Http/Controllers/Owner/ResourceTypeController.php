<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ResourceType;

class ResourceTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $resourceTypes = ResourceType::where('propertyId',$request->id)->get();
            $response = ['status'=>true,'message'=>'','data'=>$resourceTypes];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false,'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
    public function destroy(string $id)
    {
        try {
            $resourceType = ResourceType::where('id',$id)->first();
            if(!empty($resourceType)){
                if($resourceType->delete()){
                    $response = ['status'=>true,'message'=>'Resource Type deleted successfully','data'=>[]];
                }else{
                    $response = ['status'=>false,'message'=>'Resource Type not deleted','data'=>[]];
                }
            }else{
                $response = ['status'=>false,'message'=>'Resource Type not found','data'=>[]];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false,'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
}

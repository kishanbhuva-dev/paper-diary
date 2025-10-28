<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resource;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $resources = Resource::where('resourceTypeId',$request->id)->get();
            $response = ['status'=>true,'message'=>'','data'=>$resources];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false,'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
    public function destroy(string $id)
    {
        try {
            $resources = Resource::where('id',$id)->first();
            if(!empty($resources)){
                if($resources->delete()){
                    $response = ['status'=>true,'message'=>'Resource deleted successfully','data'=>[]];
                }else{
                    $response = ['status'=>false,'message'=>'Resource not deleted','data'=>[]];
                }
            }else{
                $response = ['status'=>false,'message'=>'Resource not found','data'=>[]];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status'=>false,'message'=>$th->getMessage(),'data'=>[]];
            return response()->json($response);
        }
    }
}

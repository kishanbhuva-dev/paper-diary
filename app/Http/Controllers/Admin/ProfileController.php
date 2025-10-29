<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProfileController extends Controller
{
    public function index()
    {
        try {
            $admin = User::where('id',Auth::id())->first();
            if(!empty($admin)){
                $response = ['status'=>true,'message'=>'','data'=>$admin];
            }else{
                $response = ['status'=>false,'message'=>'Admin not found','data'=>''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
           return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>'']);
        }
    }
    public function update(Request $request){
        try {
            $admin = User::where('id',Auth::id())->first();
            if (!empty($admin)) {
                $admin->firstName = $request->firstName;
                $admin->lastName = $request->lastName;
                if ($admin->save()) {
                    $response = ['status'=>true,'message'=>'Profile updated successfully','data'=>''];
                }else{
                    $response = ['status'=>false,'message'=>'Profile not updated please try again','data'=>''];
                }
            }else{
                $response = ['status'=>false,'message'=>'Admin not found','data'=>''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>'']);
        }
    }
}

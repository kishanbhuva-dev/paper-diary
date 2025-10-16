<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(),'data' => null]);
            }

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['status' => false, 'message' => 'The provided credentials are incorrect.']);
            }

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful',
                'data' => compact('user', 'token'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName'  => 'required|string|max:255',
                'email'     => 'required|email|unique:users,email',
                'password'             => 'required|min:8',
                'confirm_password'     => 'required_with:password|same:password|min:8',
                'role'      => 'nullable|in:user,owner',
                'address'   => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(),'data' => null]);
            }

            $user = new User;
            $user->firstName = $request->firstName;
            $user->lastName  = $request->lastName;
            $user->email     = $request->email;
            $user->password  = Hash::make($request->password);
            $user->role      = $request->role ?? 'user';
            $user->address   = $request->address;
            $user->address2  = $request->address2;
            $user->city      = $request->city;
            $user->country   = $request->country;
            $user->postcode  = $request->postcode;
            $user->phone     = $request->phone;
            $user->telephone = $request->telephone;
            if($user->save()){
                if ($user->role === 'owner') {
                    $validator = Validator::make($request->all(), [
                        'propertyName' => 'required',
                        'propertyAddress' => 'required',
                    ]);

                    if ($validator->fails()) {
                        return response()->json(['status' => false, 'message' => $validator->errors(),'data' => null]);
                    }
                    $this->createProperty($request, $user->id);
                }
                $token = $user->createToken('api-token')->plainTextToken;
                $response = ['status'=>true,'message'=>'User registered successfully','data'=>compact('user','token')];
            }else{
                $response = ['status'=>false,'message'=>'User registration failed','data'=>null];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status'=>false,'message'=>$th->getMessage(),'data'=>null]);
        }
        
    }

    private function createProperty($request, $userId)
    {
        $property = new Property();
        $property->userId = $userId;
        $property->title = $request->title;
        $property->propertyName = $request->propertyName;
        $property->email = $request->email;
        $property->address = $request->propertyAddress;
        $property->address2 = $request->propertyAddress2;
        $property->description = $request->description;
        $property->status = 0;
        $property->latitude = 0;
        $property->longitude = 0;
        $property->save();
    }
}

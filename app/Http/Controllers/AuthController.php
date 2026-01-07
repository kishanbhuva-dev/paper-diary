<?php
namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }

            $user = User::where('email', $request->email)->first();

            if (! $user || ! Hash::check($request->password, $user->password)) {
                return response()->json(['status' => false, 'message' => 'The provided credentials are incorrect.']);
            }
            $subscription=true;
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status'  => true,
                'message' => 'Login successful',
                'data'    => compact('user', 'token','subscription'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstName'        => 'required|string|max:255',
                'lastName'         => 'required|string|max:255',
                'email'            => 'required|email|unique:users,email',
                'password'         => 'required|min:8',
                'confirm_password' => 'required_with:password|same:password|min:8',
                'role'             => 'nullable|in:user,owner',
                'address'          => 'required|string',
                'address2'=>'nullable|string',
                'city'=>'required|string',
                'country'=>'required|string',
                'postcode'=>'required|string',
                'telephone'=>'required|string',
                'phone'=>'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $user            = new User;
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
            if ($user->save()) {
                $token    = $user->createToken('api-token')->plainTextToken;
                $response = ['status' => true, 'message' => 'User registered successfully', 'data' => compact('user', 'token')];
            } else {
                $response = ['status' => false, 'message' => 'User registration failed', 'data' => null];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => null]);
        }
    }

    public function profileUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'firstName' => 'required|string|max:255',
                'lastName'  => 'required|string|max:255',
                'phone'     => 'required|string|max:255',
                'address'   => 'required|string',
                'address2'  => 'nullable|string',
                'city'      => 'nullable|string|max:255',
                'country'   => 'nullable|string|max:255',
                'postcode'  => 'nullable|string|max:255',
                'telephone' => 'nullable|string|max:255',
                'stripePublicKey' => 'nullable|string|max:255',
                'stripeSecretKey' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $user = User::where('id', Auth::id())->first();
            if (! empty($user)) {
                $user->firstName = $request->firstName;
                $user->lastName  = $request->lastName;
                $user->phone     = $request->phone;
                $user->address   = $request->address;
                $user->address2  = $request->address2;
                $user->city      = $request->city;
                $user->country   = $request->country;
                $user->postcode  = $request->postcode;
                $user->telephone = $request->telephone;
                if ($user->role=='owner') {
                    $user->stripePublicKey = $request->stripePublicKey;
                    $user->stripeSecretKey = $request->stripeSecretKey;
                }
                if ($user->save()) {
                    $response = ['status' => true, 'message' => 'Profile updated successfully', 'data' => ''];
                } else {
                    $response = ['status' => false, 'message' => 'Profile not updated please try again', 'data' => ''];
                }
            } else {
                $response = ['status' => false, 'message' => 'Admin not found', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function logOut(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            $response = ['status' => true, 'message' => 'User logged out successfully', 'data' => ''];
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => ''];
        }
        return response()->json($response);
    }
    public function forgetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $user = User::where('email', $request->email)->first();
            if (! empty($user)) {
                $token = $user->createToken('api-token')->plainTextToken;
                $data  = [
                    'name'  => $user->firstName . ' ' . $user->lastName,
                    'token' => $token,
                    'url'   => env('FRONTEND_URL'),
                    'year'  => date('Y'),
                ];
                $existing = DB::table('password_reset_tokens')
                    ->where('email', $user->email)
                    ->first();

                if ($existing) {
                    DB::table('password_reset_tokens')
                        ->where('email', $user->email)
                        ->update([
                            'token'      => $token,
                            'created_at' => now(),
                        ]);
                } else {
                    DB::table('password_reset_tokens')->insert([
                        'email'      => $user->email,
                        'token'      => $token,
                        'created_at' => now(),
                    ]);
                }

                Mail::to($request->email)->send(new SendMail($data));
                $response = ['status' => true, 'message' => 'We have e-mailed your password reset link!', 'data' => compact('token')];
            } else {
                $response = ['status' => false, 'message' => 'User not found', 'data' => null];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => null]);
        }
    }
    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'token'           => 'required|string',
                'password'        => 'required|string|min:8|max:255',
                'confirmPassword' => 'required|string|min:8|max:255|same:password',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $password_reset = DB::table('password_reset_tokens')->select('*')->where('token', $request->token)->first();

            if (empty($password_reset)) {
                return $response = ['status' => false, 'message' => 'Invalid token', 'data' => ''];
            }

            $user          = User::where('email', $password_reset->email)->first();
            $expireMinutes = config('auth.passwords.users.expire');
            if ($password_reset && now()->diffInMinutes($password_reset->created_at) <= $expireMinutes) {
                $user->password = Hash::make($request->password);
                if ($user->save()) {
                    $response             = ['status' => true, 'message' => 'Password reset successfully', 'data' => ''];
                    $password_reset_token = DB::table('password_reset_tokens')->where('email', $user->email)->delete();
                } else {
                    $response = ['status' => false, 'message' => 'Password not reset please try again', 'data' => ''];
                }
                return response()->json($response);
            } else {
                return $response = ['status' => false, 'message' => 'Token expired', 'data' => ''];
            }

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => null]);
        }
    }
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password'        => 'required|string|min:8|max:255',
                'confirmPassword' => 'required|string|min:8|max:255|same:password',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }
            $user = Auth::user(); 
            if (Hash::check($request->password, $user->password)) {
                return response()->json(['status' => false, 'message' => 'New password should be different from old password', 'data' => null]);
            }
            $user->password = Hash::make($request->password);
            if ($user->save()) {
                $response = ['status' => true, 'message' => 'Password changed successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Password not changed please try again', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => null]);
        }
    }
    public function checkEmail(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|exists:users,email|max:255',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors(), 'data' => null]);
            }            
            if (Auth::check()) {
                if (Auth::user()->email === $request->email) {
                    return response()->json(['status' => true, 'message' => '', 'data' => '']);
                }
            }
            $user = User::where('email', $request->email)->first();
            if (!empty($user)) {
                return response()->json(['status' => false, 'message' => 'Email already exists', 'data' => null]);
            }
            return response()->json(['status' => true, 'message' => 'Email is available for registration', 'data' => '']);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => null]);
        }
    }
}

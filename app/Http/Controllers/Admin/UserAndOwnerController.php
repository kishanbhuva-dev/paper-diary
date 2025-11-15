<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingGroups;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAndOwnerController extends Controller
{
    public function emailWiseLogin(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'email' => 'required|email',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $user = User::where('email', $request->email)->first();

            if (empty($user)) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => '']);
            }

            $token    = $user->createToken('api-token')->plainTextToken;
            $response = ['status' => true, 'message' => 'Login successful', 'data' => compact('user', 'token')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function fetchAllUser(Request $request)
    {
        try {
            $user = User::selectRaw('id, concat(firstName, " ", lastName) as name,  phone, email, address, role')->where(function ($query) use ($request) {
                $query->where('firstName', 'like', '%' . $request->search . '%')->orWhere('lastName', 'like', '%' . $request->search . '%')->orWhere('id', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')->orWhere('phone', 'like', '%' . $request->search . '%')->orWhere('address', 'like', '%' . $request->search . '%');
            })->where('role', 'user');
            ///order by and pagination for variable: orderBy, sort, pagination   default value: id, asc, 10
            $order      = $request->orderBy ?? 'id';
            $pagination = $request->pagination ?? 10;
            $user       = $user->orderBy($order, $request->sort ?? 'asc')->paginate($pagination);
            if (count($user) > 0) {
                $response = ['status' => true, 'message' => '', 'data' => $user];
            } else {
                $response = ['status' => false, 'message' => 'No user found', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function fetchAllOwner(Request $request)
    {
        try {
            $owner = User::selectRaw('id, concat(firstName, " ", lastName) as name,  phone, email, address, role')->where(function ($query) use ($request) {
                $query->where('firstName', 'like', '%' . $request->search . '%')->orWhere('lastName', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')->orWhere('phone', 'like', '%' . $request->search . '%')->orWhere('address', 'like', '%' . $request->search . '%');
            })->where('role', 'owner');
            $order      = $request->orderBy ?? 'id';
            $pagination = $request->pagination ?? 10;
            $owner      = $owner->orderBy($order, $request->sort ?? 'asc')->paginate($pagination);
            if (count($owner) > 0) {
                $response = ['status' => true, 'message' => '', 'data' => $owner];
            } else {
                $response = ['status' => false, 'message' => 'No owner found', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function createUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'firstName' => 'required|string|max:255',
                    'lastName'  => 'required|string|max:255',
                    'email'     => 'required|email|unique:users,email',
                    'password'  => 'required|min:8',
                    // 'role'      => 'nullable|in:user,owner',
                    'address'   => 'required|string',
                    'address2'  => 'nullable|string',
                    'city'      => 'nullable|string',
                    'country'   => 'nullable|string',
                    'postcode'  => 'nullable|string',
                    'phone'     => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $user            = new User;
            $user->firstName = $request->firstName;
            $user->lastName  = $request->lastName;
            $user->email     = $request->email;
            $user->password  = Hash::make($request->password);
            $user->role      = 'user';
            $user->address   = $request->address;
            $user->address2  = $request->address2;
            $user->city      = $request->city;
            $user->country   = $request->country;
            $user->postcode  = $request->postcode;
            $user->phone     = $request->phone;
            $user->telephone = $request->telephone;
            $user->save();
            // $token    = $user->createToken('api-token')->plainTextToken;
            if ($user->role == 'owner') {

                $response = ['status' => true, 'message' => 'Owner created successfully', 'data' => compact('user')];
            } else {
                $response = ['status' => true, 'message' => 'User created successfully', 'data' => compact('user')];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [

                    'id'        => 'required|exists:users,id',
                    'firstName' => 'required|string|max:255',
                    'lastName'  => 'required|string|max:255',
                    'email'     => 'required|email|unique:users,email' . ',' . $request->id,
                    // 'password'  => 'required|min:8',
                    'address'   => 'required|string',
                    'address2'  => 'nullable|string',
                    'city'      => 'nullable|string',
                    'country'   => 'nullable|string',
                    'postcode'  => 'nullable|string',
                    'phone'     => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $user = User::where('id', $request->id)->where('role', 'user')->first();
            if (empty($user)) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => '']);
            }
            $user->firstName = $request->firstName;
            $user->lastName  = $request->lastName;
            $user->email     = $request->email;
            // $user->password  = Hash::make($request->password);
            $user->role      = 'user';
            $user->address   = $request->address;
            $user->address2  = $request->address2;
            $user->city      = $request->city;
            $user->country   = $request->country;
            $user->postcode  = $request->postcode;
            $user->phone     = $request->phone;
            $user->telephone = $request->telephone;
            $user->save();
            $response = ['status' => true, 'message' => 'User updated successfully', 'data' => compact('user')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function deleteUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'id' => 'required|exists:users,id',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $user = User::where('id', $request->id)->where('role', 'user')->first();
            if (empty($user)) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => '']);
            }
            Booking::where('userId', $user->id)->delete();
            BookingGroups::where('userId', $user->id)->delete();
            $user->delete();
            $response = ['status' => true, 'message' => 'User deleted successfully', 'data' => ''];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function createOwner(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'firstName' => 'required|string|max:255',
                    'lastName'  => 'required|string|max:255',
                    'email'     => 'required|email|unique:users,email',
                    'password'  => 'required|min:8',
                    'address'   => 'required|string',
                    'address2'  => 'nullable|string',
                    'city'      => 'nullable|string',
                    'country'   => 'nullable|string',
                    'postcode'  => 'nullable|string',
                    'phone'     => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $user            = new User;
            $user->firstName = $request->firstName;
            $user->lastName  = $request->lastName;
            $user->email     = $request->email;
            $user->password  = Hash::make($request->password);
            $user->role      = 'owner';
            $user->address   = $request->address;
            $user->address2  = $request->address2;
            $user->city      = $request->city;
            $user->country   = $request->country;
            $user->postcode  = $request->postcode;
            $user->phone     = $request->phone;
            $user->telephone = $request->telephone;
            $user->save();
            // $token    = $user->createToken('api-token')->plainTextToken;
            $response = ['status' => true, 'message' => 'Owner created successfully', 'data' => compact('user')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function updateOwner(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'id'        => 'required|exists:users,id',
                    'firstName' => 'required|string|max:255',
                    'lastName'  => 'required|string|max:255',
                    'email'     => 'required|email|unique:users,email' . ',' . $request->id,
                    // 'password'  => 'required|min:8',
                    'address'   => 'required|string',
                    'address2'  => 'nullable|string',
                    'city'      => 'nullable|string',
                    'country'   => 'nullable|string',
                    'postcode'  => 'nullable|string',
                    'phone'     => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $user = User::where('id', $request->id)->where('role', 'owner')->first();
            if (empty($user)) {
                return response()->json(['status' => false, 'message' => 'User not found', 'data' => '']);
            }
            $user->firstName = $request->firstName;
            $user->lastName  = $request->lastName;
            $user->email     = $request->email;
            // $user->password  = Hash::make($request->password);
            $user->role      = 'owner';
            $user->address   = $request->address;
            $user->address2  = $request->address2;
            $user->city      = $request->city;
            $user->country   = $request->country;
            $user->postcode  = $request->postcode;
            $user->phone     = $request->phone;
            $user->password  = $request->password ? Hash::make($request->password) : $user->password;
            $user->telephone = $request->telephone;
            $user->save();
            $response = ['status' => true, 'message' => 'User updated successfully', 'data' => compact('user')];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
    public function deleteOwner(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'id' => 'required|exists:users,id',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }
            $owner = User::where('id', $request->id)->where('role', 'owner')->first();
            if (empty($owner)) {
                return response()->json(['status' => false, 'message' => 'Owner not found', 'data' => '']);
            }
            $property       = Property::where('ownerId', $owner->id)->pluck('id');
            $resourceTypeId = ResourceType::whereIn('propertyId', $property)->pluck('id');
            Resource::whereIn('resourceTypeId', $resourceTypeId)->delete();
            ResourceType::whereIn('propertyId', $property)->delete();
            PropertyImage::whereIn('propertyId', $property)->delete();
            Property::where('ownerId', $owner->id)->delete();
            if ($owner->delete()) {
                $response = ['status' => true, 'message' => 'Owner deleted successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Owner not deleted Something went wrong', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
}

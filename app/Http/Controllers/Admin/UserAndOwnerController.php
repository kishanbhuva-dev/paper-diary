<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bookings;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\BookingOrder;
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

            $token = $user->createToken('api-token')->plainTextToken;
            $response = ['status' => true, 'message' => 'Login successful', 'data' => compact('user', 'token')];

            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function fetchAllUser(Request $request)
    {
        try {
            return $this->fetchAll($request, 'user');
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function fetchAllOwner(Request $request)
    {
        try {
            return $this->fetchAll($request, 'owner');
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];

            return response()->json($response);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'address' => 'required|string',
                    'address2' => 'nullable|string',
                    'city' => 'nullable|string',
                    'country' => 'nullable|string',
                    'postcode' => 'nullable|string',
                    'phone' => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }

            return $this->store($request, 'user');

        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function updateUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [

                    'id' => 'required|exists:users,id',
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email'.','.$request->id,
                    'address' => 'required|string',
                    'address2' => 'nullable|string',
                    'city' => 'nullable|string',
                    'country' => 'nullable|string',
                    'postcode' => 'nullable|string',
                    'phone' => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }

            return $this->update($request, 'user');

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

            return $this->delete($request, 'user');
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
                    'lastName' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email',
                    'password' => 'required|min:8',
                    'address' => 'required|string',
                    'address2' => 'nullable|string',
                    'city' => 'nullable|string',
                    'country' => 'nullable|string',
                    'postcode' => 'nullable|string',
                    'phone' => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }

            return $this->store($request, 'owner');
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function updateOwner(Request $request)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'id' => 'required|exists:users,id',
                    'firstName' => 'required|string|max:255',
                    'lastName' => 'required|string|max:255',
                    'email' => 'required|email|unique:users,email'.','.$request->id,
                    'address' => 'required|string',
                    'address2' => 'nullable|string',
                    'city' => 'nullable|string',
                    'country' => 'nullable|string',
                    'postcode' => 'nullable|string',
                    'phone' => 'nullable|string',
                    'telephone' => 'nullable|string',
                ]
            );
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => '']);
            }

            return $this->update($request, 'owner');
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

            return $this->delete($request, 'owner');
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    private function fetchAll($request, $role)
    {
        $data = User::where('role', $role);

        if ($request->search) {
            $search = $request->search;
            $data->where(function ($q) use ($search) {
                $q->where('firstName', 'LIKE', '%'.$search.'%')
                    ->orWhere('lastName', 'LIKE', '%'.$search.'%')
                    ->orWhere('email', 'LIKE', '%'.$search.'%')
                    ->orWhere('phone', 'LIKE', '%'.$search.'%')
                    ->orWhere('address', 'LIKE', '%'.$search.'%');
            });
            $search = explode(' ', $search);
            if (count($search) > 1) {
                $data->orWhere(function ($q) use ($search) {
                    $q->where('firstName', 'LIKE', '%'.$search[0].'%')
                        ->where('lastName', 'LIKE', '%'.$search[1].'%');
                });
            }
        }

        $orderby = $request->descending == 'true' ? 'DESC' : 'ASC';
        $column = $request->sortBy ?? 'id';

        if (! empty($column)) {
            $data->orderBy($column, $orderby);
        }
        $perPage = $request->input('per_page', 15);
        $data = $data->paginate($perPage);
        $response = ['status' => true, 'message' => '', 'data' => $data];

        return response()->json($response);
    }

    private function store($request, $role)
    {
        $user = new User;
        $user->firstName = $request->firstName;
        $user->lastName = $request->lastName;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->role = $role;
        $user->address = $request->address;
        $user->address2 = $request->address2;
        $user->city = $request->city;
        $user->country = $request->country;
        $user->postcode = $request->postcode;
        $user->phone = $request->phone;
        $user->telephone = $request->telephone;
        if ($user->save()) {
            $response = ['status' => true, 'message' => ucwords($role).' created successfully', 'data' => ''];
        } else {
            $response = ['status' => false, 'message' => ucwords($role).' not created Something went wrong', 'data' => ''];
        }

        return response()->json($response);

    }

    private function update($request, $role)
    {
        $user = User::where('id', $request->id)->where('role', $role)->first();
        if (empty($user)) {
            return response()->json(['status' => false, 'message' => ucwords($role).' not found', 'data' => '']);
        }
        $user->firstName = $request->firstName;
        $user->lastName = $request->lastName;
        $user->email = $request->email;
        $user->address = $request->address;
        $user->address2 = $request->address2;
        $user->city = $request->city;
        $user->country = $request->country;
        $user->postcode = $request->postcode;
        if (! empty($request->password)) {
            $user->password = Hash::make($request->password);
        }
        $user->phone = $request->phone;
        $user->telephone = $request->telephone;
        if ($user->save()) {
            return response()->json(['status' => true, 'message' => ucwords($role).' updated successfully', 'data' => '']);
        } else {
            return response()->json(['status' => false, 'message' => ucwords($role).' not updated Something went wrong', 'data' => '']);
        }

        return response()->json($response);
    }

    private function delete($request, $role)
    {
        $user = User::where('id', $request->id)->where('role', $role)->first();
        if ($role == 'owner') {
            $property = Property::where('ownerId', $user->id)->pluck('id');
            $resourceTypeId = ResourceType::whereIn('propertyId', $property)->pluck('id');
            Resource::whereIn('resourceTypeId', $resourceTypeId)->delete();
            ResourceType::whereIn('propertyId', $property)->delete();
            PropertyImage::whereIn('propertyId', $property)->delete();
            Property::where('ownerId', $user->id)->delete();
        } else {
            $bookingOrderIds = BookingOrder::where('userId', $user->id);
            Bookings::whereIn('bookingOrderId', $bookingOrderIds->pluck('id'))->delete();
            $bookingOrderIds->delete();
        }
        if (empty($user)) {
            return response()->json(['status' => false, 'message' => ucwords($role).' not found', 'data' => '']);
        }
        if ($user->delete()) {
            return response()->json(['status' => true, 'message' => ucwords($role).' deleted successfully', 'data' => '']);
        } else {
            return response()->json(['status' => false, 'message' => ucwords($role).' not deleted Something went wrong', 'data' => '']);
        }

        return response()->json($response);
    }
}

<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Resource;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class PropertyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // return Auth::user()->id;
            $property = Property::selectRaw('id, ownerId, propertyName, address,address2, latitude, longitude, email, country, county, city, postcode, phone, telephone, arrivalTime, departureTime, status, isIcal')->where('ownerId', Auth::id());
            if ($request->search) {
                $property->where(function ($query) use ($request) {
                    $query->where('email', 'like', '%' . $request->search . '%')
                        ->orWhere('propertyName', 'like', '%' . $request->search . '%')
                        ->orWhere('country', 'like', '%' . $request->search . '%')
                        ->orWhere('county', 'like', '%' . $request->search . '%')
                        ->orWhere('city', 'like', '%' . $request->search . '%')
                        ->orWhere('postcode', 'like', '%' . $request->search . '%')
                        ->orWhere('phone', 'like', '%' . $request->search . '%')
                        ->orWhere('address', 'like', '%' . $request->search . '%')
                        ->orWhere('address2', 'like', '%' . $request->search . '%')
                        ->orWhere('telephone', 'like', '%' . $request->search . '%')
                        ->orWhere('arrivalTime', 'like', '%' . $request->search . '%')
                        ->orWhere('departureTime', 'like', '%' . $request->search . '%');

                });
            }
            $pagination     = $request->pagination ?? 10;
            $orderBy        = $request->orderBy ?? 'id';
            $orderDirection = $request->orderDirection ?? 'asc';
            $property       = $property->orderBy($orderBy, $orderDirection)->paginate($pagination);
            $response       = ['status' => true, 'message' => '', 'data' => $property];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

/**
 * Show the form for creating a new resource.
 */
    public function create()
    {
        //
    }

/**
 * Store a newly created resource in storage.
 */
    public function store(Request $request)
    {
        try {
            // return $request->all();
            $validator = Validator::make($request->all(),
                [
                    'propertyName'     => 'required',
                    'address'          => 'required',
                    'latitude'         => 'required',
                    'longitude'        => 'required',
                    'email'            => 'nullable|email',
                    'country'          => 'nullable|string',
                    'county'           => 'nullable|string',
                    'city'             => 'nullable|string',
                    'postcode'         => 'nullable|string',
                    'phone'            => 'nullable|string',
                    'telephone'        => 'nullable|string',
                    'arrivalTime'      => 'nullable|date',
                    'departureTime'    => 'nullable|date',
                    'status'           => 'nullable|boolean',
                    'isIcal'           => 'nullable|boolean',
                    'resourceTypeName' => 'required|string',
                    'price'            => 'required|numeric',
                    'adjustedPrice'    => 'nullable|numeric',
                    'adjustedStart'    => 'nullable|date',
                    'adjustedEnd'      => 'nullable|date',
                    'resourceName'     => 'required|string',
                    'resourceStatus'   => 'nullable|boolean',

                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }

            $property                = new Property();
            $property->ownerId       = Auth::id();
            $property->propertyName  = $request->propertyName;
            $property->email         = $request->email;
            $property->address       = $request->address;
            $property->address2      = $request->address2;
            $property->country       = $request->country;
            $property->county        = $request->county;
            $property->city          = $request->city;
            $property->postcode      = $request->postcode;
            $property->phone         = $request->phone;
            $property->telephone     = $request->telephone;
            $property->latitude      = $request->latitude;
            $property->longitude     = $request->longitude;
            $property->arrivalTime   = $request->arrivalTime ? date('H:i', strtotime($request->arrivalTime)) : null;
            $property->departureTime = $request->departureTime ? date('H:i', strtotime($request->departureTime)) : null;
            $property->status        = $request->status ?? 0;
            $property->isIcal        = $request->isIcal ?? 0;
            if ($property->save()) {
                $resourceTypes                = new ResourceType();
                $resourceTypes->propertyId    = $property->id;
                $resourceTypes->name          = $request->resourceTypeName;
                $resourceTypes->price         = $request->price;
                $resourceTypes->adjustedPrice = $request->adjustedPrice;
                $resourceTypes->adjustedStart = $request->adjustedStart;
                $resourceTypes->adjustedEnd   = $request->adjustedEnd;
                $resourceTypes->save();

                $resources                 = new Resource();
                $resources->resourceTypeId = $resourceTypes->id;
                $resources->name           = $request->resourceName;
                $resources->status         = $request->resourceStatus ?? 1;
                $resources->save();

                $response = ['status' => true, 'message' => 'Property added successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property addition failed', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

    public function show(string $id)
    {
        //
        try {
            $property = Property::where('id', $id)->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $response = ['status' => true, 'message' => '', 'data' => $property];
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

/**
 * Show the form for editing the specified resource.
 */
    public function edit(string $id)
    {
        //
    }

/**
 * Update the specified resource in storage.
 */
    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(),
                [
                    'propertyName'     => 'required',
                    'address'          => 'required',
                    'latitude'         => 'required',
                    'longitude'        => 'required',
                    'email'            => 'nullable|email',
                    'country'          => 'nullable|string',
                    'county'           => 'nullable|string',
                    'city'             => 'nullable|string',
                    'postcode'         => 'nullable|string',
                    'phone'            => 'nullable|string',
                    'telephone'        => 'nullable|string',
                    'arrivalTime'      => 'nullable|time',
                    'departureTime'    => 'nullable|time',
                    'status'           => 'nullable|boolean',
                    'isIcal'           => 'nullable|boolean',
                    'resourceTypeName' => 'required',
                    'price'            => 'required',
                    'adjustedPrice'    => 'nullable|numeric',
                    'adjustedStart'    => 'nullable|date',
                    'adjustedEnd'      => 'nullable|date',
                    'resourceName'     => 'required',
                    'resourceStatus'   => 'nullable|boolean',
                ]
            );
            if ($validator->fails()) {
                return $response = ['status' => false, 'message' => $validator->messages()->first()];
            }
            $property = Property::where('id', $id)->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $property->propertyName  = $request->propertyName;
            $property->email         = $request->email;
            $property->address       = $request->address;
            $property->address2      = $request->address2;
            $property->country       = $request->country;
            $property->county        = $request->county;
            $property->city          = $request->city;
            $property->postcode      = $request->postcode;
            $property->phone         = $request->phone;
            $property->telephone     = $request->telephone;
            $property->latitude      = $request->latitude;
            $property->longitude     = $request->longitude;
            $property->arrivalTime   = $request->arrivalTime ? date('H:i', strtotime($request->arrivalTime)) : null;
            $property->departureTime = $request->departureTime ? date('H:i', strtotime($request->departureTime)) : null;
            $property->status        = $request->status ?? 0;
            $property->isIcal        = $request->isIcal ?? 0;

            if ($property->save()) {
                $resourceTypes = ResourceType::where('propertyId', $id)->first();
                if (empty($resourceTypes)) {
                    $resourceTypes                = new ResourceType();
                    $resourceTypes->propertyId    = $id;
                    $resourceTypes->name          = $request->resourceTypeName;
                    $resourceTypes->price         = $request->price;
                    $resourceTypes->adjustedPrice = $request->adjustedPrice;
                    $resourceTypes->adjustedStart = $request->adjustedStart;
                    $resourceTypes->adjustedEnd   = $request->adjustedEnd;
                    $resourceTypes->save();
                    if ($resourceTypes->save()) {
                        $resource                 = new Resource();
                        $resource->resourceTypeId = $resourceTypes->id;
                        $resource->name           = $request->resourceName;
                        $resource->status         = $request->resourceStatus ?? 1;
                        $resource->save();
                    }
                } else {
                    $resourceTypes->name          = $request->resourceTypeName;
                    $resourceTypes->price         = $request->price;
                    $resourceTypes->adjustedPrice = $request->adjustedPrice;
                    $resourceTypes->adjustedStart = $request->adjustedStart ? date('Y-m-d', strtotime($request->adjustedStart)) : null;
                    $resourceTypes->adjustedEnd   = $request->adjustedEnd ? date('Y-m-d 23:59:59', strtotime($request->adjustedEnd)) : null;
                    $resourceTypes->save();
                    if ($resourceTypes->save()) {
                        $resource         = Resource::where('resourceTypeId', $resourceTypes->id)->first();
                        $resource->name   = $request->resourceName;
                        $resource->status = $request->resourceStatus ?? 1;
                        $resource->save();
                    }
                }
                $response = ['status' => true, 'message' => 'Property updated successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property addition failed', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }

/**
 * Remove the specified resource from storage.
 */
    public function destroy(string $id)
    {
        try {
            $property = Property::where('id', $id)->where('ownerId', Auth::id())->first();
            if (empty($property)) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => '']);
            }
            $resourceTypes = ResourceType::where('propertyId', $id)->pluck('id');
            $resources     = Resource::whereIn('resourceTypeId', $resourceTypes)->delete();
            $resourceTypes = ResourceType::where('propertyId', $id)->delete();
            if ($property->delete()) {
                $response = ['status' => true, 'message' => 'Property deleted successfully', 'data' => ''];
            } else {
                $response = ['status' => false, 'message' => 'Property deletion failed', 'data' => ''];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => '']);
        }
    }
}

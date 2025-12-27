<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Property;
use App\Models\ResourceType;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'propertyId' => 'required|exists:property,id',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            $resources = Resource::whereHas('resourceType', function ($query) use ($request) {
                $query->where('propertyId', $request->propertyId);
            })->with('resourceType.property')->get();
            if ($resources->count() > 0) {
                $response = ['status' => true, 'message' => '', 'data' => $resources];
            } else {
                $response = ['status' => true, 'message' => 'No resources found', 'data' => []];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'           => 'required|string',
                'status'         => 'nullable|boolean',
                'resourceTypeId' => 'required|exists:resource_types,id',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            $resources       = new Resource;
            $resources->name = $request->name;
            if (isset($request->status)) {
                $resources->status = $request->status;
            }
            $resources->resourceTypeId = $request->resourceTypeId;
            $resources->save();
            $response = ['status' => true, 'message' => 'Resource created successfully', 'data' => []];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function multipleStore(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'name'             => 'required|array',
                'name.*'           => 'required|string',
                'status'           => 'nullable|array',
                'status.*'         => 'nullable|boolean',
                'resourceTypeId'   => 'required|array',
                'resourceTypeId.*' => 'required|exists:resource_types,id',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            foreach ($request->name as $key => $value) {
                $resources       = new Resource;
                $resources->name = $value;
                if (isset($request->status[$key])) {
                    $resources->status = $request->status[$key];
                }
                $resources->resourceTypeId = $request->resourceTypeId[$key];
                $resources->save();
            }
            $response = ['status' => true, 'message' => 'Resources created successfully', 'data' => []];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
        // try {
        //     $validator = Validator::make($request->all(), [
        //         'data'                  => 'required|array',
        //         'data.name'             => 'required|string',
        //         'data.status'           => 'nullable|boolean',
        //         'data.resourceTypeId.*' => 'required|exists:resource_types,id',
        //     ]);
        //     if ($validator->fails()) {
        //         $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
        //         return response()->json($response);
        //     }
        //     foreach ($request->data as $key => $value) {
        //         $resources       = new Resource;
        //         $resources->name = $value['name'];
        //         if (isset($value['status'])) {
        //             $resources->status = $value['status'];
        //         }
        //         $resources->resourceTypeId = $value['resourceTypeId'];
        //         $resources->save();
        //     }
        //     $response = ['status' => true, 'message' => 'Resources created successfully', 'data' => []];
        //     return response()->json($response);
        // } catch (\Throwable $th) {
        //     $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
        //     return response()->json($response);
        // }
    }
    public function show(string $id)
    {
        try {
            $resources = Resource::where('id', $id)->with('resourceType.property')->first();
            if (! empty($resources)) {
                $response = ['status' => true, 'message' => '', 'data' => $resources];
            } else {
                $response = ['status' => false, 'message' => 'Resource not found', 'data' => []];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'           => 'required|string',
                'status'         => 'nullable|boolean',
                'resourceTypeId' => 'required|exists:resource_types,id',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            $resources = Resource::where('id', $id)->first();
            if (! empty($resources)) {
                $resources->name = $request->name;
                if (isset($request->status)) {
                    $resources->status = $request->status;
                }
                $resources->resourceTypeId = $request->resourceTypeId;
                $resources->save();
                $response = ['status' => true, 'message' => 'Resource updated successfully', 'data' => []];
            } else {
                $response = ['status' => false, 'message' => 'Resource not found', 'data' => []];
            }
            return response()->json($response);
        } catch (\Throwable $th) {

        }
    }
    public function multipleUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids'              => 'required|array',
                'ids.*'            => 'required|exists:resources,id',
                'name'             => 'required|array',
                'name.*'           => 'required|string',
                'status'           => 'nullable|array',
                'status.*'         => 'nullable|boolean',
                'resourceTypeId'   => 'required|array',
                'resourceTypeId.*' => 'required|exists:resource_types,id',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }

            foreach ($request->ids as $key => $value) {
                $resources       = Resource::where('id', $value)->first();
                $resources->name = $request->name[$key];
                if (isset($request->status[$key])) {
                    $resources->status = $request->status[$key];
                }
                $resources->resourceTypeId = $request->resourceTypeId[$key];
                $resources->save();
            }
            $response = ['status' => true, 'message' => 'Resources updated successfully', 'data' => []];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
        // try {
        //     $validator = Validator::make($request->all(), [
        //         'data'                  => 'required|array',

        //         'data.*.id'             => 'required|exists:resources,id',
        //         'data.*.name'           => 'required|string',
        //         'data.*.status'         => 'nullable|boolean',
        //         'data.*.resourceTypeId' => 'required|exists:resource_types,id',
        //     ]);
        //     if ($validator->fails()) {
        //         $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
        //         return response()->json($response);
        //     }
        //     foreach ($request->data as $key => $value) {
        //         $resources       = Resource::where('id', $value['id'])->first();
        //         $resources->name = $value['name'];
        //         if (isset($value['status'])) {
        //             $resources->status = $value['status'];
        //         }
        //         $resources->resourceTypeId = $value['resourceTypeId'];
        //         $resources->save();
        //     }
        //     $response = ['status' => true, 'message' => 'Resources updated successfully', 'data' => []];
        //     return response()->json($response);
        // } catch (\Throwable $th) {
        //     $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
        //     return response()->json($response);
        // }
    }
    public function destroy(string $id)
    {
        try {
            $resources = Resource::where('id', $id)->first();
            if (! empty($resources)) {
                if ($resources->delete()) {
                    $response = ['status' => true, 'message' => 'Resource deleted successfully', 'data' => []];
                } else {
                    $response = ['status' => false, 'message' => 'Resource not deleted', 'data' => []];
                }
            } else {
                $response = ['status' => false, 'message' => 'Resource not found', 'data' => []];
            }
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function resourceWiseList(Request $request)
    {
        try {

            $ownerId = auth()->user()->id;
            $propertyIds = Property::where('ownerId', $ownerId)->pluck('id');
            $resourceTypes = ResourceType::whereIn('propertyId', $propertyIds)->get();
            $resources = Resource::whereIn('resourceTypeId', $resourceTypes->pluck('id'))
                ->withAggregate('resourceType', 'name')
                ->with(['bookings' => function ($query) {
                    $query->selectRaw('id,resourceId,date_format(arrivalDateTime, "%Y-%m-%d") as arrivalDateTime, date_format(departureDateTime, "%Y-%m-%d") as departureDateTime')->where('status', '!=', 'cancelled');
                }])
                ->get();
            
            $response  = ['status' => true, 'message' => 'Resources fetched successfully', 'data' => $resources];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
}
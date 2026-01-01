<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\BookingOrder;
use App\Models\Property;
use App\Models\ResourceType;
use App\Models\Bookings;
use Carbon\Carbon;

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
    // public function resourceWiseList(Request $request)
    // {
    //     try {
    //         $ownerId = auth()->id();
    //         $propertyIds = Property::where('ownerId', $ownerId)->pluck('id');
    //         $resourceTypes = ResourceType::whereIn('propertyId', $propertyIds)->get();
    //         $resourceTypeIds = $resourceTypes->pluck('id');
    //         $resources = Resource::select('id', 'name', 'resourceTypeId')
    //         ->whereIn('resourceTypeId', $resourceTypeIds)
    //         ->with('resourceType.property')
    //         ->get()
    //         ->map(function ($item) {
    //             return [
    //                 'id' => $item->id,
    //                 'name' => $item->name,
    //                 'property_name' => $item->resourceType->property->propertyName ?? 'N/A',
    //                 'resource_type_name' => $item->resourceType->name ?? 'N/A',
    //             ];
    //         });
    //         $resourcesid = $resources->pluck('id');    
    //         $bookings = BookingOrder::selectRaw("id,guestFullName,guestEmail,guestPhone,resourceTypeId,arrivalDateTime,departureDateTime,status")->with([
    //         'booking' => function ($query) use ($resourcesid) {$query->select('id', 'resourceId', 'bookingOrderId')->whereIn('resourceId', $resourcesid);},'booking.resource:id,name'])->get();
    //         $bookings = $bookings->map(function ($order) {
    //             return [
    //                 'id'               => $order->id,
    //                 'guestFullName'    => $order->guestFullName,
    //                 'guestEmail'       => $order->guestEmail,
    //                 'guestPhone'       => $order->guestPhone,
    //                 'resourceTypeId'   => $order->resourceTypeId,
    //                 'arrivalDateTime'  => Carbon::parse($order->arrivalDateTime)->format('d-m-Y'),
    //                 'departureDateTime'=> Carbon::parse($order->departureDateTime)->format('d-m-Y'),
    //                 'status'           => $order->status,
    //                 'resource_name'    => optional($order->booking->first()->resource)->name,
    //             ];
    //         });
    //         $data = [
    //             'resource' => $resources->toArray(),
    //             'booking' => $bookings->toArray(),
    //         ];
    //         $response  = ['status' => true, 'message' => 'Resources fetched successfully', 'data' => $data];
    //         return response()->json($response);
    //     } catch (\Throwable $th) {
    //         $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
    //         return response()->json($response);
    //     }
    // }
    public function resourceWiseList(Request $request)
    {
        try {
            $propertyIds = $request->propertyIds;
            
            if (!empty($propertyIds)) {
                // When property IDs exist in request, filter by those properties
                $propertyIds = is_array($propertyIds) ? $propertyIds : [$propertyIds];
                $resourceTypes = ResourceType::whereIn('propertyId', $propertyIds)->get();
                $resourceTypeIds = $resourceTypes->pluck('id');
                $resources = Resource::select('id', 'name', 'resourceTypeId')
                ->whereIn('resourceTypeId', $resourceTypeIds)
                ->with('resourceType.property')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'property_name' => $item->resourceType->property->propertyName ?? 'N/A',
                        'resource_type_name' => $item->resourceType->name ?? 'N/A',
                    ];
                });
            } else {
                // When no property IDs in request, get all resources
                $resources = Resource::select('id', 'name', 'resourceTypeId')
                ->with('resourceType.property')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'property_name' => $item->resourceType->property->propertyName ?? 'N/A',
                        'resource_type_name' => $item->resourceType->name ?? 'N/A',
                    ];
                });
            }
            $resourcesid = $resources->pluck('id');    
            $bookings = BookingOrder::selectRaw("id,guestFullName,guestEmail,guestPhone,resourceTypeId,arrivalDateTime,departureDateTime,status")->with([
            'booking' => function ($query) use ($resourcesid) {$query->select('id', 'resourceId', 'bookingOrderId')->whereIn('resourceId', $resourcesid);},'booking.resource:id,name'])->get();
            $bookings = $bookings->map(function ($order) {
                return [
                    'id'               => $order->id,
                    'guestFullName'    => $order->guestFullName,
                    'guestEmail'       => $order->guestEmail,
                    'guestPhone'       => $order->guestPhone,
                    'resourceTypeId'   => $order->resourceTypeId,
                    'arrivalDateTime'  => Carbon::parse($order->arrivalDateTime)->format('d-m-Y'),
                    'departureDateTime'=> Carbon::parse($order->departureDateTime)->format('d-m-Y'),
                    'status'           => $order->status,
                    'resource_name'    => optional(optional($order->booking->first())->resource)->name ?? 'N/A',
                ];
            });
            $data = [
                'resource' => $resources->toArray(),
                'booking' => $bookings->toArray(),
            ];
            $response  = ['status' => true, 'message' => 'Resources fetched successfully', 'data' => $data];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
}
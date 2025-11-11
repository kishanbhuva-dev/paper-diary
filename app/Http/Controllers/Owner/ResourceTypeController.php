<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use Validator;

class ResourceTypeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $resourceTypes = ResourceType::where('propertyId', $request->id)->with('property')->get();
            $response      = ['status' => true, 'message' => '', 'data' => $resourceTypes];
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

                'propertyId'      => 'required|exists:property,id',
                'name'            => 'required|array',
                'name.*'          => 'required|string',
                'price'           => 'required|array',
                'price.*'         => 'required|numeric',
                'adjustPrice'     => 'nullable|array',
                'adjustPrice.*'   => 'nullable|numeric',
                'adjustedStart'   => 'nullable|array',
                'adjustedStart.*' => 'nullable|date',
                'adjustedEnd'     => 'nullable|array',
                'adjustedEnd.*'   => 'nullable|date',
                'slot'            => 'nullable|array',
                'slot.*'          => 'nullable|in:hourly,day,monthly',

            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            foreach ($request->name as $key => $nameValue) {
                $resourceType                = new ResourceType();
                $resourceType->propertyId    = $request->propertyId;
                $resourceType->name          = $nameValue;
                $resourceType->price         = $request->price[$key];
                $resourceType->adjustedPrice = $request->adjustedPrice[$key] ?? null;
                $resourceType->adjustedStart = ($request->adjustedStart[$key] ?? null) ? date('Y-m-d 00:00:00', strtotime($request->adjustedStart[$key])) : null;
                $resourceType->adjustedEnd   = ($request->adjustedEnd[$key] ?? null) ? date('Y-m-d 00:00:00', strtotime($request->adjustedEnd[$key])) : null;
                if (isset($request->slot[$key])) {
                    $resourceType->slot = $request->slot[$key];
                }if (! $resourceType->save()) {
                    $response = ['status' => false, 'message' => 'Resource Type not added', 'data' => []];
                    return response()->json($response);
                }
            }
            $response = ['status' => true, 'message' => 'Resource Types successfully added', 'data' => []];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
        // try {
        //     $validator = Validator::make($request->all(), [
        //         'data'                 => 'required|array',
        //         'data.*.propertyId'    => 'required|exists:property,id',
        //         'data.*.name'          => 'required|string',
        //         'data.*.price'         => 'required|numeric',
        //         'data.*.adjustPrice'   => 'nullable|numeric',
        //         'data.*.adjustedStart' => 'nullable|date',
        //         'data.*.adjustedEnd'   => 'nullable|date',
        //         'data.*.slot'          => 'nullable|in:hourly,day,monthly',
        //     ]);
        //     if ($validator->fails()) {
        //         $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
        //         return response()->json($response);
        //     }
        //     foreach ($request->data as $key => $dataValue) {
        //         $resourceType                = new ResourceType();
        //         $resourceType->propertyId    = $dataValue['propertyId'];
        //         $resourceType->name          = $dataValue['name'];
        //         $resourceType->price         = $dataValue['price'];
        //         $resourceType->adjustedPrice = $dataValue['adjustPrice'] ?? null;
        //         $resourceType->adjustedStart = ($dataValue['adjustedStart'] ?? null) ? date('Y-m-d 00:00:00', strtotime($dataValue['adjustedStart'])) : null;
        //         $resourceType->adjustedEnd   = ($dataValue['adjustedEnd'] ?? null) ? date('Y-m-d 00:00:00', strtotime($dataValue['adjustedEnd'])) : null;
        //         if (isset($dataValue['slot'])) {
        //             $resourceType->slot = $dataValue['slot'];
        //         }if (! $resourceType->save()) {
        //             $response = ['status' => false, 'message' => 'Resource Type not added', 'data' => []];
        //             return response()->json($response);
        //         }
        //     }
        //     $response = ['status' => true, 'message' => 'Resource Types successfully added', 'data' => []];
        //     return response()->json($response);
        // } catch (\Throwable $th) {
        //     $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
        //     return response()->json($response);
        // }
    }
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'propertyId'    => 'required|integer',
                'name'          => 'required|string',
                'price'         => 'required|numeric',
                'adjustedPrice' => 'nullable|numeric',
                'adjustedStart' => 'nullable|date',
                'adjustedEnd'   => 'nullable|date',
                'slot'          => 'nullable|in:hourly,day,monthly',
            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            $resourceType                = new ResourceType;
            $resourceType->propertyId    = $request->propertyId;
            $resourceType->name          = $request->name;
            $resourceType->price         = $request->price;
            $resourceType->adjustedPrice = $request->adjustPrice;
            $resourceType->adjustedStart = $request->adjustedStart ? date('Y-m-d 00:00:00', strtotime($request->adjustedStart)) : null;
            $resourceType->adjustedEnd   = $request->adjustedEnd ? date('Y-m-d 23:59:59', strtotime($request->adjustedEnd)) : null;
            if (! $resourceType->save()) {
                $response = ['status' => false, 'message' => 'Resource Type not updated', 'data' => []];
                return response()->json($response);
            }
            $response = ['status' => true, 'message' => '', 'data' => $resourceType];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }

    public function multipleUpdate(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ids'             => 'required|array',
                'ids.*'           => 'required|exists:resource_types,id',
                'name'            => 'required|array',
                'name.*'          => 'required|string',
                'price'           => 'required|array',
                'price.*'         => 'required|numeric',
                'adjustPrice'     => 'nullable|array',
                'adjustPrice.*'   => 'nullable|numeric',
                'adjustedStart'   => 'nullable|array',
                'adjustedStart.*' => 'nullable|date',
                'adjustedEnd'     => 'nullable|array',
                'adjustedEnd.*'   => 'nullable|date',
                'slot'            => 'nullable|array',
                'slot.*'          => 'nullable|in:hourly,day,monthly',

            ]);
            if ($validator->fails()) {
                $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
                return response()->json($response);
            }
            foreach ($request->name as $key => $value) {
                $resourceType                = ResourceType::where('id', $request->ids[$key])->first();
                $resourceType->propertyId    = $request->propertyId;
                $resourceType->name          = $value;
                $resourceType->price         = $request->price[$key];
                $resourceType->adjustedPrice = $request->adjustedPrice[$key] ?? null;
                $resourceType->adjustedStart = ($request->adjustedStart[$key] ?? null) ? date('Y-m-d 00:00:00', strtotime($request->adjustedStart[$key])) : null;
                $resourceType->adjustedEnd   = ($request->adjustedEnd[$key] ?? null) ? date('Y-m-d 00:00:00', strtotime($request->adjustedEnd[$key])) : null;
                if (isset($request->slot[$key])) {
                    $resourceType->slot = $request->slot[$key];
                }if (! $resourceType->save()) {
                    $response = ['status' => false, 'message' => 'Resource Type not added', 'data' => []];
                    return response()->json($response);
                }
            }
            $response = ['status' => true, 'message' => 'Resource Type updated successfully', 'data' => []];
            return response()->json($response);

        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }

        //     try {
        //         $validator = Validator::make($request->all(), [
        //             'data'                 => 'required|array',
        //             'data.ids.*'           => ''
        //             'data.*.propertyId'    => 'required|exists:property,id',
        //             'data.*.name'          => 'required|string',
        //             'data.*.price'         => 'required|numeric',
        //             'data.*.adjustPrice'   => 'nullable|numeric',
        //             'data.*.adjustedStart' => 'nullable|date',
        //             'data.*.adjustedEnd'   => 'nullable|date',
        //             'data.*.slot'          => 'nullable|in:hourly,day,monthly',

        //         ]);
        //     } catch (\Throwable $th) {
        //         $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
        //         return response()->json($response);
        //     }
        // }
        // public function update(Request $request, string $id)
        // {
        //     try {

        //         $validator = Validator::make($request->all(), [
        //             'name'          => 'required|string',
        //             'price'         => 'required|numeric',
        //             'adjustPrice'   => 'nullable|numeric',
        //             'adjustedStart' => 'nullable|date',
        //             'adjustedEnd'   => 'nullable|date',
        //             'slot'          => 'nullable|in:hourly,day,monthly',
        //         ]);
        //         if ($validator->fails()) {
        //             $response = ['status' => false, 'message' => $validator->errors()->first(), 'data' => []];
        //             return response()->json($response);
        //         }
        //         $resourceType = ResourceType::where('id', $id)->first();
        //         if (! $resourceType) {
        //             $response = ['status' => false, 'message' => 'Resource Type not found', 'data' => []];
        //             return response()->json($response);
        //         }
        //         $resourceType->name          = $request->name;
        //         $resourceType->price         = $request->price;
        //         $resourceType->adjustedPrice = $request->adjustedPrice ?? null;
        //         $resourceType->adjustedStart = $request->adjustedStart ? date('Y-m-d 00:00:00', strtotime($request->adjustedStart)) : null;
        //         $resourceType->adjustedEnd   = $request->adjustedEnd ? date('Y-m-d 23:59:59', strtotime($request->adjustedEnd)) : null;
        //         if (! $resourceType->save()) {
        //             $response = ['status' => false, 'message' => 'Resource Type not updated', 'data' => []];
        //             return response()->json($response);
        //         }
        //         $response = ['status' => true, 'message' => '', 'data' => $resourceType];
        //         return response()->json($response);
        //     } catch (\Throwable $th) {
        //         $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
        //         return response()->json($response);
        //     }
    }
    public function show($id)
    {
        try {
            $resourceType = ResourceType::where('id', $id)->with('property')->first();
            if (! $resourceType) {
                $response = ['status' => false, 'message' => 'Resource Type not found', 'data' => []];
                return response()->json($response);
            }
            $response = ['status' => true, 'message' => '', 'data' => $resourceType];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
    public function destroy(string $id)
    {
        try {
            $resourceType = ResourceType::where('id', $id)->first();
            if (! $resourceType) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Resource Type not found',
                    'data'    => [],
                ]);
            }
            $resourceType->resources()->delete();
            $deleted = $resourceType->delete();
            return response()->json([
                'status'  => $deleted,
                'message' => $deleted ? 'Resource Type deleted successfully' : 'Resource Type not deleted',
                'data'    => [],
            ]);
        } catch (\Throwable $th) {
            $response = ['status' => false, 'message' => $th->getMessage(), 'data' => []];
            return response()->json($response);
        }
    }
}

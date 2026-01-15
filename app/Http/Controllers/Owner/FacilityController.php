<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Property;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Validator;

class FacilityController extends Controller
{
    public function getFacility(Request $request): JsonResponse
    {
        try {
            $facilities = Facility::where('status', true)->get();

            return response()->json(['status' => true, 'message' => '', 'data' => $facilities]);
        } catch (Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function addFacilityProperty(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'data.propertyId'   => 'required|integer|exists:property,id',
            'data.facilityId'   => 'required|array',
            'data.facilityId.*' => 'required|integer|exists:facilities,id',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
            ]);
        }

        $propertyId = $request->input('data.propertyId');
        $facilityIds = $request->input('data.facilityId');

        $property = Property::findOrFail($propertyId);
        if (! empty($facilityIds)) {
            $property->facilities()->sync($facilityIds);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Property facilities updated successfully',
            'data'    => $property->load('facilities'),
        ]);
    }
}

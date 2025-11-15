<?php
namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Property;
use Illuminate\Http\Request;
use Validator;

class FacilityController extends Controller
{
    public function getFacility(Request $request)
    {
        try {
            $facilities = Facility::where('status', true)->get();
            if (empty($facilities)) {
                return response()->json(['status' => false, 'message' => 'No facility found']);
            }
            return response()->json(['status' => true, 'message' => 'Facility found', 'data' => $facilities]);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function addFacilityProperty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data.propertyId'   => 'required|integer|exists:property,id',
            'data.facilityId'   => 'required|array',
            'data.facilityId.*' => 'required|integer|exists:facilities,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
        $propertyId  = $request->data['propertyId'];
        $facilityIds = $request->data['facilityId'];
        $property    = Property::where('id', $propertyId)->firstOrFail();
        $property->facilities()->sync($facilityIds);
        $property->load('facilities');
        return response()->json(['status' => true, 'message' => 'Property facilities updated successfully', 'data' => '']);
    }
}

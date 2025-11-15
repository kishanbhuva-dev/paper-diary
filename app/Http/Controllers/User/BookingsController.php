<?php

namespace App\Http\Controllers\User;

use App\Models\BookingOrder;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use App\Models\Bookings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingsController extends Controller
{
    
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
                'propertyId' => 'required|exists:property,id',
                'resourceTypeId' => 'required|exists:resource_types,id',
                'arrivalDateTime' => 'required|date',
                'departureDateTime' => 'required|date|after:arrivalDateTime',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'propertyId' => 'required|exists:property,id',
                'resourceTypeId' => 'required|exists:resource_types,id',
                'arrivalDateTime' => 'required|date',
                'departureDateTime' => 'required|date|after:arrivalDateTime',
                'adults' => 'required|integer',
                'children' => 'nullable|integer',
                'price' => 'required|numeric',
                'status' => 'nullable|in:pending,cancelled,confirmed',
                'paymentStatus' => 'nullable|in:paid,unpaid,failed,cancelled,confirm',
                'resources' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $availableResourcesData = getResourcesAvailable($request->resourceTypeId, $request->arrivalDateTime, $request->departureDateTime);            

            $availableResources=count($availableResourcesData);

            if ($availableResources != $request->resources) {

                return response()->json(['status' => false, 'message' => 'resources not available', 'data' => []]);
            }
            
            $resourceType = ResourceType::where('id', $request->resourceTypeId)->first();
            $booking = new BookingOrder;
            $booking->propertyId = $request->propertyId;
            $booking->resourceTypeId = $request->resourceTypeId;
            $booking->arrivalDateTime = $request->arrivalDateTime? date('Y-m-d H:i:s', strtotime($request->arrivalDateTime)) : null;
            $booking->departureDateTime = $request->departureDateTime? date('Y-m-d H:i:s', strtotime($request->departureDateTime)) : null;
            $booking->adult = $request->adults;
            if (isset($request->children)) {
                $booking->children = $request->children;
            }
            $booking->price = $request->price;
            $booking->cost = $resourceType->price;
            $booking->userId = Auth::user()->id;
            if (isset($request->status)) {
                $booking->status = $request->status;
            }
            if (isset($request->paymentStatus)) {
                $booking->paymentStatus = $request->paymentStatus;
            }
            if ($booking->save()) {
                $bookingOrderId = $booking->id;
                foreach ($availableResourcesData as $key => $resource) {
                    $booking =new Bookings;
                    $booking->bookingOrderId = $bookingOrderId;
                    $booking->resourceId = $resource;
                    $booking->resourceTypeId = $request->resourceTypeId;
                    $booking->arrivalDateTime = $request->arrivalDateTime ? date('Y-m-d H:i:s', strtotime($request->arrivalDateTime)) : null;
                    $booking->departureDateTime = $request->departureDateTime ? date('Y-m-d H:i:s', strtotime($request->departureDateTime)) : null;
                    $booking->price = $request->price ?? 0;
                    $booking->save();
                }
                return response()->json(['status' => true, 'message' => 'Booking created successfully', 'data' => ''], 201);
            }else {
                return response()->json(['status' => false, 'message' => 'Failed to your booking', 'data' => []], 500);
            }

        }catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function show( $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
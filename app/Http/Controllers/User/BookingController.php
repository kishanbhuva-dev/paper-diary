<?php
namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // try {

        //     $validator = Validator::make($request->all(),
        //         [
        //             'propertyId'        => 'required|exists:property,id',
        //             'resourcesId'       => 'required|exists:resources,id',
        //             'resourceTypesId'   => 'required|exists:resource_types,id',
        //             'paymentStatus'     => 'required|in:paid,unpaid,failed,cancelled,confirm',
        //             'amount'            => 'nullable|numeric',
        //             'arrivalDateTime'   => 'required|date',
        //             'departureDateTime' => 'required|date|after:arrivalDateTime',
        //         ],
        //     );
        //     if ($validator->fails()) {
        //         return $response = ['status' => false, 'message' => $validator->messages()->first()];
        //     }
        //     $arrivalDateTime    = date('Y-m-d H:i:s', strtotime($request->arrivalDateTime));
        //     $departureDateTime  = date('Y-m-d H:i:s', strtotime($request->departureDateTime));
        //     $availableResources = getResourceAvailable($request->resourceTypesId, $arrivalDateTime, $departureDateTime);
        //     if (! in_array($request->resourcesId, $availableResources->toArray())) {
        //         return $response = ['status' => false, 'message' => 'Resources not available'];
        //     }
        //     $booking                    = new Booking();
        //     $booking->userId            = Auth::user()->id;
        //     $booking->propertyId        = $request->propertyId;
        //     $booking->resourcesId       = $request->resourcesId;
        //     $booking->resourceTypesId   = $request->resourceTypesId;
        //     $booking->paymentStatus     = $request->paymentStatus;
        //     $booking->amount            = $request->amount;
        //     $booking->arrivalDateTime   = $request->arrivalDateTime ? date('Y-m-d H:i:s', strtotime($request->arrivalDateTime)) : null;
        //     $booking->departureDateTime = $request->departureDateTime ? date('Y-m-d H:i:s', strtotime($request->departureDateTime)) : null;
        //     $booking->save();
        //     return $response = ['status' => true, 'message' => 'Your Booking successfully'];
        // } catch (\Throwable $th) {
        //     return $response = ['status' => false, 'message' => $th->getMessage()];
        // }
        // return $request->all();
        $validator = Validator::make($request->all(),
            [
                // 'data'                   => 'required|array',
                'data.propertyId'        => 'required|integer|exists:property,id',
                'data.resourcesId'       => 'required|array',
                'data.resourcesId.*'     => 'required|integer|exists:resources,id',
                'data.resourceTypesId'   => 'required|array',
                'data.resourceTypesId.*' => 'required|integer|exists:resource_types,id',
                'data.paymentStatus'     => 'required|in:paid,unpaid,failed,cancelled,confirm',
                'data.amount'            => 'required|array',
                'data.amount.*'          => 'required|numeric',
                'data.arrivalDateTime'   => 'required|date',
                'data.departureDateTime' => 'required|date|after:data.arrivalDateTime',
            ],
        );
        if ($validator->fails()) {
            return $response = ['status' => false, 'message' => $validator->messages()->first()];
        }
        $arrivalDateTime    = date('Y-m-d H:i', strtotime($request->data['arrivalDateTime']));
        $departureDateTime  = date('Y-m-d H:i', strtotime($request->data['departureDateTime']));
        $availableResources = getResourcesAvailable($request->data['resourceTypesId'], $arrivalDateTime, $departureDateTime);
        foreach ($request->data['resourcesId'] as $key => $value) {
            if (! $availableResources->contains('id', $value)) {
                return $response = ['status' => false, 'message' => 'Resources not available'];
            }
        }
        $totalAmount = 0;
        foreach ($request->data['amount'] as $key => $value) {
            $totalAmount += $value;
        }
        $bookingGroup              = new BookingGroups;
        $bookingGroup->userId      = Auth::user()->id;
        $bookingGroup->totalAmount = $totalAmount;
        $bookingGroup->save();
        foreach ($request->data['resourcesId'] as $key => $value) {
            $booking                    = new Booking;
            $booking->userId            = Auth::user()->id;
            $booking->propertyId        = $request->data['propertyId'];
            $booking->bookingGroupId    = $bookingGroup->id;
            $booking->resourcesId       = $value;
            $booking->resourceTypesId   = $request->data['resourceTypesId'][$key];
            $booking->amount            = $request->data['amount'][$key];
            $booking->paymentStatus     = $request->data['paymentStatus'];
            $arrivalDateTime            = $request->data['arrivalDateTime'];
            $departureDateTime          = $request->data['departureDateTime'];
            $booking->arrivalDateTime   = $arrivalDateTime ? date('Y-m-d H:i:s', strtotime($arrivalDateTime)) : null;
            $booking->departureDateTime = $departureDateTime ? date('Y-m-d H:i:s', strtotime($departureDateTime)) : null;
            $booking->save();
        }
        return $response = ['status' => true, 'message' => 'Your Booking successfully'];
    }
}

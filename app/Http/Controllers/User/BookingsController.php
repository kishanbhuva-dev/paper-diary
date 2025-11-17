<?php

namespace App\Http\Controllers\User;

use App\Models\BookingOrder;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use App\Models\Bookings;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\Booking as BookingMail;
use Illuminate\Support\Facades\Mail;

class BookingsController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $bookings = BookingOrder::selectRaw("id,propertyId,resourceTypeId,userId,adult,children,price,status,paymentStatus,date_format(arrivalDateTime,'%d-%m-%Y %H:%i') as arrivalDateTime,date_format(departureDateTime,'%d-%m-%Y %H:%i') as departureDateTime")->where('userId', Auth::user()->id)->with(['property', 'resourceType' => function($query){
                $query->select('*')->withCount('resources');
            }]);  
            if ($request->search) {
                $bookings->whereHas('property', function ($query) use ($request) {
                    $query->where('propertyName', 'like', '%' . $request->search . '%');
                })
                ->orWhere('status', 'like', '%' . $request->search . '%')
                ->orWhere('paymentStatus', $request->search)
                ->orWhere('arrivalDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%')
                ->orWhere('departureDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%')
                ->orWhereHas('resourceType', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('resourceType', function ($query) use ($request) {
                    $query->whereHas('resources', function ($query) use ($request) {
                        $query->where('name', 'like', '%' . $request->search . '%');
                    });
                });
                // ->orWhereHas('user', function ($query) use ($request) {
                //     $query->where('firstName', 'like', '%' . $request->search . '%')
                //         ->orWhere('lastName', 'like', '%' . $request->search . '%')
                //         ->orWhere('email', 'like', '%' . $request->search . '%');
                // });
            }
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';
            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            $bookings->getCollection()->transform(function ($item) {
                $item->resourceTypeName=$item->resourceType->name;
                $item->resourceCount=$item->resourceType->resources_count;
                $item->propertyName=$item->property->propertyName;
                // $item->userName=$item->user->firstName.' '.$item->user->lastName;
                // $item->userEmail=$item->user->email;
                // unset($item->user);
                unset($item->property);
                unset($item->resourceType);

                return $item;
            });
            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
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
                $propertyOwnerEmail = getPropertyOwnerEmail($request->propertyId);
                $data=[];
                $data['bookingId']=$bookingOrderId;
                $data['userName']=Auth::user()->firstName.' '.Auth::user()->lastName;
                $data['arrivalDateTime']=date('d M Y H:i', strtotime($request->arrivalDateTime));
                $data['departureDateTime']=date('d M Y H:i', strtotime($request->departureDateTime));
                $data['propertyName']= getPropertyName($request->propertyId);
                $data['resourceTypeName']= getResourceTypeName($request->resourceTypeId);
                $data['totalAdults']=$request->adults;
                $data['totalChildren']=$request->children ?? 0;
                $data['totalGuests']=$request->adults + ($request->children ?? 0);
                $data['totalPrice']=$request->price;
                $data['guestEmail']=Auth::user()->email;
                $data['guestPhone']=Auth::user()->phone;
                Mail::to(Auth::user()->email)->send(new BookingMail($data, 'user'));
                Mail::to(getPropertyOwnerEmail($request->propertyId))->send(new BookingMail($data, 'owner'));
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
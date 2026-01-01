<?php

namespace App\Http\Controllers\User;

use App\Models\BookingOrder;
use App\Models\ResourceType;
use Illuminate\Http\Request;
use App\Models\Bookings;
use App\Models\Resource;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mail\Booking as BookingMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Property;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Charge;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Payment;

class BookingsController extends Controller
{
    
    public function index(Request $request)
    {
        try {
            $bookings = BookingOrder::selectRaw("id,propertyId,resourceTypeId,userId,adult,children,price,status,paymentStatus,date_format(arrivalDateTime,'%d-%m-%Y %H:%i') as arrivalDateTime,date_format(departureDateTime,'%d-%m-%Y %H:%i') as departureDateTime,created_at")->where('userId', Auth::user()->id)->with(['property', 'resourceType' => function($query){
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
            }
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';
            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            $bookings->getCollection()->transform(function ($item) {
                $item->resourceTypeName=$item->resourceType->name;
                $item->resourceCount=$item->resourceType->resources_count;
                $item->propertyName=$item->property->propertyName;
                $recordedTime = Carbon::parse($item->created_at);
                $item->from=$recordedTime->diffForHumans();
                unset($item->created_at);
                unset($item->property);
                unset($item->resourceType);

                return $item;
            });
            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function booking(Request $request){
        $bookingOrder = BookingOrder::selectRaw("id,userId,propertyId,resourceTypeId,status,date_format(arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(departureDateTime,'%d %b %Y') as departureDateTime")->where('userId', Auth::user()->id);
        if ($request->type=='past') {   
            $bookingOrder->where('arrivalDateTime','<',date('Y-m-d'));
        }else{
            $bookingOrder->where('arrivalDateTime','>=',date('Y-m-d'));
        }
        if ($request->search) {
            $bookingOrder->where(function ($query) use ($request) { $query->whereHas('property', function ($q) use ($request) { $q->where('propertyName', 'like', '%' . $request->search . '%'); }) ->orWhereHas('resourceType', function ($q) use ($request) { $q->where('name', 'like', '%' . $request->search . '%'); }) ->orWhere('arrivalDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%') ->orWhere('departureDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%'); }) ->with([ 'property:id,propertyName', 'resourceType:id,name' ])
            ->orWhere('arrivalDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%')
            ->orWhere('departureDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%');            
        }else{
            $bookingOrder->with(['property' => function($query){
                $query->select('id','propertyName');
            },'resourceType' => function($query){
                $query->select('id','name');
            }]);
        }
        if ($request->status) {
            $bookingOrder->where('status', $request->status);
        }
        if ($request->arrivalDateTime && $request->departureDateTime) {
            $arrivalDate = Carbon::parse($request->arrivalDateTime)->format('Y-m-d');
            $departureDate = Carbon::parse($request->departureDateTime)->format('Y-m-d');
            $bookingOrder->where('arrivalDateTime', '>=', $arrivalDate)
                         ->where('departureDateTime', '<=', $departureDate);
        }
        $pagination = $request->pagination ?? 10;
        $sortBy = $request->sortBy ?? 'id';
        $sortOrder = $request->sortOrder ?? 'desc';
        $bookingOrder = $bookingOrder->orderBy($sortBy, $sortOrder)->paginate($pagination);
        return response()->json(['status' => true, 'message' => '', 'data' => $bookingOrder]);
    }
    public function bookingStatusUpdate(Request $request){
        try {
            $validator = Validator::make($request->all(), [
            'bookingId' => 'required|exists:booking_orders,id',
            'status' => 'required|in:pending,cancelled,confirm',
            'payment_intent_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }

            $bookingOrder = BookingOrder::where('id',$request->bookingId)->first();
            $booking = Bookings::where('bookingOrderId',$request->bookingId)->get();
            foreach ($booking as $key => $value) {
                if($request->status=='confirm'){
                    $value->status = 'confirmed';
                }elseif($request->status=='cancelled'){  
                    $value->status = $request->status;
                }else{
                    $value->status = 'pending';
                }
                $value->save();
            }
            
            $bookingOrder->status = $request->status;
            if ($request->status =='pending') {
                $bookingOrder->paymentStatus = 'unpaid';
            }elseif ($request->status =='cancelled') {
                $bookingOrder->paymentStatus = 'failed';
            }else{
                $bookingOrder->paymentStatus = 'paid';
            }
            $bookingOrder->save();
            $bookingOrderId = $bookingOrder->propertyId;
            $property = Property::where('id',$bookingOrderId)->first();
            if ($property->stripeSecretKey) {
                $ownerStripeSecret = $property->stripeSecretKey;
            }else {
                $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
            }
            $stripe = new \Stripe\StripeClient($ownerStripeSecret);
            $paymentDetail = $stripe->paymentIntents->retrieve($request->payment_intent_id);
            $paymentDetail->status;
            if ($paymentDetail->status == 'succeeded') {
                $payment = new Payment();
                $payment->transaction_id = $paymentDetail->id;
                $payment->userId = Auth::user()->id;
                $payment->bookingOrderId = $request->bookingId;
                $payment->save();
            }
            return response()->json(['status' => true, 'message' => 'Your booking is confirmed we send you a confirmation email', 'data' => []]);
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
                'status' => 'nullable|in:pending,cancelled,confirm',
                'paymentStatus' => 'nullable|in:paid,unpaid,failed,cancelled,confirm',
                'resources' => 'required|integer',
                'guestFullName' => 'required|string',
                'guestEmail' => 'required|email',
                'guestPhone' => 'required|string',
                'guestAddress' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $availableResourcesData = getResourcesAvailable($request->resourceTypeId, $request->arrivalDateTime, $request->departureDateTime);
            if ($availableResourcesData->count() < $request->resources) {
                return response()->json(['status' => false, 'message' => 'resources not available', 'data' => []]);
            }
            $resourcesToBookIds = $availableResourcesData->take((int)$request->resources);
            $resourcesToBook = Resource::whereIn('id', $resourcesToBookIds)->get();
            $resourceType = ResourceType::where('id', $request->resourceTypeId)->first();
            $resourcePriceTotal = 0;
            foreach ($resourcesToBook as $resource) {
                $resourcePriceTotal += $resource->customPrice ?? $resourceType->price;
            }
            $booking = new BookingOrder;
            $booking->propertyId = $request->propertyId;
            $booking->resourceTypeId = $request->resourceTypeId;
            $booking->arrivalDateTime = $request->arrivalDateTime? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
            $booking->departureDateTime = $request->departureDateTime? date('Y-m-d', strtotime($request->departureDateTime)) : null;
            $booking->adult = $request->adults;
            $booking->guestFullName = $request->guestFullName;
            $booking->guestEmail = $request->guestEmail;
            $booking->guestPhone = $request->guestPhone;
            $booking->guestAddress = $request->guestAddress;
            if (isset($request->children)) {
                $booking->children = $request->children;
            }
            $booking->price = $resourcePriceTotal;
            $booking->cost = $resourcePriceTotal;
            $booking->userId = Auth::user()->id;
            if (isset($request->status)) {
                $booking->status = $request->status;
            }
            if (isset($request->paymentStatus)) {
                $booking->paymentStatus = $request->paymentStatus;
            }
            if ($booking->save()) {
                $bookingOrderId = $booking->id;
                foreach ($resourcesToBook as $resource) {
                    $resourcePrice = $resource->customPrice ?? $resourceType->price;
                    $booking = new Bookings;
                    $booking->bookingOrderId = $bookingOrderId;
                    $booking->resourceId = $resource->id;
                    $booking->resourceTypeId = $request->resourceTypeId;
                    $booking->arrivalDateTime = $request->arrivalDateTime ? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
                    $booking->departureDateTime = $request->departureDateTime ? date('Y-m-d', strtotime($request->departureDateTime)) : null;
                    $booking->price = $resourcePrice;
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
                return response()->json(['status' => true, 'message' => '', 'data' => ['id' => $bookingOrderId]], 201);
            }else {
                return response()->json(['status' => false, 'message' => 'Failed to your booking', 'data' => []], 500);
            }
        }catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function propertyDetails(Request $request)
    {
        $property = Property::where('slug', $request->slug)->with(['owner','resourceTypes','facilities','propertyImage'=>function($query) {
            $query->orderBy('position','asc');
        },'resourceTypes.resources'=>function($query){
            $query->select('id','name','customPrice','status')->where('status',1);
        }])->first();
        if ($property) {
            $property->propertyImage->transform(function ($item) {
                $item->image = asset('storage/property/images/' . $item->image); 
                return $item;
            });
            $property->ownerName = $property->owner->firstName . ' ' . $property->owner->lastName;
            unset($property->owner);
            return response()->json(['status' => true, 'message' => '', 'data' => $property]);
        } else {
            return response()->json(['status' => false, 'message' => 'Property not found', 'data' => []]);
        }
    }
    public function getAvailableResourcesTypes(Request $request) 
    { 
        try { 
            $validator = Validator::make($request->all(), [ 
                'slug' => 'required|exists:property,slug', 
                'arrivalDateTime' => 'required|date', 
                'departureDateTime' => 'required|date|after:arrivalDateTime', 
                'totalResources' => 'required|integer', 
            ]); 
            if ($validator->fails()) { 
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]); 
            }
            $property = Property::where('slug', $request->slug)
                ->with(['resourceTypes.resources' => function($query) {
                    $query->select('id','name','customPrice','status')->where('status',1);
                }])->first(); 

            if (!$property) { 
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => []], 404); 
            } 

            $arrivalDateTime = date('Y-m-d', strtotime($request->arrivalDateTime)); 
            $departureDateTime = date('Y-m-d', strtotime($request->departureDateTime)); 
            $availableResourcesData = getResourcesTypeAvailableByProperty($property->id, $arrivalDateTime, $departureDateTime, $request->totalResources); 
            
            return response()->json(['status' => true, 'message' => '', 'data' => $availableResourcesData]); 
        } catch (\Throwable $th) { 
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]); 
        } 
    }    
    public function bookingCancel(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'bookingId' => 'required|exists:booking_orders,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $bookingOrder = BookingOrder::where('id',$request->bookingId)->first();
            if ($bookingOrder->userId != Auth::user()->id) {
                return response()->json(['status' => false, 'message' => 'you are not authorized to cancel this booking', 'data' => []]);
            }
            $booking = Bookings::where('bookingOrderId',$request->bookingId)->get();
            foreach ($booking as $key => $value) {
                $value->status = 'cancelled';
                $value->save();
            }
            $bookingOrder->status = 'cancelled';
            $bookingOrder->paymentStatus = 'failed';
            $bookingOrder->save();
            return response()->json(['status' => true, 'message' => 'Booking cancelled successfully', 'data' => []]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'   => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'slug' => 'required|exists:property,slug',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'data'    => []
            ]);
        }
        $property = Property::where('slug', $request->slug)->first();
        if ($property->stripeSecretKey) {
            $ownerStripeSecret = $property->stripeSecretKey;
        }else {
            $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
        }
        if (!$property->stripeSecretKey && !$ownerStripeSecret) {
            return response()->json([
                'status'  => false,
                'message' => 'You can not book this property',
                'data'    => []
            ]);
        }
        Stripe::setApiKey($ownerStripeSecret);
        $intent = PaymentIntent::create([
            'amount'               => $request->amount * 100,
            'currency'             => $request->currency,
            'payment_method_types' => ['card'],
            'metadata'             => [
                'user_id'  => auth()->id(),
                'order_id' => $request->order_id ?? null,
            ],
        ]);
        return response()->json([
            'status'             => true,
            'message'            => '',
            'clientSecret'       => $intent->client_secret,
            'payment_intent_id'  => $intent->id,
            'token'              => (string) Str::uuid(),
        ]);
    }
    public function completePayment(Request $request)
    {
        if (empty($request->payment_intent_id)) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment Intent ID is required',
                'data'    => []
            ]);
        }
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $paymentDetail = $stripe->paymentIntents->retrieve($request->payment_intent_id);
            if ($paymentDetail->status === 'succeeded') {
                return response()->json([
                    'status'  => true,
                    'message' => 'Payment Successfully Complete',
                    'data'    => [],
                ]);
            }
            return response()->json([
                'status'  => false,
                'message' => 'Payment not completed yet',
                'data'    => $paymentDetail,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => []
            ]);
        }
    }
}
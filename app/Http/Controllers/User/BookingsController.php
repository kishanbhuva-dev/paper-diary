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
use Stripe\Charge;
use App\Models\User;
use Illuminate\Support\Str;
use App\Models\Payment;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe as StripeGateway;
use Stripe\Stripe;
use Stripe\StripeClient;

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
        // try {
            $validator = Validator::make($request->all(), [
            'bookingId' => 'required|exists:booking_orders,id',
            'payment_intent_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }

            $bookingOrder = BookingOrder::where('id',$request->bookingId)->first();
            $property = Property::where('id', $bookingOrder->propertyId)->first();
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
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $booking = Bookings::where('bookingOrderId',$request->bookingId)->get();
            $paymentIntentId = $request->payment_intent_id;
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
            if ($paymentIntent->status=='succeeded') {
                foreach ($booking as $key => $value) {
                    $value->status = 'confirmed';
                    $value->save();
                }
                $bookingOrder->status = 'confirm';
                $bookingOrder->paymentStatus = 'paid';
                $bookingOrder->save();   
            }else{
                $bookingOrder->paymentStatus = 'cancelled';
                $bookingOrder->status = 'cancelled';
                $bookingOrder->save();   
            }            
            $bookingOrderId = $bookingOrder->propertyId;
            
            $stripe = new \Stripe\StripeClient($ownerStripeSecret);
            $paymentDetail = $stripe->paymentIntents->retrieve($request->token);
            if ($paymentDetail->status == 'succeeded') {
                $payment = new Payment();
                $payment->transaction_id = $paymentDetail->id;
                $payment->userId = Auth::user()->id;
                $payment->bookingOrderId = $request->bookingId;
                $payment->save();
            }
            return response()->json(['status' => true, 'message' => 'Your booking is confirmed we send you a confirmation email', 'data' => []]);
        // } catch (\Throwable $th) {
        //     return response()->json(['status' => false, 'message1' => $th->getMessage(), 'data' => []]);
        // }
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
                'additionalInformation' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $property = Property::where('id', $request->propertyId)->first();
            if ($property->stripePublicKey) {
                $ownerStripePublicKey = $property->stripePublicKey;
            }else {
                $ownerStripePublicKey = User::where('id', $property->ownerId)->value('stripePublicKey');
            }
            if (empty($property->stripePublicKey) && empty($ownerStripePublicKey)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You can not book this property',
                    'data'    => []
                ]);
            }
            $availableResourcesData = getResourcesAvailable($request->resourceTypeId, $request->arrivalDateTime, $request->departureDateTime);
            if ($availableResourcesData->count() < $request->resources) {
                return response()->json(['status' => false, 'message' => 'Resources not available for the selected dates and quantity', 'data' => []]);
            }
            $resourcesToBookIds = $availableResourcesData->take((int)$request->resources);
            $resourcesToBook = Resource::whereIn('id', $resourcesToBookIds)->get();
            $resourceType = ResourceType::where('id', $request->resourceTypeId)->first();
            $resourcePriceTotal = 0;
            foreach ($resourcesToBook as $resource) {
                $resourcePriceTotal += $resource->customPrice ?? $resourceType->price;
            }
            $bookingOrder = new BookingOrder;
            $bookingOrder->propertyId = $request->propertyId;
            $bookingOrder->resourceTypeId = $request->resourceTypeId;
            $bookingOrder->arrivalDateTime = $request->arrivalDateTime? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
            $bookingOrder->departureDateTime = $request->departureDateTime? date('Y-m-d', strtotime($request->departureDateTime)) : null;
            $bookingOrder->adult = $request->adults;
            $bookingOrder->guestFullName = $request->guestFullName;
            $bookingOrder->guestEmail = $request->guestEmail;
            $bookingOrder->guestPhone = $request->guestPhone;
            $bookingOrder->guestAddress = $request->guestAddress;
            $bookingOrder->additionalInformation = $request->additionalInformation;            
            if (isset($request->children)) {
                $bookingOrder->children = $request->children;
            }
            $bookingOrder->price = $resourcePriceTotal;
            $bookingOrder->cost = $resourceType->price * $request->nightsCount;
            $bookingOrder->userId = Auth::user()->id;
            if (isset($request->status)) {
                $bookingOrder->status = $request->status;
            }
            if (isset($request->paymentStatus)) {
                $bookingOrder->paymentStatus = $request->paymentStatus;
            }
            if ($bookingOrder->save()) {
                $bookingOrderId = $bookingOrder->id;
                foreach ($resourcesToBook as $resource) {
                    $resourcePrice = $resource->customPrice ?? $resourceType->price;
                    $bookingItem = new Bookings;
                    $bookingItem->bookingOrderId = $bookingOrderId;
                    $bookingItem->resourceId = $resource->id;
                    $bookingItem->resourceTypeId = $request->resourceTypeId;
                    $bookingItem->arrivalDateTime = $request->arrivalDateTime ? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
                    $bookingItem->departureDateTime = $request->departureDateTime ? date('Y-m-d', strtotime($request->departureDateTime)) : null;
                    $bookingItem->price = $resourcePrice;
                    $bookingItem->save();
                }
                $userData = [];
                $userData['bookingId'] = $bookingOrderId;
                $userData['userName'] = Auth::user()->firstName.' '.Auth::user()->lastName;
                $userData['arrivalDateTime'] = date('d M Y', strtotime($request->arrivalDateTime));
                $userData['departureDateTime'] = date('d M Y', strtotime($request->departureDateTime));
                $userData['propertyName'] = getPropertyName($request->propertyId);
                $userData['resourceTypeName'] = getResourceTypeName($request->resourceTypeId);
                $userData['totalAdults'] = $request->adults;
                $userData['totalChildren'] = $request->children ?? 0;
                $userData['totalGuests'] = $request->adults + ($request->children ?? 0);
                $userData['totalPrice'] = $resourcePriceTotal;
                $userData['guestEmail'] = $request->guestEmail;
                $userData['guestPhone'] = $request->guestPhone;
                $userData['guestAddress'] = $request->guestAddress;
                $userData['additionalInformation'] = $request->additionalInformation;              
                $arrival = Carbon::parse($request->arrivalDateTime);
                $departure = Carbon::parse($request->departureDateTime);
                $interval = $arrival->diffInDays($departure);
                $userData['totalNights'] = $interval;
                
                $resourceNames = $resourcesToBook->pluck('name')->implode(', ');
                $userData['resourceNames'] = $resourceNames;
                
                $property = Property::where('id', $request->propertyId)->with('owner')->first();
                $userData['ownerName'] = $property && $property->owner ? $property->owner->firstName . ' ' . $property->owner->lastName : 'Property Owner';
                
                $ownerData = [];
                $ownerData['bookingId'] = $bookingOrderId;
                $ownerData['userName'] = $request->guestFullName;
                $ownerData['guestEmail'] = $request->guestEmail;
                $ownerData['guestPhone'] = $request->guestPhone;
                $ownerData['arrivalDateTime'] = date('d M Y', strtotime($request->arrivalDateTime));
                $ownerData['departureDateTime'] = date('d M Y', strtotime($request->departureDateTime));
                $ownerData['propertyName'] = getPropertyName($request->propertyId);
                $ownerData['resourceTypeName'] = getResourceTypeName($request->resourceTypeId);
                $ownerData['totalAdults'] = $request->adults;
                $ownerData['totalChildren'] = $request->children ?? 0;
                $ownerData['totalGuests'] = $request->adults + ($request->children ?? 0);
                $ownerData['totalPrice'] = $resourcePriceTotal;
                $ownerData['bookingUser'] = Auth::user()->firstName.' '.Auth::user()->lastName;
                $ownerData['bookingUserEmail'] = Auth::user()->email;
                $ownerData['totalNights'] = $interval;
                $ownerData['resourceNames'] = $resourceNames;
                $ownerData['ownerName'] = $property && $property->owner ? $property->owner->firstName . ' ' . $property->owner->lastName : 'Property Owner';
                if (config('mail.default') && config('mail.mailers.' . config('mail.default'))) {
                    $userEmail = Auth::user()->email;
                    $ownerEmail = getPropertyOwnerEmail($request->propertyId);
                    
                    if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($userEmail)->send(new BookingMail($userData, 'user'));
                    }                    
                    if ($ownerEmail && filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
                        Mail::to($ownerEmail)->send(new BookingMail($ownerData, 'owner'));
                    }
                }
                if ($property->stripeSecretKey) {
                    $ownerStripeSecret = $property->stripeSecretKey;
                } else {
                    $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
                }
                if (empty($property->stripeSecretKey) && empty($ownerStripeSecret)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'You can not book this property',
                        'data'    => []
                    ]);
                }
                Stripe::setApiKey($ownerStripeSecret);
                $existingCustomers = Customer::all(['email' => Auth::user()->email]);
                if (count($existingCustomers->data) > 0) {
                    $customer = $existingCustomers->data[0];
                } else {
                    // Create a new customer if not exists
                    $customer = Customer::create([
                        'email' => Auth::user()->email,
                        'name'  => Auth::user()->name,
                    ]);
                }
                Stripe::setApiKey($ownerStripeSecret);

                $paymentIntent = PaymentIntent::create([
                'amount'                    => $bookingOrder->price * 100,
                'currency'                  => "GBP",
                'customer'                  => $customer->id,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                "metadata"=>["registrationId"=>$bookingOrderId]
                ]);
                $response = [
                    'status'       => true,
                    'bookingId'   => $bookingOrderId,
                    'token'        => (string) $paymentIntent->id,
                    'clientSecret' => $paymentIntent->client_secret,
                    'publishable'  => base64_encode($ownerStripePublicKey),
                    "total"        => $bookingOrder->price,
                ];
                return response()->json($response);
            }else {
                return response()->json(['status' => false, 'message' => 'Failed to your booking', 'data' => []], 500);
            }
        }catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function propertyDetails(Request $request)
    {
        $property = Property::selectRaw("id,ownerId,propertyName,address,address2,city,country,postcode,telephone,phone,latitude,longitude,description,slug,county")->where('slug', $request->slug)->with(['owner','resourceTypes','facilities','propertyImage'=>function($query) {
            $query->orderBy('position','asc');
        },'resourceTypes.resources'=>function($query){
            $query->select('id','resourceTypeId','name','customPrice','status')->where('status',1);
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
            if (!$property && $request->booking=='confirm') { 
                return response()->json(['status' => false, 'message' => 'You cannot book this resource,Because this resource type already booked', 'data' => []], 404); 
            } 
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
    public function bookingDelete(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'bookingId' => 'required|exists:booking_orders,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $bookingOrder = BookingOrder::where('id',$request->bookingId)->first();
            if ($bookingOrder->userId != Auth::user()->id) {
                return response()->json(['status' => false, 'message' => 'you are not authorized to delete this booking', 'data' => []]);
            }
            Bookings::where('bookingOrderId',$request->bookingId)->delete();
            $bookingOrder->delete();
            return response()->json(['status' => true, 'message' => '', 'data' => []]);
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
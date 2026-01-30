<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Mail\Booking as BookingMail;
use App\Models\BookingOrder;
use App\Models\Bookings;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stripe\Customer;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use Throwable;

class BookingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $bookings = BookingOrder::selectRaw("id,propertyId,resourceTypeId,userId,adult,children,price,status,paymentStatus,date_format(arrivalDateTime,'%d-%m-%Y %H:%i') as arrivalDateTime,date_format(departureDateTime,'%d-%m-%Y %H:%i') as departureDateTime,created_at")->where('userId', Auth::user()->id)->with(['property', 'resourceType' => function ($query) {
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
                $item->resourceTypeName = $item->resourceType->name;
                $item->resourceCount = $item->resourceType->resources_count;
                $item->propertyName = $item->property->propertyName;
                $recordedTime = Carbon::parse($item->created_at);
                $item->from = $recordedTime->diffForHumans();
                unset($item->created_at);
                unset($item->property);
                unset($item->resourceType);

                return $item;
            });

            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function booking(Request $request): JsonResponse
    {
        $bookingOrder = BookingOrder::selectRaw("id,userId,propertyId,resourceTypeId,status,date_format(arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(departureDateTime,'%d %b %Y') as departureDateTime")->where('userId', Auth::user()->id);
        if ($request->type == 'past') {
            $bookingOrder->where('arrivalDateTime', '<', date('Y-m-d'));
        } else {
            $bookingOrder->where('arrivalDateTime', '>=', date('Y-m-d'));
        }
        if ($request->search) {
            $bookingOrder->where(function ($query) use ($request) {
                $query->whereHas('property', function ($q) use ($request) {
                    $q->where('propertyName', 'like', '%' . $request->search . '%');
                })->orWhereHas('resourceType', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })->orWhere('arrivalDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%')->orWhere('departureDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%');
            })->with(['property:id,propertyName', 'resourceType:id,name'])
                ->orWhere('arrivalDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%')
                ->orWhere('departureDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%');
        } else {
            $bookingOrder->with(['property' => function ($query) {
                $query->select('id', 'propertyName');
            }, 'resourceType' => function ($query) {
                $query->select('id', 'name');
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
        } elseif ($request->arrivalDateTime) {
            $arrivalDate = Carbon::parse($request->arrivalDateTime)->format('Y-m-d');
            $bookingOrder->where('arrivalDateTime', '>=', $arrivalDate);
        } elseif ($request->departureDateTime) {
            $departureDate = Carbon::parse($request->departureDateTime)->format('Y-m-d');
            $bookingOrder->where('departureDateTime', '<=', $departureDate);
        }
        $pagination = $request->pagination ?? 10;
        $sortBy = $request->sortBy ?? 'arrivalDateTime';
        $sortOrder = $request->sortOrder ?? 'desc';
        // Handle sorting by related columns
        if ($sortBy === 'property.propertyName') {
            $bookingOrder->leftJoin('property', 'booking_orders.propertyId', '=', 'property.id')
                ->orderBy('property.propertyName', $sortOrder)
                ->select('booking_orders.*', 'property.propertyName');
        } elseif ($sortBy === 'resource_type.name') {
            $bookingOrder->leftJoin('resource_types', 'booking_orders.resourceTypeId', '=', 'resource_types.id')
                ->orderBy('resource_types.name', $sortOrder)
                ->select('booking_orders.*', 'resource_types.name as resourceTypeName');
        } else {
            $bookingOrder->orderBy($sortBy, $sortOrder);
        }

        $bookingOrder = $bookingOrder->paginate($pagination);

        return response()->json(['status' => true, 'message' => '', 'data' => $bookingOrder]);
    }

    public function bookingStatusUpdate(Request $request): JsonResponse
    {
        // try {
        $validator = Validator::make($request->all(), [
            'bookingId'         => 'required|exists:booking_orders,id',
            'payment_intent_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
        }

        $bookingOrder = BookingOrder::where('id', $request->bookingId)->first();
        $property = Property::where('id', $bookingOrder->propertyId)->first();
        if ($property->stripeSecretKey) {
            $ownerStripeSecret = $property->stripeSecretKey;
        } else {
            $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
        }
        if (! $property->stripeSecretKey && ! $ownerStripeSecret) {
            return response()->json([
                'status'  => false,
                'message' => 'You can not book this property',
                'data'    => [],
            ]);
        }
        $stripe = new StripeClient($ownerStripeSecret);
        $booking = Bookings::where('bookingOrderId', $request->bookingId)->get();
        $paymentIntentId = $request->payment_intent_id;
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);
        if ($paymentIntent->status == 'succeeded') {
            foreach ($booking as $key => $value) {
                $value->status = 'confirmed';
                $value->save();
            }
            $bookingOrder->status = 'confirm';
            $bookingOrder->paymentStatus = 'paid';
            $bookingOrder->save();

            $userData = [];
            $userData['bookingId'] = $bookingOrder->id;
            $userData['userName'] = Auth::user()->firstName . ' ' . Auth::user()->lastName;
            $userData['arrivalDateTime'] = date('d M Y', strtotime($bookingOrder->arrivalDateTime));
            $userData['departureDateTime'] = date('d M Y', strtotime($bookingOrder->departureDateTime));
            $userData['propertyName'] = getPropertyName($bookingOrder->propertyId);
            $userData['resourceTypeName'] = getResourceTypeName($bookingOrder->resourceTypeId);
            $userData['totalAdults'] = $bookingOrder->adult;
            $userData['totalChildren'] = $bookingOrder->children ?? 0;
            $userData['totalGuests'] = $bookingOrder->adult + ($bookingOrder->children ?? 0);
            $userData['totalPrice'] = $bookingOrder->price;
            $userData['guestEmail'] = $bookingOrder->guestEmail;
            $userData['guestPhone'] = $bookingOrder->guestPhone;
            $userData['guestAddress'] = $bookingOrder->guestAddress;
            $userData['additionalInformation'] = $bookingOrder->additionalInformation ?? '';
            $arrival = Carbon::parse($bookingOrder->arrivalDateTime);
            $departure = Carbon::parse($bookingOrder->departureDateTime);
            $interval = $arrival->diffInDays($departure);
            $userData['totalNights'] = $interval;

            $existingBookings = Bookings::where('bookingOrderId', $bookingOrder->id)->with('resource')->get();
            $userData['resourceNames'] = $existingBookings->pluck('resource.name')->implode(', ');

            $property = Property::where('id', $bookingOrder->propertyId)->with('owner')->first();
            $userData['ownerName'] = $property->owner ? $property->owner->firstName . ' ' . $property->owner->lastName : 'Property Owner';

            $ownerData = [];
            $ownerData['bookingId'] = $bookingOrder->id;
            $ownerData['bookingOrderId'] = $bookingOrder->Id;
            $ownerData['userName'] = $bookingOrder->guestFullName;
            $ownerData['guestEmail'] = $bookingOrder->guestEmail;
            $ownerData['guestPhone'] = $bookingOrder->guestPhone;
            $ownerData['arrivalDateTime'] = date('d M Y', strtotime($bookingOrder->arrivalDateTime));
            $ownerData['departureDateTime'] = date('d M Y', strtotime($bookingOrder->departureDateTime));
            $ownerData['propertyName'] = getPropertyName($bookingOrder->propertyId);
            $ownerData['resourceTypeName'] = getResourceTypeName($bookingOrder->resourceTypeId);
            $ownerData['totalAdults'] = $bookingOrder->adult;
            $ownerData['totalChildren'] = $bookingOrder->children ?? 0;
            $ownerData['totalGuests'] = $bookingOrder->adult + ($bookingOrder->children ?? 0);
            $ownerData['totalPrice'] = $bookingOrder->price;
            $ownerData['bookingUser'] = Auth::user()->firstName . ' ' . Auth::user()->lastName;
            $ownerData['bookingUserEmail'] = Auth::user()->email;
            $ownerData['totalNights'] = $interval;
            $ownerData['resourceNames'] = $existingBookings->pluck('resource.name')->implode(', ');
            $ownerData['ownerName'] = $property->owner ? $property->owner->firstName . ' ' . $property->owner->lastName : 'Property Owner';

            if (config('mail.default') && config('mail.mailers.' . config('mail.default'))) {
                $userEmail = Auth::user()->email;
                $ownerEmail = getPropertyOwnerEmail($bookingOrder->propertyId);

                if ($userEmail && filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($userEmail)->send(new BookingMail($userData, 'user'));
                }
                if ($ownerEmail && filter_var($ownerEmail, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($ownerEmail)->send(new BookingMail($ownerData, 'owner'));
                }
            }
        } else {
            $bookingOrder->paymentStatus = 'cancelled';
            $bookingOrder->status = 'cancelled';
            $bookingOrder->save();
        }
        $bookingOrderId = $bookingOrder->id;

        $stripe = new StripeClient($ownerStripeSecret);
        $paymentDetail = $stripe->paymentIntents->retrieve($request->token);
        if ($paymentDetail->status == 'succeeded') {
            $payment = new Payment;
            $payment->transaction_id = $paymentDetail->id;
            $payment->userId = Auth::user()->id;
            $payment->bookingOrderId = $bookingOrderId;
            $payment->save();
        }

        return response()->json(['status' => true, 'message' => 'Your booking is confirmed we send you a confirmation email', 'data' => []]);
        // } catch (\Throwable $th) {
        //     return response()->json(['status' => false, 'message1' => $th->getMessage(), 'data' => []]);
        // }
    }

    // public function store(Request $request): JsonResponse
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'propertyId'            => 'required|exists:property,id',
    //             'resourceTypeId'        => 'required|exists:resource_types,id',
    //             'arrivalDateTime'       => 'required|date',
    //             'departureDateTime'     => 'required|date|after:arrivalDateTime',
    //             'adults'                => 'required|integer',
    //             'children'              => 'nullable|integer',
    //             'status'                => 'nullable|in:pending,cancelled,confirm',
    //             'paymentStatus'         => 'nullable|in:paid,unpaid,failed,cancelled,confirm',
    //             'resources'             => 'required|integer',
    //             'guestFullName'         => 'required|string',
    //             'guestEmail'            => 'required|email',
    //             'guestPhone'            => 'required|string',
    //             'guestAddress'          => 'required|string',
    //             'additionalInformation' => 'nullable|string',
    //         ]);
    //         if ($validator->fails()) {
    //             return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
    //         }
    //         $property = Property::where('id', $request->propertyId)->first();
    //         if ($property->stripePublicKey) {
    //             $ownerStripePublicKey = $property->stripePublicKey;
    //         } else {
    //             $ownerStripePublicKey = User::where('id', $property->ownerId)->value('stripePublicKey');
    //         }
    //         if (empty($property->stripePublicKey) && empty($ownerStripePublicKey)) {
    //             return response()->json([
    //                 'status'  => false,
    //                 'message' => 'You can not book this property',
    //                 'data'    => [],
    //             ]);
    //         }
    //         $availableResourcesData = getResourcesAvailable($request->resourceTypeId, $request->arrivalDateTime, $request->departureDateTime);
    //         if ($availableResourcesData->count() < $request->resources) {
    //             return response()->json(['status' => false, 'message' => 'Resources not available for the selected dates and quantity', 'data' => []]);
    //         }
    //         $resourcesToBookIds = $availableResourcesData->take((int) $request->resources);
    //         $resourcesToBook = Resource::whereIn('id', $resourcesToBookIds)->get();
    //         $resourceType = ResourceType::where('id', $request->resourceTypeId)->first();
    //         $resourcePriceTotal = 0;
    //         foreach ($resourcesToBook as $resource) {
    //             $resourcePriceTotal += $resource->customPrice ?? $resourceType->price;
    //         }
    //         $bookingOrder = new BookingOrder;
    //         $bookingOrder->propertyId = $request->propertyId;
    //         $bookingOrder->resourceTypeId = $request->resourceTypeId;
    //         $bookingOrder->arrivalDateTime = $request->arrivalDateTime ? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
    //         $bookingOrder->departureDateTime = $request->departureDateTime ? date('Y-m-d', strtotime($request->departureDateTime)) : null;
    //         $bookingOrder->adult = $request->adults;
    //         $bookingOrder->guestFullName = $request->guestFullName;
    //         $bookingOrder->guestEmail = $request->guestEmail;
    //         $bookingOrder->guestPhone = $request->guestPhone;
    //         $bookingOrder->guestAddress = $request->guestAddress;
    //         $bookingOrder->additionalInformation = $request->additionalInformation;
    //         if (isset($request->children)) {
    //             $bookingOrder->children = $request->children;
    //         }
    //         $bookingOrder->price = $resourcePriceTotal;
    //         $bookingOrder->cost = $resourceType->price * $request->nightsCount;
    //         $bookingOrder->userId = Auth::user()->id;
    //         if (isset($request->status)) {
    //             $bookingOrder->status = $request->status;
    //         }
    //         if (isset($request->paymentStatus)) {
    //             $bookingOrder->paymentStatus = $request->paymentStatus;
    //         }
    //         if ($bookingOrder->save()) {
    //             $bookingOrderId = $bookingOrder->id;
    //             foreach ($resourcesToBook as $resource) {
    //                 $resourcePrice = $resource->customPrice ?? $resourceType->price;
    //                 $bookingItem = new Bookings;
    //                 $bookingItem->bookingOrderId = $bookingOrderId;
    //                 $bookingItem->resourceId = $resource->id;
    //                 $bookingItem->resourceTypeId = $request->resourceTypeId;
    //                 $bookingItem->arrivalDateTime = $request->arrivalDateTime ? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
    //                 $bookingItem->departureDateTime = $request->departureDateTime ? date('Y-m-d', strtotime($request->departureDateTime)) : null;
    //                 $bookingItem->price = $resourcePrice;
    //                 $bookingItem->save();
    //             }

    //             if ($property->stripeSecretKey) {
    //                 $ownerStripeSecret = $property->stripeSecretKey;
    //             } else {
    //                 $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
    //             }
    //             if (empty($property->stripeSecretKey) && empty($ownerStripeSecret)) {
    //                 return response()->json([
    //                     'status'  => false,
    //                     'message' => 'You can not book this property',
    //                     'data'    => [],
    //                 ]);
    //             }
    //             Stripe::setApiKey($ownerStripeSecret);
    //             $existingCustomers = Customer::all(['email' => Auth::user()->email]);
    //             if (count($existingCustomers->data) > 0) {
    //                 $customer = $existingCustomers->data[0];
    //             } else {
    //                 // Create a new customer if not exists
    //                 $customer = Customer::create([
    //                     'email' => Auth::user()->email,
    //                     'name'  => Auth::user()->name,
    //                 ]);
    //             }
    //             Stripe::setApiKey($ownerStripeSecret);

    //             $paymentIntent = PaymentIntent::create([
    //                 'amount'                    => (int) ($bookingOrder->price * 100),
    //                 'currency'                  => 'GBP',
    //                 'customer'                  => $customer->id,
    //                 'automatic_payment_methods' => [
    //                     'enabled' => true,
    //                 ],
    //                 'metadata' => [
    //                     'registrationId' => (string) $bookingOrderId,
    //                 ],
    //             ]);

    //             $response = [
    //                 'status'       => true,
    //                 'bookingId'    => $bookingOrderId,
    //                 'token'        => (string) $paymentIntent->id,
    //                 'clientSecret' => $paymentIntent->client_secret,
    //                 'publishable'  => base64_encode($ownerStripePublicKey),
    //                 'total'        => $bookingOrder->cost * 100,
    //             ];

    //             return response()->json($response);
    //         }

    //         return response()->json(['status' => false, 'message' => 'Failed to your booking', 'data' => []], 500);
    //     } catch (Throwable $th) {
    //         return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
    //     }
    // }
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'propertyId'            => 'required|exists:property,id',
                'resourceTypeId'        => 'required|exists:resource_types,id',
                'arrivalDateTime'       => 'required|date',
                'departureDateTime'     => 'required|date|after:arrivalDateTime',
                'adults'                => 'required|integer',
                'children'              => 'nullable|integer',
                'status'                => 'nullable|in:pending,cancelled,confirm',
                'paymentStatus'         => 'nullable|in:paid,unpaid,failed,cancelled,confirm',
                'resources'             => 'required|integer',
                'guestFullName'         => 'required|string',
                'guestEmail'            => 'required|email',
                'guestPhone'            => 'required|string',
                'guestAddress'          => 'required|string',
                'additionalInformation' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }

            // Use database transaction to ensure data integrity
            return DB::transaction(function () use ($request) {
                $property = Property::where('id', $request->propertyId)->first();
                if ($property->stripePublicKey) {
                    $ownerStripePublicKey = $property->stripePublicKey;
                } else {
                    $ownerStripePublicKey = User::where('id', $property->ownerId)->value('stripePublicKey');
                }
                if (empty($property->stripePublicKey) && empty($ownerStripePublicKey)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'You can not book this property',
                        'data'    => [],
                    ]);
                }
                $availableResourcesData = getResourcesAvailable($request->resourceTypeId, $request->arrivalDateTime, $request->departureDateTime);
                if ($availableResourcesData->count() < $request->resources) {
                    return response()->json(['status' => false, 'message' => 'Resources not available for the selected dates and quantity', 'data' => []]);
                }
                $resourcesToBookIds = $availableResourcesData->take((int) $request->resources);
                $resourcesToBook = Resource::whereIn('id', $resourcesToBookIds)->get();
                $resourceType = ResourceType::where('id', $request->resourceTypeId)->first();
                $resourcePriceTotal = 0;
                foreach ($resourcesToBook as $resource) {
                    $resourcePriceTotal += $resource->customPrice ?? $resourceType->price;
                }
                $bookingOrder = new BookingOrder;
                $bookingOrder->propertyId = $request->propertyId;
                $bookingOrder->resourceTypeId = $request->resourceTypeId;
                $bookingOrder->arrivalDateTime = $request->arrivalDateTime ? date('Y-m-d', strtotime($request->arrivalDateTime)) : null;
                $bookingOrder->departureDateTime = $request->departureDateTime ? date('Y-m-d', strtotime($request->departureDateTime)) : null;
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

                    if ($property->stripeSecretKey) {
                        $ownerStripeSecret = $property->stripeSecretKey;
                    } else {
                        $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
                    }
                    if (empty($property->stripeSecretKey) && empty($ownerStripeSecret)) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'You can not book this property',
                            'data'    => [],
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
                        'amount'                    => (int) ($bookingOrder->price * 100),
                        'currency'                  => 'GBP',
                        'customer'                  => $customer->id,
                        'automatic_payment_methods' => [
                            'enabled' => true,
                        ],
                        'metadata' => [
                            'registrationId' => (string) $bookingOrderId,
                        ],
                    ]);

                    $response = [
                        'status'       => true,
                        'bookingId'    => $bookingOrderId,
                        'token'        => (string) $paymentIntent->id,
                        'clientSecret' => $paymentIntent->client_secret,
                        'publishable'  => base64_encode($ownerStripePublicKey),
                        'total'        => $bookingOrder->cost * 100,
                    ];

                    return response()->json($response);
                }

                return response()->json(['status' => false, 'message' => 'Failed to your booking', 'data' => []], 500);
            });
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => 'Unable to process booking at this time. Please try again later.', 'data' => []]);
        }
    }

    public function propertyDetails(Request $request): JsonResponse
    {
        $property = Property::selectRaw('id,ownerId,propertyName,address,address2,city,country,postcode,telephone,phone,latitude,longitude,description,slug,county')->where('slug', $request->slug)->with(['owner', 'resourceTypes', 'facilities', 'propertyImage' => function ($query) {
            $query->orderBy('position', 'asc');
        }, 'resourceTypes.resources' => function ($query) {
            $query->select('id', 'resourceTypeId', 'name', 'customPrice', 'status')->where('status', 1);
        }])->first();
        if ($property) {
            $property->propertyImage->transform(function ($item) {
                $item->image = asset('storage/property/images/' . $item->image);

                return $item;
            });
            $property->ownerName = $property->owner->firstName . ' ' . $property->owner->lastName;
            unset($property->owner);

            return response()->json(['status' => true, 'message' => '', 'data' => $property]);
        }

        return response()->json(['status' => false, 'message' => 'Property not found', 'data' => []]);
    }

    public function getAvailableResourcesTypes(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'slug'              => 'required|exists:property,slug',
                'arrivalDateTime'   => 'required|date',
                'departureDateTime' => 'required|date|after:arrivalDateTime',
                'totalResources'    => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $property = Property::where('slug', $request->slug)
                ->with(['resourceTypes.resources' => function ($query) {
                    $query->select('id', 'name', 'customPrice', 'status')->where('status', 1);
                }])->first();
            if (! $property && $request->booking == 'confirm') {
                return response()->json(['status' => false, 'message' => 'You cannot book this resource,Because this resource type already booked', 'data' => []], 404);
            }
            if (! $property) {
                return response()->json(['status' => false, 'message' => 'Property not found', 'data' => []], 404);
            }

            $arrivalDateTime = date('Y-m-d', strtotime($request->arrivalDateTime));
            $departureDateTime = date('Y-m-d', strtotime($request->departureDateTime));
            $availableResourcesData = getResourcesTypeAvailableByProperty($property->id, $arrivalDateTime, $departureDateTime, $request->totalResources);

            return response()->json(['status' => true, 'message' => '', 'data' => $availableResourcesData]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function bookingCancel(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'bookingId' => 'required|exists:booking_orders,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $bookingOrder = BookingOrder::where('id', $request->bookingId)->first();
            if ($bookingOrder->userId != Auth::user()->id) {
                return response()->json(['status' => false, 'message' => 'you are not authorized to cancel this booking', 'data' => []]);
            }
            $booking = Bookings::where('bookingOrderId', $request->bookingId)->get();
            foreach ($booking as $key => $value) {
                $value->status = 'cancelled';
                $value->save();
            }
            $bookingOrder->status = 'cancelled';
            $bookingOrder->paymentStatus = 'failed';
            $bookingOrder->save();

            return response()->json(['status' => true, 'message' => 'Booking cancelled successfully', 'data' => []]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function bookingDelete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'bookingId' => 'required|exists:booking_orders,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $bookingOrder = BookingOrder::where('id', $request->bookingId)->first();
            if ($bookingOrder->userId != Auth::user()->id) {
                return response()->json(['status' => false, 'message' => 'you are not authorized to delete this booking', 'data' => []]);
            }
            Bookings::where('bookingOrderId', $request->bookingId)->delete();
            $bookingOrder->delete();

            return response()->json(['status' => true, 'message' => '', 'data' => []]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount'   => 'required|integer|min:1',
            'currency' => 'required|string|size:3',
            'slug'     => 'required|exists:property,slug',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'data'    => [],
            ]);
        }
        $property = Property::where('slug', $request->slug)->first();
        if ($property->stripeSecretKey) {
            $ownerStripeSecret = $property->stripeSecretKey;
        } else {
            $ownerStripeSecret = User::where('id', $property->ownerId)->value('stripeSecretKey');
        }
        if (! $property->stripeSecretKey && ! $ownerStripeSecret) {
            return response()->json([
                'status'  => false,
                'message' => 'You can not book this property',
                'data'    => [],
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
            'status'            => true,
            'message'           => '',
            'clientSecret'      => $intent->client_secret,
            'payment_intent_id' => $intent->id,
            'token'             => (string) Str::uuid(),
        ]);
    }

    public function completePayment(Request $request): JsonResponse
    {
        if (empty($request->payment_intent_id)) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment Intent ID is required',
                'data'    => [],
            ]);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
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
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => $e->getMessage(),
                'data'    => [],
            ]);
        }
    }

    public function bookingCancelDelete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'bookingId' => 'required|exists:booking_orders,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $bookingOrder = BookingOrder::where('id', $request->bookingId)->where('userId', Auth::user()->id)->where('status', 'cancelled')->first();
            if ($bookingOrder->userId != Auth::user()->id) {
                return response()->json(['status' => false, 'message' => 'you are not authorized to delete this booking', 'data' => []]);
            }
            Bookings::where('bookingOrderId', $request->bookingId)->delete();
            $bookingOrder->delete();
            if ($bookingOrder) {
                return response()->json(['status' => true, 'message' => 'Booking deleted successfully', 'data' => []]);
            }

            return response()->json(['status' => false, 'message' => 'Booking not found', 'data' => []]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}

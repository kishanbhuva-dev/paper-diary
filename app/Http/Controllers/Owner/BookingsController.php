<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingOrder;
use App\Models\Bookings;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingsController extends Controller
{


    public function index(Request $request)
    {
        try {
            $propertyIds = Auth::user()->properties->pluck('id');

            $bookings = BookingOrder::selectRaw("
    booking_orders.id,
    booking_orders.resourceTypeId,
    booking_orders.userId,
    booking_orders.propertyId,
    date_format(booking_orders.arrivalDateTime,'%d %b %Y') as arrivalDateTime,
    date_format(booking_orders.departureDateTime,'%d %b %Y') as departureDateTime,
    booking_orders.status,
    booking_orders.guestFullName as guestName,
    booking_orders.paymentStatus,
    booking_orders.price,
    booking_orders.guestEmail,
    booking_orders.guestAddress,
    booking_orders.created_at,
    date_format(booking_orders.created_at,'%d %b %Y ') as bookedOn
")->withAggregate('resourceType', 'name')
                ->whereIn('propertyId', $propertyIds)
                ->with(['property' => function ($query) {
                    $query->select('id', 'propertyName');
                }]);

            if ($request->search) {
                $bookings->whereHas('property', function ($query) use ($request) {
                    $query->where('propertyName', 'like', '%' . $request->search . '%');
                })
                    ->orWhere('status', 'like', '%' . $request->search . '%')
                    ->orWhere('price', 'like', '%' . $request->search . '%')
                    ->orWhere('paymentStatus', 'like', '%' . $request->search . '%')
                    ->orWhere('guestFullName', 'like', '%' . $request->search . '%')
                    ->orwhere(function ($q2) use ($request) {
                        $q2->where('guestFullName', 'like', '%' . $request->search . '%')
                            ->orWhere('guestEmail', 'like', '%' . $request->search . '%');
                    });
                $bookings->orWhereHas('resourceType', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                    ->orWhereHas('user', function ($query) use ($request) {
                        $query->where('firstName', 'like', '%' . $request->search . '%')
                            ->orWhere('lastName', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            }
            // filter


            if ($request->guestName) {
                $bookings->where('guestFullName', 'like', '%' . $request->guestName . '%');
            }

            if ($request->guestEmail) {
                $bookings->where('guestEmail', 'like', '%' . $request->guestEmail . '%');
            }

            if ($request->bookedDate) {
                $bookedDate = Carbon::parse($request->bookedDate)->format('Y-m-d');
                $bookings->whereDate('created_at', $bookedDate);
            }



            if ($request->arrivalDateTime && $request->departureDateTime) {

                $startDate = Carbon::parse($request->arrivalDateTime)->startOfDay();
                $endDate   = Carbon::parse($request->departureDateTime)->endOfDay();

                $bookings->whereBetween('arrivalDateTime', [$startDate, $endDate]);
            }

            if ($request->status && $request->status !== 'all') {
                $valid = ['confirm', 'cancelled'];
                $filtered = array_intersect(explode(',', $request->status), $valid);
                if (!empty($filtered)) {
                    $bookings->whereIn('status', $filtered);
                } else {
                    $bookings->whereRaw('1 = 0');
                }
            }

            if ($request->paymentstatus && $request->paymentstatus !== 'all') {
                $valid = ['paid', 'failed'];
                $filtered = array_intersect(explode(',', $request->paymentstatus), $valid);
                if (!empty($filtered)) {
                    $bookings->whereIn('paymentStatus', $filtered);
                } else {
                    $bookings->whereRaw('1 = 0');
                }
            }

            if ($request->fromNow) {

                $now = Carbon::now();

                switch ($request->fromNow) {

                    case 'hour':
                        $from = $now->copy()->subHour();
                        $bookings->whereBetween([$from, $now]);
                        break;

                    case 'today':
                        $from = Carbon::today();
                        $bookings->whereBetween([$from, $now]);
                        break;

                    case 'week':
                        $from = $now->copy()->startOfWeek();
                        $bookings->whereBetween([$from, $now]);
                        break;

                    case 'month':
                        $from = $now->copy()->startOfMonth();
                        $bookings->whereBetween([$from, $now]);
                        break;

                    case 'year':
                        $from = $now->copy()->startOfYear();
                        $bookings->whereBetween([$from, $now]);
                        break;
                }

                $bookings->orderBy('created_at', 'desc');
            }




            $bookings->with(['user' => function ($query) {
                $query->select('id', 'firstName', 'lastName', 'email');
            }, 'resourceType.resources' => function ($query) {
                $query->select('id', 'resourceTypeId', 'name');
            }]);
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';

            if ($sortBy === 'propertyName') {
                $bookings->join('property', 'property.id', '=', 'booking_orders.propertyId')
                    ->orderBy('property.propertyName', $sortOrder);
            } else {
                $bookings->orderBy('booking_orders.' . $sortBy, $sortOrder);
            }



            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            $bookings->getCollection()->transform(function ($booking) {

                $booking->fromNow = carbon::parse($booking->created_at)->diffForHumans();


                return $booking;
            });


            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'ownerNotes' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()]);
        }
        $bookingOrder = BookingOrder::where('id', $id)->first();
        $bookingOrder->ownerNotes = $request->ownerNotes;
        $bookingOrder->save();
        return response()->json(['status' => true, 'message' => 'Booking updated successfully', 'data' => []]);
    }
    public function bookingCancel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'bookingId' => 'required|exists:booking_orders,id',
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'message' => $validator->errors()->first(), 'data' => []]);
            }
            $bookingOrder = BookingOrder::where('id', $request->bookingId)->first();
            $propertyIds = Auth::user()->properties->pluck('id');
            if (!$propertyIds->contains($bookingOrder->propertyId)) {
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
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message'    => $th->getMessage(), 'data' => []]);
        }
    }
}

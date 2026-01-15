<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\Bookings;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class BookingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $propertyIds = Property::where('ownerId', Auth::user()->id)->pluck('id');
            if (count($propertyIds) > 0) {
                return response()->json(['status' => false, 'message' => 'Property and Resource Type and resource add first after you can see calender and dashboard data', 'data' => []]);
            }
            $bookings = BookingOrder::selectRaw("booking_orders.id,booking_orders.resourceTypeId,booking_orders.userId,booking_orders.propertyId,date_format(booking_orders.arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(booking_orders.departureDateTime,'%d %b %Y') as departureDateTime,booking_orders.status,booking_orders.guestFullName as guestName,booking_orders.paymentStatus,booking_orders.price,booking_orders.guestEmail,booking_orders.guestAddress, booking_orders.created_at, date_format(booking_orders.created_at,'%d %b %Y') as bookedOn, resource_types.name as resource_type_name, property.propertyName as propertyName")->leftJoin('resource_types', 'booking_orders.resourceTypeId', '=', 'resource_types.id')->leftJoin('users', 'booking_orders.userId', '=', 'users.id')->leftJoin('property', 'booking_orders.propertyId', '=', 'property.id')->whereRaw('booking_orders.propertyId IN (' . implode(',', $propertyIds->toArray()) . ')')->with(['property' => function ($query) {
                $query->select('id', 'propertyName');
            }]);
            if ($request->search) {
                $bookings->whereRaw("(booking_orders.guestFullName LIKE '%" . $request->search . "%' OR booking_orders.status LIKE '%" . $request->search . "%' OR booking_orders.price LIKE '%" . $request->search . "%' OR booking_orders.paymentStatus LIKE '%" . $request->search . "%' OR booking_orders.guestEmail LIKE '%" . $request->search . "%' OR resource_types.name LIKE '%" . $request->search . "%' OR users.firstName LIKE '%" . $request->search . "%' OR users.lastName LIKE '%" . $request->search . "%' OR users.email LIKE '%" . $request->search . "%')");
            }
            // filter

            if ($request->guestName) {
                $bookings->where('booking_orders.guestFullName', 'like', '%' . $request->guestName . '%');
            }

            if ($request->guestEmail) {
                $bookings->where('booking_orders.guestEmail', 'like', '%' . $request->guestEmail . '%');
            }

            if ($request->bookedDate) {
                $bookedDate = Carbon::parse($request->bookedDate)->format('Y-m-d');
                $bookings->whereDate('booking_orders.created_at', $bookedDate);
            }

            if ($request->arrivalDateTime && $request->departureDateTime) {
                $startDate = Carbon::parse($request->arrivalDateTime)->startOfDay();
                $endDate = Carbon::parse($request->departureDateTime)->endOfDay();

                $bookings->whereBetween('arrivalDateTime', [$startDate, $endDate]);
            }

            if ($request->status && $request->status !== 'all') {
                $valid = ['confirm', 'cancelled'];
                $filtered = array_intersect(explode(',', $request->status), $valid);
                if (! empty($filtered)) {
                    $bookings->whereIn('status', $filtered);
                } else {
                    $bookings->whereRaw('1 = 0');
                }
            }

            if ($request->paymentstatus && $request->paymentstatus !== 'all') {
                $valid = ['paid', 'failed'];
                $filtered = array_intersect(explode(',', $request->paymentstatus), $valid);
                if (! empty($filtered)) {
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
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;

                    case 'today':
                        $from = Carbon::today();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;

                    case 'week':
                        $from = $now->copy()->startOfWeek();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;

                    case 'month':
                        $from = $now->copy()->startOfMonth();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;

                    case 'year':
                        $from = $now->copy()->startOfYear();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                }

                $bookings->orderBy('booking_orders.created_at', 'desc');
            }

            $bookings->with(['user' => function ($query) {
                $query->select('id', 'firstName', 'lastName', 'email');
            }, 'resourceType.resources' => function ($query) {
                $query->select('id', 'resourceTypeId', 'name');
            }]);
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';

            $columnMap = [
                'propertyName'       => 'property.propertyName',
                'guestName'          => 'booking_orders.guestFullName',
                'resource_type_name' => 'resource_types.name',
                'arrivalDateTime'    => 'booking_orders.arrivalDateTime',
                'departureDateTime'  => 'booking_orders.departureDateTime',
                'price'              => 'booking_orders.price',
                'status'             => 'booking_orders.status',
                'paymentStatus'      => 'booking_orders.paymentStatus',
                'bookedOn'           => 'booking_orders.created_at',
            ];

            $sortColumn = $columnMap[$sortBy] ?? 'booking_orders.' . $sortBy;

            if ($sortBy === 'fromNow') {
                $sortColumn = 'booking_orders.created_at';
            }

            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            $bookings->getCollection()->transform(function ($booking) {
                $booking->fromNow = Carbon::parse($booking->created_at)->diffForHumans();

                return $booking;
            });

            return response()->json(['status' => true, 'message' => '', 'data' => $bookings], 200);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []], 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
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

        return response()->json(['status' => true, 'message' => 'Booking updated successfully', 'data' => []], 200);
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
            $propertyIds = Auth::user()->properties->pluck('id');
            if (! $propertyIds->contains($bookingOrder->propertyId)) {
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

            return response()->json(['status' => true, 'message' => 'Booking cancelled successfully', 'data' => []], 200);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []], 500);
        }
    }
}

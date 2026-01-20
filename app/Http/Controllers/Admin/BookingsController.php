<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\Bookings;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class BookingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $bookings = BookingOrder::selectRaw("
                booking_orders.id,
                booking_orders.propertyId,
                booking_orders.resourceTypeId,
                booking_orders.userId,
                booking_orders.status,
                booking_orders.guestFullName,
                booking_orders.guestEmail,
                DATE_FORMAT(booking_orders.created_at, '%d %b %Y') as bookedOn,
                DATE_FORMAT(booking_orders.arrivalDateTime, '%d-%m-%Y') as arrivalDateTime,
                DATE_FORMAT(booking_orders.departureDateTime, '%d-%m-%Y') as departureDateTime,
                booking_orders.status,
                DATE_FORMAT(booking_orders.arrivalDateTime, '%d %b %Y') as arrivalDateTime,
                DATE_FORMAT(booking_orders.departureDateTime, '%d %b %Y') as departureDateTime,
                COALESCE(property.propertyName, 'N/A') as propertyName,
                COALESCE(CONCAT(users.firstName, ' ', users.lastName), 'N/A') as ownerName,
                COALESCE(users.email, 'N/A') as ownerEmail
            ")
                ->leftJoin('property', 'booking_orders.propertyId', '=', 'property.id')
                ->leftJoin('users', 'property.ownerId', '=', 'users.id');

            // Search Logic
            if ($request->search) {
                $searchTerm = '%' . $request->search . '%';
                $rawSearch = $request->search;

                $bookings->where(function ($query) use ($searchTerm, $rawSearch) {
                    $query->where('property.propertyName', 'like', $searchTerm)
                        ->orWhereRaw("CONCAT(users.firstName, ' ', users.lastName) LIKE ?", [$searchTerm])
                        ->orWhere('users.email', 'like', $searchTerm)
                        ->orWhere('booking_orders.status', 'like', $searchTerm)
                        ->orWhere('booking_orders.id', 'like', $searchTerm)
                        ->orWhere('booking_orders.arrivalDateTime', 'like', $searchTerm)
                        ->orWhere('booking_orders.departureDateTime', 'like', $searchTerm)
                        ->orWhereDate('booking_orders.arrivalDateTime', '=', $rawSearch)
                        ->orWhereDate('booking_orders.departureDateTime', '=', $rawSearch)
                        ->orWhere('booking_orders.guestFullName', 'like', $searchTerm)
                        ->orWhere('booking_orders.guestEmail', 'like', $searchTerm);
                });
            }
            if ($request->guestName) {
                $bookings->where('booking_orders.guestFullName', 'like', '%' . $request->guestName . '%');
            }
            if ($request->guestEmail) {
                $bookings->where('booking_orders.guestEmail', 'like', '%' . $request->guestEmail . '%');
            }
            if ($request->bookedOn) {
                $bookedDate = Carbon::parse($request->bookedOn)->format('Y-m-d');
                $bookings->whereDate('booking_orders.created_at', $bookedDate);
            }
            if ($request->fromNow) {
                $now = Carbon::now();

                switch ($request->fromNow) {
                    case '1year':
                        $from = $now->copy()->subYear();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case '6months':
                        $from = $now->copy()->subMonths(6);
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case '4months':
                        $from = $now->copy()->subMonths(4);
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case '2months':
                        $from = $now->copy()->subMonths(2);
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case '1month':
                        $from = $now->copy()->subMonth();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case '2weeks':
                        $from = $now->copy()->subWeeks(2);
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case '1week':
                        $from = $now->copy()->subWeek();
                        $bookings->whereBetween('booking_orders.created_at', [$from, $now]);

                        break;
                    case 'yesterday':
                        $bookings->whereDate('booking_orders.created_at', Carbon::yesterday());

                        break;
                    case 'today':
                        $bookings->whereDate('booking_orders.created_at', Carbon::today());

                        break;
                }
            }
            if ($request->arrivalDateTime || $request->departureDateTime) {
                $startDate = Carbon::parse($request->arrivalDateTime)->startOfDay();
                $endDate = Carbon::parse($request->departureDateTime)->endOfDay();
                if ($request->arrivalDateTime && $request->departureDateTime) {
                    $bookings->whereRaw('booking_orders.arrivalDateTime >= "' . $startDate . '"')->whereRaw('booking_orders.departureDateTime <= "' . $endDate . '"');
                } elseif ($request->arrivalDateTime) {
                    $bookings->whereRaw('booking_orders.arrivalDateTime >= "' . $startDate . '"');
                } elseif ($request->departureDateTime) {
                    $bookings->whereRaw('booking_orders.departureDateTime <= "' . $endDate . '"');
                }
            }
            if ($request->status && $request->status !== 'all') {
                $valid = ['confirm', 'cancelled'];
                $filtered = array_intersect(explode(',', $request->status), $valid);
                if (! empty($filtered)) {
                    $bookings->whereIn('booking_orders.status', $filtered);
                } else {
                    $bookings->whereRaw('1 = 0');
                }
            }
            if ($request->paymentStatus && $request->paymentStatus !== 'all') {
                $valid = ['paid', 'failed', 'unpaid'];
                $filtered = array_intersect(explode(',', $request->paymentStatus), $valid);
                if (! empty($filtered)) {
                    $bookings->whereIn('booking_orders.paymentStatus', $filtered);
                } else {
                    $bookings->whereRaw('1 = 0');
                }
            }
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';
            $sortMapping = [
                'id'                => 'booking_orders.id',
                'propertyName'      => 'property.propertyName',
                'ownerName'         => 'users.firstName',
                'ownerEmail'        => 'users.email',
                'arrivalDateTime'   => 'booking_orders.arrivalDateTime',
                'departureDateTime' => 'booking_orders.departureDateTime',
                'status'            => 'booking_orders.status',
                'guestFullName'     => 'booking_orders.guestFullName',
                'guestEmail'        => 'booking_orders.guestEmail',
                'bookedOn'          => 'booking_orders.created_at',
            ];
            $finalSort = $sortMapping[$sortBy] ?? 'booking_orders.id';
            $bookings->orderBy($finalSort, $sortOrder);
            $paginatedData = $bookings->paginate($perPage);
            $paginatedData->getCollection()->each(function (BookingOrder $booking): array {
                return [
                    'id'                => $booking->id,
                    'propertyId'        => $booking->propertyId,
                    'propertyName'      => $booking->propertyName,
                    'ownerEmail'        => $booking->ownerEmail,
                    'ownerName'         => $booking->ownerName,
                    'arrivalDateTime'   => $booking->arrivalDateTime,
                    'departureDateTime' => $booking->departureDateTime,
                    'status'            => $booking->status,
                    'guestFullName'     => $booking->guestFullName,
                    'guestEmail'        => $booking->guestEmail,
                    'bookedOn'          => $booking->created_at,
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => '',
                'data'    => $paginatedData,
            ]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    public function bookingCancel(): JsonResponse
    {
        try {
            $bookingOrder = BookingOrder::where('created_at', '<', now()->startOfDay())
                ->where('status', 'pending')->get();
            foreach ($bookingOrder as $bookingOrders) {
                $booking = Bookings::where('bookingOrderId', $bookingOrders->id)->first();
                $booking->status = 'cancelled';
                $booking->save();
                $bookingOrders->status = 'cancelled';
                $bookingOrders->paymentStatus = 'cancelled';
                $bookingOrders->save();
            }

            return response()->json(['status' => true, 'message' => 'Bookings cancelled successfully', 'data' => []]);
        } catch (Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
}

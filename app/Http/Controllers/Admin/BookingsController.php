<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingOrder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
                DATE_FORMAT(booking_orders.arrivalDateTime, '%d-%m-%Y') as arrivalDateTime, 
                DATE_FORMAT(booking_orders.departureDateTime, '%d-%m-%Y') as departureDateTime, 
                COALESCE(property.propertyName, 'N/A') as propertyName, 
                COALESCE(CONCAT(users.firstName, ' ', users.lastName), 'N/A') as ownerName, 
                COALESCE(users.email, 'N/A') as ownerEmail
            ")
            ->leftJoin('property', 'booking_orders.propertyId', '=', 'property.id')
            ->leftJoin('users', 'property.ownerId', '=', 'users.id');

            // Search Logic
            if ($request->filled('search')) {
                $searchTerm = '%' . $request->search . '%';
                $rawSearch = $request->search;

                $bookings->where(function ($query) use ($searchTerm, $rawSearch) {
                    $query->where('property.propertyName', 'like', $searchTerm)
                        ->orWhere('users.firstName', 'like', $searchTerm)
                        ->orWhere('users.lastName', 'like', $searchTerm)
                        ->orWhere('users.email', 'like', $searchTerm)
                        ->orWhere('booking_orders.status', 'like', $searchTerm)
                        ->orWhere('booking_orders.id', 'like', $searchTerm)
                        ->orWhere('booking_orders.arrivalDateTime', 'like', $searchTerm)
                        ->orWhere('booking_orders.departureDateTime', 'like', $searchTerm)
                        ->orWhereDate('booking_orders.arrivalDateTime', '=', $rawSearch)
                        ->orWhereDate('booking_orders.departureDateTime', '=', $rawSearch);
                });
            }

            // Sorting Logic
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
            ];

            $finalSort = $sortMapping[$sortBy] ?? 'booking_orders.id';
            $bookings->orderBy($finalSort, $sortOrder);

            // Pagination and Transformation
            $paginatedData = $bookings->paginate($perPage);
            
            $paginatedData->through(function ($booking) {
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
                ];
            });

            return response()->json([
                'status'  => true,
                'message' => 'Bookings retrieved successfully',
                'data'    => $paginatedData
            ]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
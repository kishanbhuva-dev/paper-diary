<?php

namespace App\Http\Controllers\Owner;

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
            $propertyIds = Auth::user()->properties->pluck('id');
            $bookings = BookingOrder::selectRaw("id,userId,propertyId,date_format(arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(departureDateTime,'%d %b %Y') as departureDateTime,status,guestFullName as guestName,paymentStatus,price,guestEmail,guestAddress,date_format(created_at,'%d %b %Y ') as bookedOn")->withAggregate('resourceType','name')->whereIn('propertyId', $propertyIds)->with(['property'=>function($query){
                $query->select('id','propertyName');
            }]);  
            if ($request->search) {
                $bookings->whereHas('property', function ($query) use ($request) {
                    $query->where('propertyName', 'like', '%' . $request->search . '%');
                })
                ->orWhere('status', 'like', '%' . $request->search . '%')
                ->orWhere('price', 'like', '%' . $request->search . '%')
                ->orWhere('paymentStatus', 'like', '%' . $request->search . '%')
                ->orWhere('guestFullName', 'like', '%' . $request->search . '%')
                ->orWhere('guestEmail', 'like', '%' . $request->search . '%')
                ->orWhere('guestAddress', 'like', '%' . $request->search . '%');
                
                if (strtotime($request->search)) {
                    $searchDate = date('Y-m-d', strtotime($request->search));
                    $bookings->orWhere('arrivalDateTime', 'like', '%' . $searchDate . '%')
                        ->orWhere('departureDateTime', 'like', '%' . $searchDate . '%');
                }
                
                $bookings->orWhereHas('resourceType', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                ->orWhereHas('user', function ($query) use ($request) {
                    $query->where('firstName', 'like', '%' . $request->search . '%')
                        ->orWhere('lastName', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
                });
            }
            $bookings->with(['user'=>function($query){
                $query->select('id','firstName','lastName','email');
            }]);
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';
            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            
            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
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
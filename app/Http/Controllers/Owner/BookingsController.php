<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BookingOrder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingsController extends Controller
{

    public function index(Request $request)
    {
        try {
            $propertyIds = Auth::user()->properties->pluck('id');
            $bookings = BookingOrder::selectRaw("id,resourceTypeId,userId,propertyId,date_format(arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(departureDateTime,'%d %b %Y') as departureDateTime,status,guestFullName as guestName,paymentStatus,price,guestEmail,guestAddress,date_format(created_at,'%d %b %Y ') as bookedOn")->withAggregate('resourceType','name')->whereIn('propertyId', $propertyIds)->with(['property'=>function($query){
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
            },'resourceType.resources'=>function($query){
                $query->select('id','resourceTypeId','name');
            }]);
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';
            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            $bookings->getCollection()->transform(function ($booking) {
                $booking->resource_names = $booking->resourceType->resources->pluck('name');
                unset($booking->resourceType);
                return $booking;
            });
            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message' => $th->getMessage(), 'data' => []]);
        }
    }
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        //
    }
    public function show(string $id)
    {
        //
    }
    public function edit(string $id)
    {
        //
    }
    public function update(Request $request, string $id)
    {
        //
    }
    public function destroy(string $id)
    {
        //
    }
}
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
            $bookings = BookingOrder::selectRaw("id,propertyId,resourceTypeId,userId,status,date_format(arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(departureDateTime,'%d %b %Y') as departureDateTime");  
            if ($request->search) { 
                $bookings->whereHas('property', function ($query) use ($request) { 
                    $query->where('propertyName', 'like', '%' . $request->search . '%'); 
                }) 
                ->orWhereHas('property.owner', function ($query) use ($request) { 
                    $query->where('firstName', 'like', '%' . $request->search . '%') 
                        ->orWhere('lastName', 'like', '%' . $request->search . '%') 
                        ->orWhere('email', 'like', '%' . $request->search . '%'); 
                }) 
                ->orWhere('status', 'like', '%' . $request->search . '%');
                if (strtotime($request->search)) {
                    $bookings->orWhere('arrivalDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%')
                        ->orWhere('departureDateTime', 'like', '%' . date('Y-m-d', strtotime($request->search)) . '%');
                }
    
                $bookings->orWhereHas('user', function ($query) use ($request) { 
                    $query->where('firstName', 'like', '%' . $request->search . '%') 
                        ->orWhere('lastName', 'like', '%' . $request->search . '%') 
                        ->orWhere('email', 'like', '%' . $request->search . '%'); 
                }); 
            }
            $bookings->with(['property'=>function($query){
                $query->select('id','ownerId','propertyName');
            },'property.owner'=>function($query){
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
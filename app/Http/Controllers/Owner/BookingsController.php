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
            $propertyIds = Auth::user()->properties->pluck('id')->toArray();
            $bookings = BookingOrder::selectRaw("id,propertyId,resourceTypeId,userId,adult,children,price,status,paymentStatus,date_format(arrivalDateTime,'%d-%m-%Y %H:%i') as arrivalDateTime,date_format(departureDateTime,'%d-%m-%Y %H:%i') as departureDateTime,created_at")->whereIn('propertyId', $propertyIds)->with(['property', 'resourceType' => function($query){
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
                })
                ->orWhereHas('user', function ($query) use ($request) {
                    $query->where('firstName', 'like', '%' . $request->search . '%')
                        ->orWhere('lastName', 'like', '%' . $request->search . '%')
                        ->orWhere('email', 'like', '%' . $request->search . '%');
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
                $item->userName=$item->user->firstName.' '.$item->user->lastName;
                $item->userEmail=$item->user->email;
                unset($item->user);
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

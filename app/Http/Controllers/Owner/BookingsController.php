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
            $bookings = BookingOrder::selectRaw("id,resourceTypeId,userId,propertyId,date_format(arrivalDateTime,'%d %b %Y') as arrivalDateTime,date_format(departureDateTime,'%d %b %Y') as departureDateTime,status,guestFullName as guestName,paymentStatus,price,guestEmail,guestAddress, created_at,date_format(created_at,'%d %b %Y ') as bookedOn")->withAggregate('resourceType', 'name')->whereIn('propertyId', $propertyIds)->with(['property' => function ($query) {
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
                // if (strtotime($search)) {
                //     $searchDate = date('Y-m-d', strtotime($search));
                //     $query->orWhereDate('arrivalDateTime', $searchDate)
                //         ->orWhereDate('departureDateTime', $searchDate);
                // }

                 
                $bookings->orWhereHas('resourceType', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search . '%');
                })
                    ->orWhereHas('user', function ($query) use ($request) {
                        $query->where('firstName', 'like', '%' . $request->search . '%')
                            ->orWhere('lastName', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            }
            $bookings->with(['user' => function ($query) {
                $query->select('id', 'firstName', 'lastName', 'email');
            }, 'resourceType.resources' => function ($query) {
                $query->select('id', 'resourceTypeId', 'name');
            }]);
            $perPage = $request->perPage ?? 10;
            $sortBy = $request->sortBy ?? 'id';
            $sortOrder = $request->sortOrder ?? 'desc';
            $bookings = $bookings->orderBy($sortBy, $sortOrder)->paginate($perPage);
            $bookings->getCollection()->transform(function ($booking) {

                $created = Carbon::parse($booking->created_at);
                $now     = Carbon::now();

                $diffInSeconds = $created->diffInSeconds($now, false);

                if ($diffInSeconds === 0) {
                    $booking->fromNow = 'Just now';
                    return $booking;
                }

                if ($diffInSeconds > 0) {
                    $minutes = floor($diffInSeconds / 60);
                    $hours   = floor($minutes / 60);
                    $days    = floor($hours / 24);
                    $weeks   = floor($days / 7);
                    $months  = floor($days / 30);
                    $years   = floor($days / 365);

                    if ($minutes < 1)       $booking->fromNow = "Just now";
                    elseif ($minutes < 60)  $booking->fromNow = $minutes . " minutes ago";
                    elseif ($hours < 24)    $booking->fromNow = $hours . " hours ago";
                    elseif ($days < 7)      $booking->fromNow = $days . " days ago";
                    elseif ($weeks < 4)     $booking->fromNow = $weeks . " weeks ago";
                    elseif ($months < 12)   $booking->fromNow = $months . " months ago";
                    else                    $booking->fromNow = $years . " years ago";
                }

                return $booking;
            });


            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message'    => $th->getMessage(), 'data' => []]);
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
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


            if ($request->checkInDate && $request->checkOutDate) {
                $startDate = Carbon::parse($request->checkInDate)->startOfDay();
                $endDate   = Carbon::parse($request->checkOutDate)->endOfDay();

                $bookings->where(function ($query) use ($startDate, $endDate) {
                    $query->where('arrivalDateTime', '<=', $endDate)
                        ->where('departureDateTime', '>=', $startDate);
                });
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


                return $booking;
            });


            return response()->json(['status' => true, 'message' => '', 'data' => $bookings]);
        } catch (\Throwable $th) {
            return response()->json(['status' => false, 'message'    => $th->getMessage(), 'data' => []]);
        }
    }
}







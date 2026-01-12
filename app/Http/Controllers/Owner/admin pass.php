admin
admin@gmail.com
123456789
owner
tester@gmail.com 
1 to 9
user
user@test.com
1 to 9




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




    public function dashboardOverview(Request $request)
    {
        //Live Property
        $liveProperties  = Property::where('status', 1)->count();
        $totalProperties = Property::count();

        //total Revenue
        $totalRevenue = BookingOrder::where('status', 'confirm')->sum('price');
        $lostAmount   = BookingOrder::where('status', 'cancelled')->sum('price');

        //Date
        $todayStart = Carbon::today();
        $todayEnd   = Carbon::today()->endOfDay();

        $weekStart  = Carbon::now()->startOfWeek();
        $weekEnd    = Carbon::now()->endOfWeek();

        $monthStart = Carbon::now()->startOfMonth();
        $monthEnd   = Carbon::now()->endOfMonth();

        //Revenue OverView
        $todayRevenue = BookingOrder::where('status', 'confirm')
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('price');

        $weekRevenue = BookingOrder::where('status', 'confirm')
            ->whereBetween('created_at', [$weekStart, $weekEnd])
            ->sum('price');

        $monthRevenue = BookingOrder::where('status', 'confirm')
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->sum('price');

        //Revenue Trands

        $revenueTrendRaw = BookingOrder::where('status', 'confirm')
            ->whereDate('created_at', '>=', Carbon::now()->subDays(6))
            ->selectRaw('DATE(created_at) as date, SUM(price) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueTrend = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $day  = Carbon::now()->subDays($i)->format('D');

            $row = $revenueTrendRaw->firstWhere('date', $date);

            $revenueTrend[] = [
                'day'    => $day,
                'amount' => (float) ($row->total ?? 0),
            ];
        }

        //booking Analytics

        $todayStart = Carbon::today();
        $todayEnd   = Carbon::today()->endOfDay();

        $todaysBookings = BookingOrder::whereBetween('created_at', [$todayStart, $todayEnd])->count();

        $todaysRevenue = BookingOrder::whereRaw('LOWER(status) = ?', ['confirm'])
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('price');

        $todayConfirmed = BookingOrder::whereRaw('LOWER(status) = ?', ['confirm'])
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $totalCancelled = BookingOrder::whereRaw('LOWER(status) = ?', ['cancelled'])->count();


        return response()->json([
            'success' => true,
            'data' => [

                // Properties
                'live_properties'  => $liveProperties,
                'total_properties' => $totalProperties,

                // Revenue
                'total_revenue'    => $totalRevenue,
                'lost_amount'      => $lostAmount,

                // Revenue Overview
                'revenue_overview' => [
                    'today'         => $todayRevenue,
                    'this_week'     => $weekRevenue,
                    'this_month'    => $monthRevenue,
                    'revenue_trend' => $revenueTrend,
                ],

                // Booking Analytics 
                'booking_analytics' => [
                    'todays_bookings' => $todaysBookings,

                    'total_revenue'   => $todaysRevenue,

                    'booking_status' => [
                        'confirmed' => $todayConfirmed,
                        'cancelled' => $totalCancelled,
                    ]
                ]

            ]
        ]);
    }

  
}
//          if ($request->fromNow) {

            //     $now = Carbon::now();
            //     $fromNow = strtolower(trim($request->fromNow));

            //     switch ($fromNow) {
            //         case 'hour':
            //             $bookings->where('created_at', '>=', $now->copy()->subHour());
            //             break;

            //         case 'today':
            //             $bookings->whereDate('created_at', Carbon::today());
            //             break;

            //         case 'yesterday':
            //             $bookings->whereDate('created_at', Carbon::yesterday());
            //             break;

            //         default:
            //             if (preg_match('/(\d+)\s*days?\s*ago/', $fromNow, $matches)) {
            //                 $days = (int) $matches[1];
            //                 $targetDate = Carbon::now()->subDays($days)->toDateString();
            //                 $bookings->whereDate('created_at', $targetDate);
            //             }
            //             break;
            //     }

            //     $bookings->orderBy('created_at', 'asc');
            // }


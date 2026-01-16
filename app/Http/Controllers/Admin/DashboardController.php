<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Throwable;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $liveProperties = Property::where('status', 1)->count();
            $totalProperties = Property::count();
            $totalRevenue = BookingOrder::where('status', 'confirm')->sum('price');

            $todayStart = Carbon::today();
            $todayEnd = Carbon::today()->endOfDay();

            $totalLostAmount = BookingOrder::where('status', 'cancelled')->sum('price');

            $lostAmountToday = BookingOrder::where('status', 'cancelled')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->sum('price');
            $summary = [
                'liveProperties'  => $liveProperties,
                'totalProperties' => $totalProperties,
                'totalRevenue'    => $totalRevenue,
                'totalLostAmount' => $totalLostAmount,
            ];

            $weekStart = Carbon::now()->startOfWeek();
            $weekEnd = Carbon::now()->endOfWeek();
            $monthStart = Carbon::now()->startOfMonth();
            $monthEnd = Carbon::now()->endOfMonth();

            $todayRevenue = BookingOrder::where('status', 'confirm')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->sum('price');

            $weekRevenue = BookingOrder::where('status', 'confirm')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('price');

            $monthRevenue = BookingOrder::where('status', 'confirm')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('price');

            $revenueTrendRaw = BookingOrder::where('status', 'confirm')
                ->whereDate('created_at', '>=', Carbon::now()->subDays(6))
                ->selectRaw('DATE(created_at) as date, SUM(price) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $trend7Days = [];
            for ($i = 6; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i)->toDateString();
                $day = Carbon::now()->subDays($i)->format('D');
                $row = $revenueTrendRaw->firstWhere('date', $date);

                $trend7Days[] = [
                    'day'    => $day,
                    'amount' => (float) ($row->total ?? 0),
                ];
            }

            $todaysBookings = BookingOrder::whereBetween('created_at', [$todayStart, $todayEnd])->count();
            $todayConfirmed = BookingOrder::where('status', 'confirm')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count();
            $todayPending = BookingOrder::where('status', 'pending')
                ->whereBetween('created_at', [$todayStart, $todayEnd])
                ->count();
            $totalCancelled = BookingOrder::whereRaw('LOWER(status) = ?', ['cancelled'])->whereBetween('created_at', [$todayStart, $todayEnd])->count();

            $details = [
                'revenue' => [
                    'today'      => $todayRevenue,
                    'thisWeek'   => $weekRevenue,
                    'thisMonth'  => $monthRevenue,
                    'trend7Days' => $trend7Days,
                ],
                'bookingAnalytics' => [
                    'todaysBookings'  => $todaysBookings,
                    'todaysRevenue'   => $todayRevenue,
                    'lostAmountToday' => $lostAmountToday,

                    'bookingStatus' => [
                        'confirmed' => $todayConfirmed,
                        'cancelled' => $totalCancelled,
                        'pending'   => $todayPending,
                    ],
                ],
            ];
            $topSites = BookingOrder::where('booking_orders.status', 'confirm')
                ->whereBetween('booking_orders.created_at', [$todayStart, $todayEnd])
                ->join('property', 'booking_orders.propertyId', '=', 'property.id')
                ->join('users', 'property.ownerId', '=', 'users.id')
                ->selectRaw('property.propertyName as property_name, CONCAT(users.firstName, " ", users.lastName) as owner_name, COUNT(booking_orders.id) as booking_count, SUM(booking_orders.price) as totalAmount')
                ->groupBy('property.id', 'property.propertyName', 'users.firstName', 'users.lastName')
                ->orderByDesc('booking_count')
                ->limit(10)
                ->get();
            $details['topSites'] = $topSites;

            $bookingTotalPerMonth = BookingOrder::where('status', 'confirm')
                ->selectRaw('DATE_FORMAT(arrivalDateTime, "%b-%Y") as month, SUM(price) as total, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            $monthlyBookingData = [];
            $grandTotal = 0;

            foreach ($bookingTotalPerMonth as $booking) {
                $total = (float) $booking->total;
                $grandTotal += $total;

                $monthlyBookingData[] = [
                    'month' => $booking->month,
                    'total' => $total,
                    'count' => $booking->count,
                ];
            }

            $details['bookingTotalPerMonth'] = [
                'data'       => $monthlyBookingData,
                'grandTotal' => $grandTotal,
            ];

            return response()->json([
                'status'  => true,
                'message' => '',
                'data'    => [
                    'summary' => $summary,
                    'details' => $details,
                ],
            ]);
        } catch (Throwable $th) {
            return response()->json([
                'status'  => false,
                'message' => $th->getMessage(),
                'data'    => [],
            ]);
        }
    }
}

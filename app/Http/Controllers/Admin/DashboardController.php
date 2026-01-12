<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\Models\Property;
use Carbon\Carbon;
use Throwable;

class DashboardController extends Controller
{
    public function index()
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

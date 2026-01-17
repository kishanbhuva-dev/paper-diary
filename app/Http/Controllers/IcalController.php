<?php

namespace App\Http\Controllers;

use App\Models\Bookings;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class IcalController extends Controller
{
    public function index(string $resourceId): Response
    {
        $query = Bookings::with('bookingOrder');

        if ($resourceId) {
            $query->where('resourceId', $resourceId);
        }

        $bookings = $query->get();
        $calendar = Calendar::create('Paper Diary Bookings');
        foreach ($bookings as $booking) {
            $bookingOrder = $booking->bookingOrder->first();
            if (! $bookingOrder) {
                continue;
            }

            $start = $bookingOrder->arrivalDateTime ? Carbon::parse($bookingOrder->arrivalDateTime) : null;
            $end = $bookingOrder->departureDateTime ? Carbon::parse($bookingOrder->departureDateTime) : null;
            if ($start) {
                /** @var Event $event */
                $event = Event::create()->uniqueIdentifier($booking->id . '@paper-dairy.eviontech.com/')->name(strtoupper($bookingOrder->guestFullName ?? 'GUEST') . " ({$booking->id})")->startsAt($start, true)->endsAt($end ? $end : $start->copy()->addDay(), true);
                $event->appendProperty(TextProperty::create('X-SOURCE', 'DIRECT'))->appendProperty(TextProperty::create('X-CHANNEL', 'N/A'))->appendProperty(TextProperty::create('X-CHANNEL-REF', 'N/A'));
                $calendar->event($event);
            }
        } if (ob_get_length()) {
            ob_clean();
        }

        return response(trim($calendar->get()))
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="bookings.ics"');
    }
}

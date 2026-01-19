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
        $query = Bookings::with('bookingOrder')->where('status', 'confirmed');

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
        }
        $content = $calendar->get();
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return response($content, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="bookings.ics"',
            'Content-Length'      => strlen($content),
            'Connection'          => 'close',
        ]);
    }
}

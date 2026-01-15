<?php

namespace App\Http\Controllers;

use App\Models\BookingOrder;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\TextProperty;

class IcalController extends Controller
{
    public function index(): Response
    {
        $bookings = BookingOrder::all();
        $calendar = Calendar::create('Paper Diary Bookings');
        foreach ($bookings as $booking) {
            $start = $booking->arrivalDateTime ? Carbon::parse($booking->arrivalDateTime) : null;
            $end = $booking->departureDateTime ? Carbon::parse($booking->departureDateTime) : null;
            if ($start) {
                /** @var Event $event */
                $event = Event::create()->uniqueIdentifier($booking->id . '@paperdiary.com')->name(strtoupper($booking->guestFullName ?? 'GUEST') . " ({$booking->id})")->startsAt($start, true)->endsAt($end ? $end : $start->copy()->addDay(), true);
                $event->appendProperty(TextProperty::create('X-SOURCE', 'DIRECT'))->appendProperty(TextProperty::create('X-CHANNEL', 'N/A'))->appendProperty(TextProperty::create('X-CHANNEL-REF', 'N/A'));
                $calendar->event($event);
            }
        } if (ob_get_length()) {
            ob_clean();
        }

        return response(trim($calendar->get()))->header('Content-Type', 'text/calendar; charset=utf-8')->header('Content-Disposition', 'attachment; filename="bookings.ics"');
    }
}

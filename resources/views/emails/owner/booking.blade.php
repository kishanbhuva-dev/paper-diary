@extends('emails.layout')

@section('title', 'New Booking Enquiry')

@section('content')
        <tr>
          <td style="padding:22px 28px 8px;">
            <h2 style="margin:0;">New Booking Enquiry</h2>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e5e7eb;"></td>
        </tr>
        <tr>
          <td style="padding:18px 28px; font-size:14px; line-height:1.6;">
            Dear <strong>{{ $data['ownerName'] ?? 'Property Owner' }}</strong>,<br><br>
            You have received a new booking enquiry for your property <strong>{{ $data['propertyName'] }}</strong>.
          </td>
        </tr>
        <tr>
          <td style="padding:0 28px 18px;">
            <table width="100%" cellpadding="6" style="font-size:14px;">
              <tr>
                <td width="50%">
                  <div style="font-size:12px; color:#777;">GUEST NAME</div>
                  <strong>{{ $data['userName'] }}</strong>
                </td>
                <td width="50%">
                  <div style="font-size:12px; color:#777;">GUEST EMAIL</div>
                  <strong>{{ $data['guestEmail'] }}</strong>
                </td>
              </tr>
              <tr>
                <td>
                  <div style="font-size:12px; color:#777;">CHECK-IN</div>
                  <strong>{{ $data['arrivalDateTime'] }}</strong>
                </td>
                <td>
                  <div style="font-size:12px; color:#777;">CHECK-OUT</div>
                  <strong>{{ $data['departureDateTime'] }}</strong>
                </td>
              </tr>
              <tr>
                <td>
                  <div style="font-size:12px; color:#777;">TOTAL GUESTS</div>
                  <strong>{{ $data['totalAdults'] }} Adults@if($data['totalChildren'] > 0), {{ $data['totalChildren'] }} Children @endif</strong>
                </td>
                <td>
                  <div style="font-size:12px; color:#777;">ROOM TYPE</div>
                  <strong>{{ $data['resourceTypeName'] }}</strong>
                </td>
              </tr>
              @if($data['guestPhone'] && $data['guestPhone'] != '' && $data['guestPhone'] != null)
              <tr>
                <td colspan="2">
                  <div style="font-size:12px; color:#777;">GUEST PHONE</div>
                  <strong>{{ $data['guestPhone'] }}</strong>
                </td>
              </tr>
              @endif
            </table>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e5e7eb;"></td>
        </tr>
        <tr>
          <td style="padding:18px 28px;">
            <div style="font-size:12px; color:#777;">TOTAL BOOKING VALUE</div>
            <div style="font-size:24px; font-weight:bold;">£{{ $data['totalPrice'] }}</div>
          </td>
        </tr>
@endsection

@section('footer_text', 'This enquiry was sent via the Paper-Dairy platform.')

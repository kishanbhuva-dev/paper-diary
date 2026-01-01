@extends('emails.layout')

@section('title', 'Booking Confirmation')

@section('content')
        <tr>
          <td style="padding:22px 28px 8px;">
            <h2 style="margin:0;">Your Stay is Confirmed.</h2>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e5e7eb;"></td>
        </tr>
        <tr>
          <td style="padding:18px 28px; font-size:14px; line-height:1.6;">
            Dear <strong>{{ $data['userName'] }}</strong>,<br><br>
            Your reservation at {{ $data['propertyName'] }} is confirmed from <strong>{{ $data['arrivalDateTime'] }}</strong> to <strong>{{ $data['departureDateTime'] }}</strong>.
          </td>
        </tr>
        <tr>
          <td style="padding:0 28px 18px;">
            <table width="100%" cellpadding="6" style="font-size:14px;">
              <tr>
                <td width="50%">
                  <div style="font-size:12px; color:#777;">CHECK-IN</div>
                  <strong>{{ $data['arrivalDateTime'] }}</strong>
                </td>
                <td width="50%">
                  <div style="font-size:12px; color:#777;">CHECK-OUT</div>
                  <strong>{{ $data['departureDateTime'] }}</strong>
                </td>
              </tr>
              <tr>
                <td>
                  <div style="font-size:12px; color:#777;">ROOM TYPE</div>
                  <strong>{{ $data['resourceTypeName'] }}</strong>
                </td>
                <td>
                  <div style="font-size:12px; color:#777;">TOTAL GUESTS</div>
                  <strong>{{ $data['totalAdults'] }} Adults@if($data['totalChildren'] > 0), {{ $data['totalChildren'] }} Children @endif</strong>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e5e7eb;"></td>
        </tr>
        <tr>
          <td style="padding:18px 28px;">
            <div style="font-size:12px; color:#777;">TOTAL PAID</div>
            <div style="font-size:24px; font-weight:bold;">£{{ $data['totalPrice'] }}</div>
          </td>
        </tr>
@endsection

@section('footer_text', 'This confirmation was sent via the Paper-Dairy platform.')

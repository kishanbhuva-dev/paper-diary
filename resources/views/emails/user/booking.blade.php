<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Booking Confirmation</title>
</head>
<body style="margin:0; padding:0; background:#f2f6fb; font-family:Arial, Helvetica, sans-serif; color:#111;">

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f2f6fb">
  <tr>
    <td align="center" style="padding:24px 12px;">

      <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="border-radius:4px;">

        <tr>
          <td style="padding:16px 20px; background:#e6efff; border-bottom:1px solid #d6e2ff;">
            <table width="100%">
              <tr>
                <td width="36">
                  <img src="https://paper-dairy.eviontech.com/build/assets/main_logo-DkHKnGTV.png" width="28">
                </td>
                <td style="padding-left:10px; font-weight:bold; color:#1e3a8a;">
                  Paper-Dairy
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <tr>
          <td style="padding:22px 28px 8px;">
            <h2 style="margin:0;"> Your Stay is Confirmed. </h2>
          </td>
        </tr>

        <tr><td style="border-top:1px solid #e5e7eb;"></td></tr>

        <tr>
          <td style="padding:18px 28px; font-size:14px; line-height:1.6;">
            Your enquiry for a stay of <strong>{{ $data['totalNights'] ?? 2 }}</strong> nights, arriving on
            <strong>{{ $data['arrivalDateTime'] }}</strong>, at the
            <strong>{{ $data['propertyName'] }}</strong> in
            <strong>United Kingdom</strong> has been successfully emailed to the owner,
            <strong>{{ $data['ownerName'] ?? 'Property Owner' }}</strong>.
          </td>
        </tr>

        <tr>
          <td style="padding:0 28px 18px;">
            <table width="100%" cellpadding="6">
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
                  <div style="font-size:12px; color:#777;">RESOURCE NAME</div>
                  <strong>{{ $data['resourceNames'] ?? 'N/A' }}</strong>
                </td>
              </tr>
              <tr>
                <td>
                  <div style="font-size:12px; color:#777;">TOTAL GUESTS</div>
                  <strong>{{ $data['totalAdults'] }} Adults{{ $data['totalChildren'] > 0 ? ', ' . $data['totalChildren'] . ' Children' : '' }}</strong>
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <tr><td style="border-top:1px solid #e5e7eb;"></td></tr>

        <tr>
          <td style="padding:18px 28px;">
            <div style="font-size:12px; color:#777;">TOTAL PAID</div>
            <div style="font-size:24px; font-weight:bold;">£{{ $data['totalPrice'] ?? 0 }}</div>
          </td>
        </tr>

        <tr>
          <td style="padding:16px 28px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
            <div style="font-size:12px; color:#6b7280;">
              © 2026 Paper-Dairy. All rights reserved.
            </div>
          </td>
        </tr>

      </table>

    </td>
  </tr>
</table>

</body>
</html>
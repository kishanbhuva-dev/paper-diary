<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>New Booking Enquiry</title>
</head>
<body style="margin:0; padding:0; background:#f2f6fb; font-family:Arial, Helvetica, sans-serif; color:#111;">

<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f2f6fb">
  <tr>
    <td align="center" style="padding:24px 12px;">

      <!-- Card -->
      <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="border-radius:4px;">

        <!-- Header -->
        <tr>
          <td style="padding:16px 20px; background:#e6efff; border-bottom:1px solid #d6e2ff;">
            <table width="100%">
              <tr>
                <td width="36">
                  <img
                    src="https://paper-dairy.eviontech.com/build/assets/main_logo-DkHKnGTV.png"
                    width="28"
                    alt="Paper-Dairy"
                  >
                </td>
                <td style="padding-left:10px; font-weight:bold; color:#1e3a8a;">
                  Paper-Dairy
                </td>
              </tr>
            </table>
          </td>
        </tr>

        <!-- Title -->
        <tr>
          <td style="padding:22px 28px 8px;">
            <h2 style="margin:0;">New Booking Enquiry</h2>
          </td>
        </tr>

        <!-- Divider -->
        <tr>
          <td style="border-top:1px solid #e5e7eb;"></td>
        </tr>

        <!-- Intro -->
        <tr>
          <td style="padding:18px 28px; font-size:14px; line-height:1.6;">
            Dear <strong>{{ $data['ownerName'] ?? 'Property Owner' }}</strong>,<br><br>
            You have received a new booking enquiry for your property
            <strong>{{ $data['propertyName'] }}</strong>.
          </td>
        </tr>

        <!-- Guest Details -->
        <tr>
          <td style="padding:0 28px 18px;">
            <table width="100%" cellpadding="6" style="font-size:14px;">
              <tr>
                <td width="50%">
                  <div style="font-size:12px; color:#777;">GUEST NAME</div>
                  <strong>{{ $data['userName'] }}</strong>
                </td>
                <td width="50%">
                  <div style="font-size:12px; color:#777;">COUNTRY</div>
                  <strong>United Kingdom</strong>
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
                  <div style="font-size:12px; color:#777;">NUMBER OF NIGHTS</div>
                  <strong>{{ $data['totalNights'] ?? 2 }} Nights</strong>
                </td>
                <td>
                  <div style="font-size:12px; color:#777;">TOTAL GUESTS</div>
                  <strong>{{ $data['totalAdults'] }} Adults{{ $data['totalChildren'] > 0 ? ', ' . $data['totalChildren'] . ' Children' : '' }}</strong>
                </td>
              </tr>
              <tr>
                <td>
                  <div style="font-size:12px; color:#777;">REQUESTED PITCH</div>
                  <strong>{{ $data['resourceTypeName'] }}</strong>
                </td>
                <td>
                  <div style="font-size:12px; color:#777;">RESOURCES</div>
                  <strong>{{ $data['resourceNames'] ?? 'N/A' }}</strong>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="border-top:1px solid #e5e7eb;"></td>
        </tr>

        <!-- Footer -->
        <tr>
          <td style="padding:16px 28px; background:#f9fafb; text-align:center;">
            <div style="font-size:12px; color:#6b7280;">
              © 2026 <strong>Paper-Dairy</strong>. All rights reserved.<br>
              This enquiry was sent via the Paper-Dairy platform.
            </div>
          </td>
        </tr>

      </table>

    </td>
  </tr>
</table>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>{{ $title ?? 'Email Notification' }}</title>
</head>
<body style="margin:0; padding:0; background:#f2f6fb; font-family:Arial, Helvetica, sans-serif; color:#111;">
<table width="100%" cellpadding="0" cellspacing="0" bgcolor="#f2f6fb">
  <tr>
    <td align="center" style="padding:24px 12px;">
      <table width="600" cellpadding="0" cellspacing="0" bgcolor="#ffffff" style="border-radius:4px;">
        <!-- Header -->
        <tr>
          <td style="padding:16px 20px; background:#e6efff; border-bottom:1px solid #d6e2ff;">
            <table width="100%">
              <tr>
                <td width="36">
                  <img src="https://paper-dairy.eviontech.com/build/assets/main_logo-DkHKnGTV.png" width="28" alt="Paper-Dairy">
                </td>
                <td style="padding-left:10px; font-weight:bold; color:#1e3a8a;">
                  Paper-Dairy
                </td>
              </tr>
            </table>
          </td>
        </tr>
        
        <!-- Content -->
        @yield('content')
        
        <!-- Footer -->
        <tr>
          <td style="padding:16px 28px; background:#f9fafb; border-top:1px solid #e5e7eb; text-align:center;">
            <div style="font-size:12px; color:#6b7280;">
              © {{ date('Y') }} <strong>Paper-Dairy</strong>. All rights reserved.<br>
              @yield('footer_text', 'This notification was sent via the Paper-Dairy platform.')
            </div>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</body>
</html>

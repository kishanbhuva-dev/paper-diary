
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Password Reset</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f7;font-family: Arial, Helvetica, sans-serif;">
  <table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="background-color:#f4f4f7;padding:20px 0;">
    <tr>
      <td align="center">
        <table role="presentation" cellpadding="0" cellspacing="0" width="600" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.08);">
          <!-- Header -->
          <tr>
            <td style="padding:20px 24px;background:#003366;color:#ffffff;text-align:left;">
              <h1 style="margin:0;font-size:20px;font-weight:600;">Paper Diary</h1>
            </td>
          </tr>

          <!-- Body -->
          <tr>
            <td style="padding:28px 24px;color:#333333;">
              <p style="margin:0 0 12px 0;font-size:16px;">Hello <strong>{{ $data['name'] }}</strong>,</p>
              <p style="margin:0 0 18px 0;font-size:15px;line-height:1.5;color:#555;">
                We received a request to reset the password for your account.
                You can reset your password using the link below. If you didn’t make this request, please ignore this email.
              </p>

              <!-- Button -->
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin:18px 0;">
                <tr>
                  <td align="center">
                    <a href="{{ url('/reset-password?token=') . $token }}" target="_blank" style="display:inline-block;padding:12px 20px;border-radius:6px;background:#007bff;color:#ffffff;text-decoration:none;font-weight:600;">
                      Reset Password
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:12px 0 0 0;font-size:14px;color:#666;line-height:1.4;">
                If the button doesn’t work, copy and paste this URL into your browser:
              </p>
              <p style="word-break:break-all;font-size:13px;color:#007bff;margin:8px 0 0 0;">
                <a href="{{ url('/reset-password?token=') . $token }}" target="_blank" style="color:#007bff;text-decoration:underline;">{{ $url . '/reset-password?token=' . $token }}</a>
              </p>

            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="padding:14px 24px;background:#fafafa;color:#999;font-size:12px;text-align:center;">
              <div>© {{ date('Y') }} Paper Diary. All rights reserved.</div>
              <div style="margin-top:6px;">This email was sent for account security purposes.</div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>

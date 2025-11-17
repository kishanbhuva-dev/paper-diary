<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation Confirmed | {{ $data['propertyName'] }}</title>
    <style type="text/css">
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        body { margin: 0; padding: 0; }
        table { border-collapse: collapse !important; }
        .content-table { 
            width: 100% !important; 
            max-width: 600px !important; 
        }
        .header-bg {
            background-color: #f0f0f0;
            border-radius: 8px 8px 0 0;
        }
        .divider { border-top: 1px solid #e0e0e0; }
        .label { 
            font-size: 13px; 
            color: #777777; 
            font-family: Arial, sans-serif; 
            text-transform: uppercase; 
            letter-spacing: 0.5px;
        }
        .value { 
            font-size: 16px; 
            font-weight: bold;
            color: #333333; 
            font-family: Arial, sans-serif; 
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f7f7;">

<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td align="center" style="padding: 20px 0;">
            <table border="0" cellpadding="0" cellspacing="0" class="content-table" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);">
                <tr>
                    <td align="center"style="padding: 5px 5px;">
                        <h1 style="margin: 0; font-family: Arial, sans-serif; font-size: 24px;">
                            {{ $data['propertyName'] }}
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td align="left" style="padding: 30px 40px 10px 40px;">
                        <h2 style="margin: 0; font-family: Arial, sans-serif; color: #333333; font-size: 20px;">
                            Your Stay is Confirmed.
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td align="left" style="padding: 0 40px 30px 40px;">
                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 14px; color: #777777;">
                            Reservation Ref: <strong style="color: #000000; font-weight: bold;">{{ $data['bookingId'] }}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="left" style="padding: 0 40px 20px 40px; border-top: 1px solid #cccccc;">
                        
                        <p style="margin: 20px 0 0 0; font-family: Arial, sans-serif; color: #333333; line-height: 1.6; font-size: 14px;">
                            Dear <b>{{ $data['userName'] }}</b>, your reservation at {{ $data['propertyName'] }} is confirmed from <b>{{ $data['arrivalDateTime'] }}</b> to <b>{{ $data['departureDateTime'] }}</b>.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 10px 40px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td width="50%" style="padding: 10px 0;">
                                    <p class="label" style="margin: 0;">Check-in</p>
                                    <p class="value" style="margin: 5px 0 0 0;">{{ $data['arrivalDateTime'] }}</p>
                                </td>
                                <td width="50%" style="padding: 10px 0;">
                                    <p class="label" style="margin: 0;">Check-out</p>
                                    <p class="value" style="margin: 5px 0 0 0;">{{ $data['departureDateTime'] }}</p>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" style="padding: 10px 0;">
                                    <p class="label" style="margin: 0;">Room Type</p>
                                    <p class="value" style="margin: 5px 0 0 0;">{{ $data['resourceTypeName'] }}</p>
                                </td>
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Total Guests</p>
                                    <p class="value" style="margin: 5px 0 0 0;"> {{$data['totalAdults']}} Adults @if($data['totalChildren'] > 0)  {{$data['totalChildren']}} Children @endif</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td class="divider" style="padding: 20px 20px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="left" style="font-family: Arial, sans-serif;">
                                    <p class="label" style="margin: 0;">Total Paid</p>
                                    <h2 style="margin: 5px 0 0 0; font-size: 24px; color: #000000;">£{{ $data['totalPrice'] }}</h2>
                                </td>  
                                </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
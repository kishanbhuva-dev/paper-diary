<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Notification | {{$data['propertyName']}}</title>
    
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
            font-weight: normal; 
            color: #333333; 
            font-family: Arial, sans-serif; 
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f7f7f7;">

<table border="0" cellpadding="0" cellspacing="5" width="100%">
    <tr>
        <td align="center" style="padding: 40px 0;">
            <table border="0" cellpadding="0" cellspacing="0" class="content-table" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);">                
                <tr>
                    <td align="center"  style="padding: 5px 5px;">
                        <h1 style="margin: 0; font-family: Arial, sans-serif; font-size: 24px; color: #333333;">
                            NEW BOOKING NOTIFICATION
                        </h1>
                    </td>
                </tr>
                <tr>
                    <td align="left" style="padding: 10px 40px 10px 40px;">
                        <h2 style="margin: 0; font-family: Arial, sans-serif; font-size: 20px; ">
                            Booking at {{$data['propertyName']}}
                        </h2>
                    </td>
                </tr>
                <tr>
                    <td align="left" style="padding: 0 40px 30px 40px;">
                        <p style="margin: 0; font-family: Arial, sans-serif; font-size: 14px; color: #777777;">
                            Booking Ref: <strong style="color: #000000; font-weight: bold;">{{$data['bookingId']}}</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 20px 40px 10px 40px; border-top: 1px solid #cccccc;">
                        <h3 style="margin: 0 0 15px 0; font-family: Arial, sans-serif; color: #555555; font-size: 18px;">
                            Guest Information
                        </h3>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Lead Guest</p>
                                    <p class="value" style="margin: 5px 0 0 0; font-weight: bold;">{{$data['userName']}}</p>
                                </td>
                                <!-- Display Phone if available -->
                                 @if($data['guestPhone'] && $data['guestPhone'] != '' && $data['guestPhone'] != null) 
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Contact Phone</p>
                                    <p class="value" style="margin: 5px 0 0 0;">{{$data['guestPhone']}}</p>
                                </td>
                                @endif
                                
                            </tr>
                            <tr>
                                
                                <td colspan="2" style="padding: 10px 0 5px 0;">
                                    <!-- Display Email if available -->
                                    <p class="label" style="margin: 0;">Email Address</p>
                                    <p class="value" style="margin: 5px 0 0 0;">{{$data['guestEmail']}}</p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- 4. RESERVATION DETAILS -->
                <tr>
                    <td style="padding: 20px 40px 10px 40px; border-top: 1px solid #f0f0f0;">
                        <h3 style="margin: 0 0 15px 0; font-family: Arial, sans-serif; color: #555555; font-size: 18px;">
                            Reservation Details
                        </h3>
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Check-in</p>
                                    <p class="value" style="margin: 5px 0 0 0;"> <b>{{$data['arrivalDateTime']}}</b></p>
                                </td>
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Check-out</p>
                                    <p class="value" style="margin: 5px 0 0 0;"> <b>{{$data['departureDateTime']}}</b></p>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Room Type</p>
                                    <p class="value" style="margin: 5px 0 0 0;"> <b>{{$data['resourceTypeName']}}</b></p>
                                </td>
                                <td width="50%" style="padding: 5px 0;">
                                    <p class="label" style="margin: 0;">Total Guests</p>
                                    <p class="value" style="margin: 5px 0 0 0;"> <b>{{$data['totalAdults']}} Adults @if($data['totalChildren'] > 0)  {{$data['totalChildren']}} Children @endif</b></p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                
                <!-- 5. FINANCIAL SUMMARY -->
                <tr>
                    <td class="divider" style="padding: 10px 40px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td align="left" style="font-family: Arial, sans-serif;">
                                    <p class="label" style="margin: 0;">Total Booking Value</p>
                                    <h2 style="margin: 5px 0 0 0; font-size: 24px; color: #000000;">
                                        £{{$data['totalPrice']}}
                                    </h2>
                                </td>
                                
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- FOOTER -->
                
            </table>
        </td>
    </tr>
</table>

</body>
</html>
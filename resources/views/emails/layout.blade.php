<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'e-Tawassul')</title>
</head>
<body style="margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f4f6f9; color:#2c3e50;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background:#f4f6f9; padding:30px 0;">
        <tr>
            <td align="center">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" style="max-width:600px; background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,.06);">
                    <tr>
                        <td style="background:#1a6fa8; padding:24px; text-align:center;">
                            <h1 style="color:#fff; margin:0; font-size:22px; letter-spacing:.5px;">e-Tawassul</h1>
                            <p style="color:#cfe3f3; margin:4px 0 0; font-size:13px;">IIUM Crisis Response System</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px 35px;">
                            @yield('content')
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f0f3f6; padding:18px 35px; font-size:12px; color:#6c757d; text-align:center;">
                            <p style="margin:0;">This is an automated message from e-Tawassul.</p>
                            <p style="margin:6px 0 0;">International Islamic University Malaysia &middot; Student Well-being Initiative</p>
                            <p style="margin:6px 0 0; font-size:11px;">Please do not reply to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

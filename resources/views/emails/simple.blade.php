<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; background:#f5f5f5; font-family:Arial, Helvetica, sans-serif; color:#111827;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f5f5; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px; background:#ffffff; border-radius:20px; overflow:hidden;">
                    <tr>
                        <td style="padding:32px 32px 24px;">
                            <table role="presentation" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="width:48px; height:48px; background:#000000; color:#ffffff; text-align:center; vertical-align:middle; font-size:24px; font-weight:800; border-radius:12px;">
                                        A
                                    </td>
                                    <td style="padding-left:12px; font-size:18px; font-weight:800; color:#111827;">
                                        Asaba Hustle
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin:24px 0 12px; font-size:24px; line-height:1.2; color:#111827;">
                                {{ $heading }}
                            </h1>

                            @foreach ($lines as $line)
                                <p style="margin:0 0 14px; font-size:15px; line-height:1.7; color:#4b5563;">
                                    {{ $line }}
                                </p>
                            @endforeach

                            @if (!empty($actionUrl) && !empty($actionText))
                                <p style="margin:24px 0 0;">
                                    <a href="{{ $actionUrl }}"
                                        style="display:inline-block; background:#000000; color:#ffffff; text-decoration:none; padding:12px 18px; border-radius:12px; font-size:13px; font-weight:700;">
                                        {{ $actionText }}
                                    </a>
                                </p>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

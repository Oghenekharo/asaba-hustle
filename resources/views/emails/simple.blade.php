<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>{{ $subject }}</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; color:#111827; font-family:Arial, Helvetica, sans-serif;">
    @php
        $emailBrand = $siteTheme['primary'] ?? '#0891b2';
        $emailLines = $lines ?? [];
        $emailCode = $code ?? null;
        $emailActionUrl = $actionUrl ?? null;
        $emailActionText = $actionText ?? null;
        $emailSubtitle = $subtitle ?? ($emailCode ? 'Security verification' : 'Account notification');
    @endphp
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f1f5f9; padding:16px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden;">
                    <tr>
                        <td align="center" style="background:{{ $emailBrand }}; padding:27px 24px 25px; color:#ffffff;">
                            <div style="width:52px; height:52px; margin:0 auto 14px; border-radius:12px; background:#ffffff; color:#111827; font-size:15px; line-height:52px; font-weight:800; text-align:center;">AH</div>
                            <div style="font-size:17px; line-height:22px; font-weight:700;">Asaba Hustle</div>
                            <div style="margin-top:3px; font-size:12px; line-height:18px; font-weight:600;">{{ $emailSubtitle }}</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:34px 36px 32px;">
                            <h1 style="margin:0 0 14px; color:#111827; font-size:24px; line-height:1.25; font-weight:700;">{{ $heading }}</h1>

                            @foreach ($emailLines as $line)
                                <p style="margin:0 0 14px; color:#1f2937; font-size:14px; line-height:1.65;">{{ $line }}</p>
                            @endforeach

                            @if ($emailCode)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:20px 0; background:#e5f4f7; border:1px solid #b7e2eb; border-radius:10px;">
                                    <tr>
                                        <td align="center" style="padding:18px 16px;">
                                            <div style="margin-bottom:8px; color:#334155; font-size:10px; line-height:14px; font-weight:700; letter-spacing:1px;">ONE-TIME PASSWORD</div>
                                            <div style="color:{{ $emailBrand }}; font-size:28px; line-height:34px; font-weight:800; letter-spacing:8px;">{{ $emailCode }}</div>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if ($emailActionUrl && $emailActionText)
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" align="center" style="margin:22px auto;">
                                    <tr>
                                        <td align="center" bgcolor="{{ $emailBrand }}" style="border-radius:8px;">
                                            <a href="{{ $emailActionUrl }}" style="display:inline-block; min-width:190px; padding:13px 22px; border-radius:8px; color:#ffffff; text-decoration:none; text-align:center; font-size:12px; line-height:16px; font-weight:700;">{{ $emailActionText }}</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:22px 0 0; color:#1f2937; font-size:13px; line-height:1.6;">Thanks,<br><strong>The Asaba Hustle team</strong></p>
                        </td>
                    </tr>
                </table>

                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:600px;">
                    <tr>
                        <td align="center" style="padding:15px 12px 0; color:#748096; font-size:10px; line-height:17px;">
                            This is an automated account notification.<br>
                            © {{ date('Y') }} Asaba Hustle. All rights reserved.
                            @if (config('mail.from.address'))
                                <br>Need help? <a href="mailto:{{ config('mail.from.address') }}" style="color:{{ $emailBrand }}; text-decoration:none;">{{ config('mail.from.address') }}</a>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

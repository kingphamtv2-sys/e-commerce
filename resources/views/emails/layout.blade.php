<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $emailLog->subject }}</title>
</head>
<body style="margin:0;background:#f1f5f9;color:#0f172a;font-family:Arial,sans-serif">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f1f5f9;padding:24px 12px">
        <tr><td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:680px;background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden">
                <tr><td style="background:#0f172a;color:#fff;padding:22px 28px;font-size:20px;font-weight:700">{{ $storeName }}</td></tr>
                <tr><td style="padding:28px">@yield('content')</td></tr>
                <tr><td style="border-top:1px solid #e2e8f0;padding:18px 28px;color:#64748b;font-size:12px">
                    {{ __('emails.footer') }}
                    @if($supportEmail)<br>{{ __('emails.support', ['email' => $supportEmail]) }}@endif
                </td></tr>
            </table>
        </td></tr>
    </table>
</body>
</html>


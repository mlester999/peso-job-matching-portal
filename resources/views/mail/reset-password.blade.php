<!DOCTYPE html>
<html>
<head>
    <title>PESO Cabuyao OTP</title>
</head>
<body>
    <p>Dear {{ $firstName }},</p>

    <p>You are receiving this email because we received a password reset request for your account.</p>

    @component('mail::button', ['url' => $resetLink])
    Reset Password
    @endcomponent

    <p>The password reset link will expire in 60 minutes.</p>

    <p>If you didn't request this, simply ignore this message.</p>

    <p>Yours,<br>
    PESO Cabuyao</p>
</body>
</html>

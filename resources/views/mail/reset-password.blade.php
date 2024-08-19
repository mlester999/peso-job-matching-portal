<!DOCTYPE html>
<html>
<head>
    <title>PESO Cabuyao OTP</title>
</head>
<body>
    <p>Dear {{ $firstName }},</p>

    <p>Please use the following verification code to reset your password: <strong>{{ $verificationCode }}</strong>.</p>

    <p>Or, click the button below to reset your password:</p>

    @component('mail::button', ['url' => $resetLink])
    Reset Password
    @endcomponent

    <p>If you didn't request this, simply ignore this message.</p>

    <p>Yours,<br>
    PESO Cabuyao</p>
</body>
</html>

<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; background:#f4f4f7; margin:0; padding:24px;">
    <div style="max-width:480px; margin:0 auto; background:#fff; border-radius:10px; padding:32px; text-align:center;">
        <h2 style="margin:0 0 8px;">
            {{ $purpose === 'reset' ? 'Password Reset' : 'Email Verification' }}
        </h2>
        <p style="color:#555; margin:0 0 24px;">
            {{ $purpose === 'reset'
                ? 'Use the code below to reset your password.'
                : 'Use the code below to verify your email and finish creating your account.' }}
        </p>

        <div style="font-size:34px; font-weight:bold; letter-spacing:8px; background:#f0f3ff; color:#3454d1; padding:16px; border-radius:8px;">
            {{ $code }}
        </div>

        <p style="color:#888; font-size:13px; margin-top:24px;">
            This code expires in {{ $expiresMinutes }} minutes. If you didn’t request this, you can ignore this email.
        </p>
    </div>
</body>
</html>

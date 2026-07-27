<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f4f7; margin:0; padding:24px;">
    <div style="max-width:520px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px; text-align:center;">

        <h2 style="margin:0 0 8px; color:#111827; font-size:20px;">
            {{ $purpose === 'reset' ? 'Password reset code' : 'Verify your email' }}
        </h2>

        <p style="color:#4b5563; margin:0 0 26px; line-height:1.6;">
            {{ $purpose === 'reset'
                ? 'Use the code below to set a new password.'
                : 'Use the code below to verify your email and finish creating your account.' }}
        </p>

        <div style="font-size:32px; font-weight:bold; letter-spacing:9px; background:#f0f3ff;
                    border:1px solid #dbe1ff; color:#3454d1; padding:18px; border-radius:10px;
                    font-family:'Courier New', monospace;">
            {{ $code }}
        </div>

        <p style="color:#9ca3af; font-size:13px; margin-top:26px; line-height:1.6;">
            This code expires in {{ $expiresMinutes }} minutes and can only be used once.
            If you did not request it, you can safely ignore this email.
        </p>

        @if ($purpose === 'reset')
            <p style="color:#9ca3af; font-size:13px; margin-top:14px; line-height:1.6;">
                Still stuck? You can ask an administrator to issue a new password for you from the
                login page.
            </p>
        @endif
    </div>
</body>
</html>

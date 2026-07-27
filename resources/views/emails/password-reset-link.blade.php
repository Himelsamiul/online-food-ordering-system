<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f4f7; margin:0; padding:24px;">
    <div style="max-width:540px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px;">

        <h2 style="margin:0 0 8px; color:#111827; font-size:20px;">Set a new password</h2>

        <p style="color:#4b5563; margin:0 0 18px; line-height:1.6;">
            Hi {{ $name }},<br>
            Your password {{ $isAdmin ? 'assistance request has been approved by a super admin' : 'reset request has been approved by an administrator' }}.
            Use the button below to choose a new password for
            <strong>{{ $email }}</strong>.
        </p>

        @if ($note)
            <p style="color:#4b5563; margin:0 0 18px; line-height:1.6; border-left:3px solid #e5e7eb; padding-left:12px;">
                <strong style="color:#111827;">Note from the administrator:</strong><br>
                {{ $note }}
            </p>
        @endif

        <p style="margin:0 0 22px;">
            <a href="{{ $url }}"
               style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none;
                      padding:13px 26px; border-radius:8px; font-weight:bold; font-size:15px;">
                Choose a new password
            </a>
        </p>

        <div style="background:#fffbeb; border:1px solid #fde68a; border-radius:8px; padding:14px 16px; margin-bottom:20px;">
            <p style="margin:0; color:#92400e; font-size:13px; line-height:1.6;">
                This link works <strong>once</strong> and expires in
                <strong>{{ $expiresMinutes }} minutes</strong>. It is tied to this email address —
                nobody else can use it.
            </p>
        </div>

        <p style="color:#6b7280; font-size:12.5px; margin:0 0 6px; line-height:1.6;">
            If the button does not work, copy this address into your browser:
        </p>
        <p style="color:#4f46e5; font-size:12px; margin:0 0 24px; word-break:break-all;">
            {{ $url }}
        </p>

        <p style="color:#9ca3af; font-size:13px; margin:0; line-height:1.6;">
            We never send passwords by email. If you did not ask for this, ignore this message and
            tell an administrator — your current password still works until the link is used.
        </p>
    </div>
</body>
</html>

@php
    $tone = match ($state) {
        'activated'   => ['#059669', '#ecfdf5', '#a7f3d0', 'Account reactivated'],
        'deactivated' => ['#b45309', '#fffbeb', '#fde68a', 'Account deactivated'],
        default       => ['#b91c1c', '#fef2f2', '#fecaca', 'Request not approved'],
    };
    [$colour, $bg, $border, $heading] = $tone;

    $loginUrl = $isAdmin ? route('admin.login') : route('login');
    $helpUrl  = $isAdmin ? route('admin.activation.request') : route('account.help', 'activation');
@endphp
<!DOCTYPE html>
<html>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f4f7; margin:0; padding:24px;">
    <div style="max-width:540px; margin:0 auto; background:#ffffff; border-radius:12px; padding:32px;">

        <div style="background:{{ $bg }}; border:1px solid {{ $border }}; border-radius:10px; padding:16px 18px; margin-bottom:22px;">
            <h2 style="margin:0; color:{{ $colour }}; font-size:18px;">{{ $heading }}</h2>
        </div>

        <p style="color:#4b5563; margin:0 0 16px; line-height:1.6;">Hi {{ $name }},</p>

        <p style="color:#4b5563; margin:0 0 16px; line-height:1.6;">
            @if ($state === 'activated')
                Good news — your {{ $isAdmin ? 'admin' : '' }} account (<strong>{{ $email }}</strong>) has been
                reactivated. You can sign in again right away.
            @elseif ($state === 'deactivated')
                Your {{ $isAdmin ? 'admin' : '' }} account (<strong>{{ $email }}</strong>) has been deactivated,
                so you will not be able to sign in for now. You can ask for it to be switched back on
                from the login page.
            @else
                An administrator has reviewed the request you sent for <strong>{{ $email }}</strong>
                and was not able to approve it.
            @endif
        </p>

        @if ($note)
            <p style="color:#4b5563; margin:0 0 16px; line-height:1.6; border-left:3px solid {{ $border }}; padding-left:12px;">
                <strong style="color:#111827;">Note from the administrator:</strong><br>
                {{ $note }}
            </p>
        @endif

        <p style="margin:24px 0 0;">
            @if ($state === 'activated')
                <a href="{{ $loginUrl }}"
                   style="display:inline-block; background:#059669; color:#ffffff; text-decoration:none;
                          padding:12px 24px; border-radius:8px; font-weight:bold;">
                    Sign in
                </a>
            @else
                <a href="{{ $helpUrl }}"
                   style="display:inline-block; background:#4f46e5; color:#ffffff; text-decoration:none;
                          padding:12px 24px; border-radius:8px; font-weight:bold;">
                    Send another request
                </a>
            @endif
        </p>

        <p style="color:#9ca3af; font-size:13px; margin-top:26px; line-height:1.6;">
            If you have questions about this decision, reply to this email and an administrator will
            get back to you.
        </p>
    </div>
</body>
</html>

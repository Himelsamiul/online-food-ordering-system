<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Minimal user-agent reader.
 *
 * Deliberately not a full UA database: the audit trail needs "which browser,
 * which machine, phone or desktop" to spot an unfamiliar session, and that is
 * answerable from a handful of tokens. Anything finer would mean shipping and
 * maintaining a regex corpus for no operational gain.
 */
class Agent
{
    /**
     * @return array{browser: string, device: string, platform: string, user_agent: string|null}
     */
    public static function parse(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        return [
            'browser'    => self::browser($ua),
            'device'     => self::device($ua),
            'platform'   => self::platform($ua),
            'user_agent' => $userAgent,
        ];
    }

    /** Same, read straight off a request. */
    public static function fromRequest(?Request $request = null): array
    {
        $request = $request ?: request();

        return self::parse($request?->userAgent());
    }

    public static function browser(string $ua): string
    {
        // Order matters: Edge and Opera both advertise Chrome, Chrome
        // advertises Safari. Most specific first.
        return match (true) {
            $ua === ''                          => 'Unknown',
            str_contains($ua, 'Edg/'),
            str_contains($ua, 'Edge/')          => 'Edge',
            str_contains($ua, 'OPR/'),
            str_contains($ua, 'Opera')          => 'Opera',
            str_contains($ua, 'SamsungBrowser') => 'Samsung Internet',
            str_contains($ua, 'Firefox/')       => 'Firefox',
            str_contains($ua, 'CriOS')          => 'Chrome',
            str_contains($ua, 'Chrome/')        => 'Chrome',
            str_contains($ua, 'Safari/')        => 'Safari',
            str_contains($ua, 'curl/')          => 'curl',
            str_contains($ua, 'PowerShell')     => 'PowerShell',
            str_contains($ua, 'bot'),
            str_contains($ua, 'Bot')            => 'Bot',
            default                             => 'Unknown',
        };
    }

    public static function platform(string $ua): string
    {
        return match (true) {
            $ua === ''                        => 'Unknown',
            str_contains($ua, 'Windows NT 10'),
            str_contains($ua, 'Windows NT 11') => 'Windows 10/11',
            str_contains($ua, 'Windows')       => 'Windows',
            str_contains($ua, 'Android')       => 'Android',
            str_contains($ua, 'iPhone'),
            str_contains($ua, 'iPad'),
            str_contains($ua, 'iPod')          => 'iOS',
            str_contains($ua, 'Mac OS X'),
            str_contains($ua, 'Macintosh')     => 'macOS',
            str_contains($ua, 'CrOS')          => 'ChromeOS',
            str_contains($ua, 'Linux')         => 'Linux',
            default                            => 'Unknown',
        };
    }

    public static function device(string $ua): string
    {
        return match (true) {
            $ua === ''                    => 'Unknown',
            str_contains($ua, 'iPad'),
            str_contains($ua, 'Tablet')   => 'Tablet',
            str_contains($ua, 'Mobile'),
            str_contains($ua, 'iPhone'),
            str_contains($ua, 'Android')  => 'Mobile',
            default                       => 'Desktop',
        };
    }

    /** "Chrome on Windows 10/11 (Desktop)" — one line for a table cell. */
    public static function summary(?string $browser, ?string $platform, ?string $device): string
    {
        $parts = array_filter([$browser ?: null, $platform ?: null]);
        $label = $parts ? implode(' on ', $parts) : 'Unknown client';

        return $device ? "{$label} ({$device})" : $label;
    }
}

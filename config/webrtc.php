<?php

/**
 * WebRTC ICE server configuration.
 * STUN is always included; add TURN for restrictive networks (mobile, symmetric NAT, corporate Wi‑Fi).
 */
return [

    'ice_servers' => (static function (): array {
        $servers = [];

        $servers[] = [
            'urls' => [
                'stun:stun.l.google.com:19302',
                'stun:stun1.l.google.com:19302',
                'stun:stun2.l.google.com:19302',
            ],
        ];

        $turnUrlsJson = env('TURN_URLS');
        if (is_string($turnUrlsJson) && $turnUrlsJson !== '') {
            $decoded = json_decode($turnUrlsJson, true);
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    if (is_string($item) && $item !== '') {
                        $entry = ['urls' => $item];
                        $user = env('TURN_USERNAME');
                        $cred = env('TURN_CREDENTIAL');
                        if ($user !== null && $user !== '') {
                            $entry['username'] = $user;
                        }
                        if ($cred !== null && $cred !== '') {
                            $entry['credential'] = $cred;
                        }
                        $servers[] = $entry;
                    }
                }
            }
        }

        $singleUrl = env('TURN_URL');
        if (is_string($singleUrl) && $singleUrl !== '' && ($turnUrlsJson === null || $turnUrlsJson === '')) {
            $entry = ['urls' => $singleUrl];
            $user = env('TURN_USERNAME');
            $cred = env('TURN_CREDENTIAL');
            if ($user !== null && $user !== '') {
                $entry['username'] = $user;
            }
            if ($cred !== null && $cred !== '') {
                $entry['credential'] = $cred;
            }
            $servers[] = $entry;
        }

        return $servers;
    })(),

];

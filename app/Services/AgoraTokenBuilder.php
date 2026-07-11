<?php

namespace App\Services;

use RuntimeException;

/**
 * Builds Agora RTC "Token 2.0" (AccessToken2, version 007) access tokens.
 *
 * Ported field-for-field from Agora's official reference implementation
 * (github.com/AgoraIO/Tools — DynamicKey/AgoraDynamicKey/php/src/AccessToken2.php
 * and RtcTokenBuilder2.php) so no Agora SDK package is required. The signing key
 * is a double-HMAC derivation (appCertificate -> issueTs -> salt), the signed
 * payload is prefixed with the packed appId, and the final blob is zlib-deflated
 * before base64 encoding — all three are required for Agora's servers to accept
 * the token; omitting any of them produces a token that decodes structurally but
 * is rejected at channel join.
 */
class AgoraTokenBuilder
{
    private const VERSION = '007';

    private const PRIVILEGE_JOIN_CHANNEL = 1;
    private const PRIVILEGE_PUBLISH_AUDIO_STREAM = 2;
    private const PRIVILEGE_PUBLISH_VIDEO_STREAM = 3;
    private const PRIVILEGE_PUBLISH_DATA_STREAM = 4;

    private const SERVICE_TYPE_RTC = 1;

    /**
     * @param string $channelName Agora channel name.
     * @param int $uid Numeric RTC uid (0 lets Agora assign one on join).
     * @param int $expireSeconds How long the token/privileges remain valid for, from now.
     */
    public static function buildRtcToken(string $channelName, int $uid, int $expireSeconds = 3600): string
    {
        return self::build($channelName, (string) $uid, $expireSeconds);
    }

    /**
     * @param string $channelName Agora channel name.
     * @param string $userAccount Non-numeric RTC user account (Agora "userAccount" auth mode).
     * @param int $expireSeconds How long the token/privileges remain valid for, from now.
     */
    public static function buildRtcTokenWithAccount(string $channelName, string $userAccount, int $expireSeconds = 3600): string
    {
        if ($userAccount === '') {
            throw new RuntimeException('userAccount must not be empty.');
        }

        return self::build($channelName, $userAccount, $expireSeconds);
    }

    private static function build(string $channelName, string $uidOrAccount, int $expireSeconds): string
    {
        $appId = (string) config('services.agora.app_id');
        $appCertificate = (string) config('services.agora.app_certificate');

        if (!$appId || !$appCertificate) {
            throw new RuntimeException('Agora is not configured. Set AGORA_APP_ID and AGORA_APP_CERTIFICATE.');
        }

        $issueTs = time();
        $salt = random_int(1, 99999999);

        $privileges = [
            self::PRIVILEGE_JOIN_CHANNEL => $expireSeconds,
            self::PRIVILEGE_PUBLISH_AUDIO_STREAM => $expireSeconds,
            self::PRIVILEGE_PUBLISH_VIDEO_STREAM => $expireSeconds,
            self::PRIVILEGE_PUBLISH_DATA_STREAM => $expireSeconds,
        ];

        $servicePacked = self::packUint16(self::SERVICE_TYPE_RTC)
            . self::packMapUint32($privileges)
            . self::packString($channelName)
            . self::packString($uidOrAccount);

        $data = self::packString($appId)
            . self::packUint32($issueTs)
            . self::packUint32($expireSeconds)
            . self::packUint32($salt)
            . self::packUint16(1)
            . $servicePacked;

        $signingKey = hash_hmac('sha256', $appCertificate, self::packUint32($issueTs), true);
        $signingKey = hash_hmac('sha256', $signingKey, self::packUint32($salt), true);

        $signature = hash_hmac('sha256', $data, $signingKey, true);

        $compressed = zlib_encode(self::packString($signature) . $data, ZLIB_ENCODING_DEFLATE);

        return self::VERSION . base64_encode($compressed);
    }

    private static function packUint16(int $value): string
    {
        return pack('v', $value);
    }

    private static function packUint32(int $value): string
    {
        return pack('V', $value);
    }

    private static function packString(string $value): string
    {
        return self::packUint16(strlen($value)) . $value;
    }

    private static function packMapUint32(array $map): string
    {
        ksort($map);
        $packed = self::packUint16(count($map));
        foreach ($map as $key => $value) {
            $packed .= self::packUint16($key) . self::packUint32($value);
        }
        return $packed;
    }
}

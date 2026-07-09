<?php

namespace App\Services;

use RuntimeException;

/**
 * Builds Agora RTC "Token 2.0" (version 007) access tokens.
 *
 * This is a from-scratch implementation of Agora's published Token004/AccessToken2
 * binary format (see AgoraIO/Tools token builder samples) so no Agora SDK package
 * is required. Not verifiable end-to-end until real Agora credentials are configured
 * (config('services.agora')) — verify against Agora Console's token tester once available.
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
        $appId = config('services.agora.app_id');
        $appCertificate = config('services.agora.app_certificate');

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
            . self::packString((string) $uid);

        $content = self::packUint32($issueTs)
            . self::packUint32($expireSeconds)
            . self::packUint32($salt)
            . self::packUint16(1)
            . $servicePacked;

        $signature = hash_hmac('sha256', $content, $appCertificate, true);

        $payload = self::packString($signature) . $content;

        return self::VERSION . base64_encode($appId . $payload);
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

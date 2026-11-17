<?php

namespace App\Core;

use RuntimeException;

/**
 * Генерация и проверка одноразовых кодов TOTP (RFC 6238) для
 * двухфакторной аутентификации. Совместимо с Google Authenticator,
 * Яндекс.Ключом и другими приложениями-аутентификаторами.
 */
class Totp
{
    private const BASE32_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    private const PERIOD = 30;
    private const DIGITS = 6;

    /**
     * Сгенерировать случайный секрет в формате base32.
     */
    public static function generateSecret(int $bytes = 20): string
    {
        try {
            $random = random_bytes($bytes);
        } catch (\Throwable $error) {
            throw new RuntimeException('Не удалось сгенерировать секрет.', 0, $error);
        }
        return self::base32Encode($random);
    }

    /**
     * Текущий 6-значный код для секрета.
     */
    public static function code(string $base32Secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $timeSlice = intdiv($timestamp, self::PERIOD);

        $binaryCounter = pack('N2', ($timeSlice >> 32) & 0xFFFFFFFF, $timeSlice & 0xFFFFFFFF);
        $hash = hash_hmac('sha1', $binaryCounter, self::base32Decode($base32Secret), true);

        // Динамическое усечение по RFC 4226
        $offset = ord(substr($hash, -1)) & 0x0F;
        $value = (ord($hash[$offset]) & 0x7F) << 24;
        $value |= (ord($hash[$offset + 1]) & 0xFF) << 16;
        $value |= (ord($hash[$offset + 2]) & 0xFF) << 8;
        $value |= ord($hash[$offset + 3]) & 0xFF;

        return str_pad((string) ($value % (10 ** self::DIGITS)), self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Проверить код с допуском в плюс-минус window периодов (30 секунд каждый).
     */
    public static function verify(string $base32Secret, string $code, int $window = 1): bool
    {
        $code = preg_replace('/\D/', '', $code) ?? '';
        if (strlen($code) !== self::DIGITS || !ctype_digit($code)) {
            return false;
        }

        $timestamp = time();
        for ($shift = -$window; $shift <= $window; $shift++) {
            $expected = self::code($base32Secret, $timestamp + $shift * self::PERIOD);
            if (hash_equals($expected, $code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ссылка otpauth:// для добавления секрета в приложение-аутентификатор.
     */
    public static function uri(string $base32Secret, string $label, string $issuer = 'МойБлог'): string
    {
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($label)
            . '?secret=' . $base32Secret
            . '&issuer=' . rawurlencode($issuer)
            . '&algorithm=SHA1&digits=' . self::DIGITS . '&period=' . self::PERIOD;
    }

    private static function base32Encode(string $bytes): string
    {
        $output = '';
        $bitBuffer = 0;
        $bitCount = 0;

        for ($i = 0, $length = strlen($bytes); $i < $length; $i++) {
            $bitBuffer = ($bitBuffer << 8) | ord($bytes[$i]);
            $bitCount += 8;
            while ($bitCount >= 5) {
                $bitCount -= 5;
                $output .= self::BASE32_ALPHABET[($bitBuffer >> $bitCount) & 0x1F];
            }
        }
        if ($bitCount > 0) {
            $output .= self::BASE32_ALPHABET[($bitBuffer << (5 - $bitCount)) & 0x1F];
        }
        return $output;
    }

    private static function base32Decode(string $value): string
    {
        $value = strtoupper(preg_replace('/[^a-zA-Z2-7]/', '', $value) ?? '');
        $output = '';
        $bitBuffer = 0;
        $bitCount = 0;

        for ($i = 0, $length = strlen($value); $i < $length; $i++) {
            $position = strpos(self::BASE32_ALPHABET, $value[$i]);
            if ($position === false) {
                throw new RuntimeException('Некорректный секрет base32.');
            }
            $bitBuffer = ($bitBuffer << 5) | $position;
            $bitCount += 5;
            if ($bitCount >= 8) {
                $bitCount -= 8;
                $output .= chr(($bitBuffer >> $bitCount) & 0xFF);
            }
        }
        return $output;
    }
}

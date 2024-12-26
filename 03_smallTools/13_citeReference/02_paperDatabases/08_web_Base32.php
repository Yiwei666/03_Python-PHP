<?php
// 08_web_Base32.php

class Base32
{
    private static $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * 将输入字符串进行 Base32 编码
     * @param string $input
     * @return string
     */
    public static function encode($input)
    {
        if (empty($input)) return '';

        $binary = '';
        // 将每个字符转换为其二进制表示
        for ($i = 0; $i < strlen($input); $i++) {
            $binary .= str_pad(decbin(ord($input[$i])), 8, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串填充到5的倍数
        $binary = str_pad($binary, ceil(strlen($binary) / 5) * 5, '0', STR_PAD_RIGHT);

        $base32 = '';
        for ($i = 0; $i < strlen($binary); $i += 5) {
            $chunk = substr($binary, $i, 5);
            // 如果最后一组不足5位，则补0
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $index = bindec($chunk);
            $base32 .= self::$alphabet[$index];
        }

        // 添加填充
        $padding = 8 - (strlen($base32) % 8);
        if ($padding !== 8) {
            $base32 .= str_repeat('=', $padding);
        }

        return $base32;
    }

    /**
     * 将 Base32 编码的字符串解码回原始字符串
     * @param string $input
     * @return string|false
     */
    public static function decode($input)
    {
        if (empty($input)) return '';

        // 移除填充字符
        $input = strtoupper($input);
        $input = rtrim($input, '=');

        $binary = '';
        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];
            $index = strpos(self::$alphabet, $char);
            if ($index === false) {
                // 无效的Base32字符
                return false;
            }
            $binary .= str_pad(decbin($index), 5, '0', STR_PAD_LEFT);
        }

        // 将二进制字符串转换回原始字符串
        $decoded = '';
        for ($i = 0; $i < strlen($binary); $i += 8) {
            $byte = substr($binary, $i, 8);
            if (strlen($byte) < 8) {
                // 忽略不完整的字节
                break;
            }
            $decoded .= chr(bindec($byte));
        }

        return $decoded;
    }
}

<?php
/**
 *  自定义的一个加解密插件
 * User: staff
 * Date: 2019/3/27
 * Time: 16:13
 */


class Encrypter
{
    private static $key = "ZmUbqPipJ0Pr7tGmkHDBazjpbKjFZc7S";
    private static $cipher = "AES-256-CBC";

    public static function encrypt($str)
    {
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $value = openssl_encrypt($str, self::$cipher, self::$key, 0, $iv);
        $iv = base64_encode($iv);
        $param = [
            'iv' => $iv,
            'value' => $value
        ];
        return base64_encode(json_encode($param));


    }

    public static function decrypt($str)
    {
        $payload = self::getJsonPayload($str);
        $iv = base64_decode($payload['iv']);
        $decrypted = openssl_decrypt($payload['value'], self::$cipher, self::$key, 0, $iv);
        return $decrypted;

    }

    /**
     * @param $payload
     * @return array
     */
    static function getJsonPayload($payload)
    {
        $payload = json_decode(base64_decode($payload), true);
        return $payload;
    }

}
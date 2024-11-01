<?php

namespace MoUserSync\Handler;

class EncryptionHandler
{

    public static function mo_user_sync_encrypt_data($data, $key)
    {
        $key = openssl_digest($key, 'sha256');
        $method = 'aes-128-ecb';
        $strCrypt = openssl_encrypt($data, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING);
        return base64_encode($strCrypt);
    }

    public static function mo_user_sync_decrypt_data($data, $key)
    {
        $strIn = base64_decode($data);
        $key = openssl_digest($key, 'sha256');
        $method = 'AES-128-ECB';
        $ivSize = openssl_cipher_iv_length($method);
        $iv = substr($strIn, 0, $ivSize);
        $data = substr($strIn, $ivSize);
        $clear = openssl_decrypt($data, $method, $key, OPENSSL_RAW_DATA || OPENSSL_ZERO_PADDING, $iv);

        return $clear;
    }
}
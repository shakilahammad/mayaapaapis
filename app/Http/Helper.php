<?php

namespace App\Http;

use Illuminate\Encryption\Encrypter;

class Helper
{
    public static function maya_encrypt($data)
    {
        $encryption_key = base64_decode(\Config::get('config.E_KEY'));
        $iv = 1245891314192026;
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv);
        return base64_encode($encrypted . '::' . $iv);
    }

    public static function maya_decrypt($data)
    {
        if (!empty($data)) {
            $encryption_key = base64_decode(\Config::get('config.E_KEY'));
            list($encrypted_data, $iv) = explode('::', base64_decode($data), 2);
            return openssl_decrypt($encrypted_data, 'AES-256-CBC', $encryption_key, 0, $iv);
        }

        return null;
    }

    public static function dcrypt($data){
        if (!empty($data)) {
            $newEncrypter = new Encrypter(\Config::get('config.E_KEY'), \Config::get('app.cipher'));
            return $newEncrypter->decrypt($data);
        }

        return null;
    }

}

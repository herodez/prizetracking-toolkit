<?php

namespace App\Cryptography;

Class DataEncryption
{
    /**
     * @var array encryption parameters
     */
    private static $parameters = [
        'cipher' => 'aes-256-cbc',
        'sha' => 'sha512',
        'iterations_length' => 10,
        'key_length' => 64,
    ];
    
    /**
     * Encrypt a data pass with a key.
     *
     * @param $data
     * @param $key
     * @return array
     * @throws \Exception
     */
    public static function encrypt($data, $key)
    {
        $salt = random_bytes(64);
        $iv = random_bytes(16);
        
        $securityKey = hash_pbkdf2(self::$parameters['sha'], $key, $salt, self::$parameters['iterations_length'],
            self::$parameters['key_length']);
        $dataEncrypt = openssl_encrypt(
            $data,
            self::$parameters['cipher'],
            hex2bin($securityKey),
            OPENSSL_RAW_DATA,
            $iv
        );
        
        return [
            'data' => base64_encode($dataEncrypt),
            'decryptParameters' => self::serializeRandomParameters([
                'salt' => $salt,
                'iv' => $iv
            ])
        ];
    }
    
    /**
     * Decrypt a data pass with key and encryption ['salt', 'iv'] parameters.
     *
     * @param $data
     * @param $key
     * @param $decryptParameters
     * @return string
     */
    public static function decrypt($data, $key, $decryptParameters)
    {
        $data = base64_decode($data);
        $randomParameters = self::unserializeRandomParameters($decryptParameters);
        $salt = base64_decode($randomParameters['salt']);
        $iv = base64_decode($randomParameters['iv']);
        
        $securityKey = hash_pbkdf2(self::$parameters['sha'], $key, $salt, self::$parameters['iterations_length'],
            self::$parameters['key_length']);
        
        return openssl_decrypt(
            $data,
            self::$parameters['cipher'],
            hex2bin($securityKey),
            OPENSSL_RAW_DATA,
            $iv
        );
    }
    
    /**
     * Return Unserialize random parameters.
     *
     * @param $randomParameters
     * @return mixed
     */
    private static function unserializeRandomParameters($randomParameters)
    {
        return json_decode(base64_decode($randomParameters), true);
    }
    
    /**
     * Return Serialize random parameters.
     *
     * @param $parameters
     * @return string
     */
    private static function serializeRandomParameters($parameters)
    {
        return base64_encode(json_encode([
            'salt' => base64_encode($parameters['salt']),
            'iv' => base64_encode($parameters['iv'])
        ]));
    }
}
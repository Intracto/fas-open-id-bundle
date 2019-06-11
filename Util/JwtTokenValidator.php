<?php

namespace Intracto\FasOpenIdBundle\Util;

use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JwtTokenValidator
{
    /**
     * @param string $token
     * @param array  $keys
     *
     * @return object
     */
    public static function validateToken(string $token, array $keys)
    {
        list($header, $payload, $signature) = explode('.', $token);
        $tokenData = $header.'.'.$payload;

        $parsedPayload = json_decode(self::sanitizeAndBase64Decode($payload), false);
        $decodedHeader = json_decode(self::sanitizeAndBase64Decode($header), false);
        $keys = array_filter($keys, static function (object $key) use ($decodedHeader) {
            return $key->kid === $decodedHeader->kid;
        });

        if (empty($keys)) {
            throw new \RuntimeException('No valid public key was given');
        }

        // Format data
        $signature = self::sanitizeAndBase64Decode($signature);
        $n = self::sanitizeAndBase64Decode($keys[0]->n);
        $e = self::sanitizeAndBase64Decode($keys[0]->e);

        $rsa = new RSA();
        $rsa->loadKey([
            'n' => new BigInteger($n, 256),
            'e' => new BigInteger($e, 256),
        ]);

        $rsa->setHash('sha256');
        $rsa->setSignatureMode(2);

        if (!$rsa->verify($tokenData, $signature)) {
            throw new AuthenticationException('Verification failed');
        }

        return $parsedPayload;
    }

    /**
     * @param string $str
     *
     * @return string
     */
    private static function sanitizeAndBase64Decode(string $str): string
    {
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $str), true);
    }
}

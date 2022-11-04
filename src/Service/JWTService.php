<?php

namespace App\Service;

use DateTimeImmutable;

class JWTService
{
    // generate JWT     -   10800s = 3H
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string
    {
        if($validity > 0) {
            /** get the expiration date */
            $now = new DateTimeImmutable();
            $exp = $now->getTimestamp() + $validity;
    
            $payload['iat'] = $now->getTimestamp();
            $payload['exp'] = $exp;
        }


        /** encode in base64 */
        $base64Header = base64_encode(json_encode($header));
        $base64Payload = base64_encode(json_encode($payload));

        /** clean encoded values (retrait des +, / et =) */
        /** car en base64 on a ces signes là, alors dans json web token we don't use them */
        $base64Header = str_replace(['+', '/', '='], ['-', '_', ''], $base64Header);
        $base64Payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64Payload);

        /** generate signature - we need secret */
        $secret = base64_encode($secret);

        $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, $secret, true);

        $base64Signature = base64_encode($signature);
        
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        /** create token */
        $jwt = $base64Header . '.' . $base64Payload . '.' . $base64Signature;

        return $jwt;
    }



    /** check if token is valid (correctly formed) */
    public function isValid(string $token): bool
    {
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    /** récupérer le payload */
    public function getPayload(string $token): array
    {
        /** démonter le token */
        $array = explode('.', $token);
        /** decode le payload (2nd part du token) */
        $payload = json_decode(base64_decode($array[1]), true);

        return $payload;
    }

    /** récupérer le header */
    public function getHeader(string $token): array
    {
        /** démonter le token */
        $array = explode('.', $token);
        /** decode le header (1st part du token) */
        $header = json_decode(base64_decode($array[0]), true);

        return $header;
    }


    /** check if token is expired */
    public function isExpired(string $token): bool
    {
        $payload = $this->getPayload($token);

        $now = new DateTimeImmutable();

        return $payload['exp'] < $now->getTimestamp();
    }

    /** check signature of the token */
    public function check(string $token, string $secret)
    {
        /** récupérer le header et le payload */
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        /** regénérer un token */
        $verifToken = $this->generate($header, $payload, $secret, 0);

        return $token === $verifToken;
    }

}
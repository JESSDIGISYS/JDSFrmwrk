<?php

namespace JDS\Http\Middleware\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secretKey;
    private string $algorithm;
    public function __construct(string $secretKey, string $algorithm = 'HS256')
    {
        $this->secretKey = $secretKey;
        $this->algorithm = $algorithm;
    }

    public function encode(array $payload): string
    {
        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function decode(string $jwt): \stdClass
    {
        return JWT::decode($jwt, new Key($this->secretKey, $this->algorithm));
    }

    public function getTimestamp(): int
    {
        return time();
    }
}



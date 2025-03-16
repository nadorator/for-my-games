<?php

namespace App\Infrastructure\Auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

class JwtService
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @var int
     */
    private $expiration;

    /**
     * Constructor
     */
    public function __construct(string $secret, int $expiration)
    {
        $this->secret = $secret;
        $this->expiration = $expiration;
    }

    /**
     * Generate JWT token for editor
     *
     * @param string $editorId
     * @param string $role
     * @return string
     */
    public function generateToken(string $editorId, string $role): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->expiration;

        $payload = [
            'iss' => 'api.videogamecontent.com',  // Issuer
            'aud' => 'api.videogamecontent.com',  // Audience
            'iat' => $issuedAt,                  // Issued at
            'exp' => $expiresAt,                 // Expiration
            'editor_id' => $editorId,            // Editor ID
            'role' => $role                      // Editor role
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Validate JWT token
     *
     * @param string $token
     * @return stdClass|null
     */
    public function validateToken(string $token): ?stdClass
    {
        try {
            return JWT::decode($token, new Key($this->secret, 'HS256'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get expiration date
     *
     * @return string
     */
    public function getExpirationDate(): string
    {
        return date('Y-m-d\TH:i:s\Z', time() + $this->expiration);
    }
} 
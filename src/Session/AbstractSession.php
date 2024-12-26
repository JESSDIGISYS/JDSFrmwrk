<?php

namespace JDS\Session;

use JDS\Authentication\RuntimeException;

class AbstractSession
{

    protected function configuration(): void
    {
        // Set session save path.
        $savePath = getenv('SESSION_SAVE_PATH') ?: ini_get('session.save_path');

        // Ensure the session save path exists.
        if (!is_dir($savePath) && !mkdir($savePath, 0777, true) && !is_dir($savePath)) {
            // If the directory cannot be created, throw an error.
            throw new RuntimeException('Failed to create session save path: ' . $savePath);
        }

// Ensure the session save path is writable.
        if (!is_writable($savePath)) {
            throw new RuntimeException('Session save path is not writable: ' . $savePath);
        }

// Apply the session save path.
        session_save_path($savePath);

// Set session cookie parameters.
        session_set_cookie_params([
            'lifetime' => getenv('SESSION_COOKIE_LIFETIME') ?: 900,          // Default to 15 minutes.
            'path' => getenv('SESSION_COOKIE_PATH') ?: '/',               // Default to root path.
            'domain' => getenv('SESSION_COOKIE_DOMAIN') ?: '',            // Default to current domain.
            'secure' => getenv('SESSION_COOKIE_SECURE') === 'true',       // True for secure (HTTPS).
            'httponly' => getenv('SESSION_COOKIE_HTTPONLY') === 'true',   // True for HTTP-only cookies.
            'samesite' => getenv('SESSION_COOKIE_SAMESITE') ?: 'Lax',     // Default to "Lax".
        ]);

    }
}
<?php

namespace JDS\Authentication;

class AbstractSession
{

    protected function configuration(): void
    {
        // Set session save path.
        $savePath = ini_get('session.save_path');

//        // Ensure the session save path exists.
//        if (!is_dir($savePath) && !mkdir($savePath, 0777, true) && !is_dir($savePath)) {
//            // If the directory cannot be created, throw an error.
//            throw new RuntimeException('Failed to create session save path: ' . $savePath);
//        }
//
//        // Ensure the session save path is writable.
//        if (!is_writable($savePath)) {
//            throw new RuntimeException('Session save path is not writable: ' . $savePath);
//        }

        // Apply the session save path.
        session_save_path($savePath);

        // Set session cookie parameters.
    }

    protected function resetCookie(): void
    {
        setcookie(
            session_name(),
            '',
            time() - 42000,
            '/'
        );

//        session_set_cookie_params([
//            'lifetime' => ini_get('session.cookie_lifetime') ?? 300,          // Default to 5 minutes.
//            'path' => ini_get('session.save_path'),                  // Default to root path.
//            'domain' => ini_get('session.cookie_domain') ?? 'localhost',            // Default to current domain.
//            'secure' => ini_get('session.cookie_secure') === "true" ?? false,       // True for secure (HTTPS).
//            'httponly' => ini_get('session.cookie_httponly'),   // True for HTTP-only cookies.
//            'samesite' => ini_get('session.cookie_samesite'),     // Default to "Lax".
//        ]);

    }
}


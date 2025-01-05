<?php

namespace JDS\Session;

use JDS\Authentication\RuntimeException;
use Random\RandomException;

class Session extends AbstractSession implements SessionInterface
{
    private const FLASH_KEY = 'flash';
    public const AUTH_KEY = 'auth_id';
    public const ACCESS_TOKEN = 'access_token';
    public const REFRESH_TOKEN = 'refresh_token';

    /**
     * @throws RuntimeException
     * @throws RandomException
     */
    public function start(): void
    {

        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        $this->configuration();

        session_start();

        if (!$this->has('csrf_token')) {
            $this->set('csrf_token', bin2hex(random_bytes(32)));
        }
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function getFlash(string $type): array
    {
        $flash = $this->get(self::FLASH_KEY) ?? [];
        if (isset($flash[$type])) {
            $messages = $flash[$type];
            unset($flash[$type]);
            $this->set(self::FLASH_KEY, $flash);
            return $messages;
        }
        return [];
    }

    public function setFlash(string $type, string $message): void
    {
        $flash = $this->get(self::FLASH_KEY) ?? [];
        $flash[$type][] = $message;
        $this->set(self::FLASH_KEY, $flash);
    }

    public function hasFlash(string $type): bool
    {
        return isset($_SESSION[self::FLASH_KEY][$type]);
    }

    public function clearFlash(): void
    {
        unset($_SESSION[self::FLASH_KEY]);
    }

    public function isAuthenticated(): bool
    {
        return $this->has(self::AUTH_KEY);
    }

    public function isNotAuthenticated(): bool
    {
        return !$this->isAuthenticated();
    }

    public function destroy(): void
    {
        // Clear all session data
        $this->clear();

        // Unset the session cookie, if it exists
        $this->invalidateSessionCookie();
    }

    private function invalidateSessionCookie(): void
    {
        // Check if cookies are being used
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            // Invalidate the session cookie by setting its expiration to a past time
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
    }
    public function clear(): void
    {
        $_SESSION = [];
    }

    public function resetSessionState(): void
    {
        $authId = self::AUTH_KEY; // Preserve the current user's ID

        $this->clear(); // Clears all session data

        // Restore the authentication key to keep the user logged in
        $this->set(Session::AUTH_KEY, $authId);
    }
}



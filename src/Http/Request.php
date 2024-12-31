<?php
/*
 * Copyright (c) 2024 Jessop Digital Systems All Rights Reserved
 *
 */

namespace JDS\Http;

use Exception;
use JDS\Session\SessionInterface;
use Throwable;

class Request
{
    private SessionInterface $session;
    private mixed $routeHandler;
    private array $routeHandlerArgs;

    public function __construct(
        public array $getParams, // $_GET
        public array $postParams = [], // $_POST
        public array $cookies = [], // $_COOKIE
        public array $files = [], // $_FILES
        public array $server = [] // $_SERVER
    )
    {
    }

    public static function createFromGlobals(): static
    {
        return new static($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
    }

    public function getPathInfo(): string
    {
        return strtok($this->server['REQUEST_URI'], '?');
    }

    public function getMethod(): string
    {
        return $this->server['REQUEST_METHOD'];
    }

    public function getSession(): SessionInterface
    {
        return $this->session;
    }

    public function setSession(SessionInterface $session): void
    {
        $this->session = $session;
    }

    public function postInput($key): mixed
    {
        return $this->postParams[$key] ?? null;
    }

    public function getRouteHandler(): mixed
    {
        return $this->routeHandler;
    }

    /**
     * @throws Exception
     */
    public function setRouteHandler(mixed $routeHandler): void
    {
        try {
            if (empty($routeHandler)) {
                throw new Exception('Route handler cannot be empty.');
            }
            $this->routeHandler = $routeHandler;
        } catch (Throwable $exception) {
            throw new Exception('Error setting route handler: ' . $exception->getMessage());
        }
    }

    public function getRouteHandlerArgs(): array
    {
        return $this->routeHandlerArgs;
    }

    /**
     * @throws Exception
     */
    public function setRouteHandlerArgs(array $routeHandlerArgs): void
    {
        $this->routeHandlerArgs = $routeHandlerArgs;
    }

    public function getServerVariable(string $serverVariable): ?string
    {
        return $this->server[$serverVariable] ?? null;
    }
}


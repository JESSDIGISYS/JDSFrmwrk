<?php

namespace JDS\Http;

use JDS\Session\SessionInterface;

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

	public function input($key): mixed
	{
		if (array_key_exists($key, $this->postParams)) {
			return $this->postParams[$key];
		}
		return '';
	}

	public function getRouteHandler(): mixed
	{
		return $this->routeHandler;
	}

	public function setRouteHandler(mixed $routeHandler): void
	{
		$this->routeHandler = $routeHandler;
	}

	public function getRouteHandlerArgs(): array
	{
		return $this->routeHandlerArgs;
	}

	public function setRouteHandlerArgs(array $routeHandlerArgs): void
	{
		$this->routeHandlerArgs = $routeHandlerArgs;
	}

	public function getServerVariable(string $serverVariable): ?string
	{
		return $this->server[$serverVariable] ?? null;
	}

}


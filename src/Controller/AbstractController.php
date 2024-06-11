<?php

namespace JDS\Framework\Controller;

use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
	protected ?ContainerInterface $container = null;
	protected Request $request;
	public function setContainer(ContainerInterface $container): void
	{
		$this->container = $container;
	}

	public function setRequest(Request $request): void
	{
		$this->request = $request;
	}

	public function render(string $template, array $parameters= [], Response $response = null):
	Response
	{
		$content = $this->container->get('twig')->render($template, $parameters);

		$response ??= new Response();

		$response->setContent($content);

		return $response;
	}


}
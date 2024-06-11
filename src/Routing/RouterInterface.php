<?php

namespace JDS\Framework\Routing;

use JDS\Framework\Http\Request;
use Psr\Container\ContainerInterface;

interface RouterInterface
{
	public function dispatch(Request $request, ContainerInterface $container);
}


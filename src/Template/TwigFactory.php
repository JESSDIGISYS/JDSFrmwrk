<?php

namespace JDS\Framework\Template;

use JDS\Framework\Session\SessionInterface;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TwigFactory
{
	public function __construct(
		private SessionInterface $session,
		private string $templatesPath
	)
	{
	}

	public function create(): Environment
	{
//		dd(dirname(__DIR__, 3) . '/templates');
		// instantiate FileSysteLoader with templates path
		$loader = new FilesystemLoader(dirname(__DIR__,3) . '/templates');

		// instantiate Twig Environment with loader
		$twig = new Environment($loader, [
			'debug' => true,
			'cache' => false,
		]);

		$baseDir = '/';

		// add new twig session() funciton to Envirnment
		$twig->addExtension(new DebugExtension());
		$twig->addExtension(new AssetExtension($baseDir));
		$twig->addFunction(new TwigFunction('session', [$this, 'getSession']));

		return $twig;
	}

	public function getSession(): SessionInterface
	{
		return $this->session;
	}

//	public function assetFunction($path): string {
//		return dirname(__DIR__, 3) . '/public/' . $path;
//	}
}
<?php

namespace JDS\Template;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AssetExtension extends AbstractExtension
{
	private string $basePath;

	public function __construct(string $basePath)
	{
		$this->basePath = rtrim($basePath, '/');
	}

	public function getFunctions(): array {
		return [
			new TwigFunction('asset', [$this, 'asset']),
		];
	}

	public function asset($path): string
	{
		return $this->basePath . '/' . ltrim($path, '/');
	}

}
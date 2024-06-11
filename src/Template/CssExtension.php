<?php

namespace JDS\Framework\Template;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CssExtension extends AbstractExtension
{
	public function getFunctions(): array
	{
		return [
			new TwigFunction("inline_css", [$this, "inlineCss"]),
		];
	}

	public function inlineCss(string $filePath): string|bool
	{
		return $this->getCssContent($filePath);
	}

	private function getCssContent($filePath): string|bool
	{
		if (file_exists($filePath)) {
			return file_get_contents($filePath);
		}
		return '';
	}
}
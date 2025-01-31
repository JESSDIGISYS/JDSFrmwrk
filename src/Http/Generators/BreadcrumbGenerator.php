<?php

namespace JDS\Http\Generators;

use JDS\Routing\BreadcrumbGeneratorInterface;
use League\Container\Container;

class BreadcrumbGenerator implements BreadcrumbGeneratorInterface
{
	public function __construct(private readonly array $routes, private readonly string $routePrefix)
	{
	}

    public function generateBreadcrumbs(): array
    {
        $currentPath = $this->getURI();
        $breadcrumbs = [];
        $path = $currentPath;

        while ($path !== null) {
            $matched = false; // Indicator if current path got a match in the route definitions

            foreach ($this->routes as $route) {
                // Skip if not a 'GET' route
                if ($route[0] !== 'GET') {
                    continue;
                }

                $routePath = $this->routePrefix . $route[1];

                // Check for exactly match or pattern match
                if ($routePath === $path || preg_match('~' . str_replace('\{\w+:\w+\}', '\w+', preg_quote($routePath, '~')) . '~', $path)) {
                    array_unshift($breadcrumbs, [
                        'label' => $route[3][0],
                        'url' => $routePath,
                    ]);

                    // Assume second element in the route payload indicates parent route
                    // And it's a full route path similar to '/foo/bar'
                    $path = $route[3][1];
                    $matched = true;
                    break;
                }
            }

            // Break the loop if it didn't match any route
            if (!$matched) {
                break;
            }
        }

        return $breadcrumbs;
    }

    private function getURI(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        $cleanUri = strtok($uri, '?');
        return $cleanUri !== false ? rawurldecode($cleanUri) : '';
    }

}
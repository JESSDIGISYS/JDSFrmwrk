<?php

namespace JDS\Http\Middleware;


use Exception;
use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use JDS\Authentication\RuntimeException;
use JDS\Http\HttpException;
use JDS\Http\HttpRequestMethodException;
use JDS\Http\Request;
use JDS\Http\Response;
use League\Container\Container;
use function FastRoute\simpleDispatcher;

class ExtractRouteInfo implements MiddlewareInterface
{
	public function __construct(private readonly array $routes, private readonly string $routePath)
	{
	}

	/**
	 * @throws HttpException
	 * @throws HttpRequestMethodException
	 * @throws Exception
	 */
	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
		// create a dispatcher
		$dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) {

			foreach ($this->routes as $route) {
                $routePath = '/' . trim($this->routePath, '/');
                $route[1] = $routePath . '/' . trim($route[1], '/');
				$routeCollector->addRoute(...$route);
			}
		});

        // Generate the sitemap if it hasn't been updated in a month
        if ($this->shouldRegenerateSitemap()) {
            $this->generateSitemap($dispatcher);
        }

		// dispatch a URI, to obtain the route info
		$routeInfo = $dispatcher->dispatch(
			$request->getMethod(),
			$request->getPathInfo(),
		);

		switch ($routeInfo[0]) {
			case Dispatcher::FOUND:
				// set $request->routeHandler
				$request->setRouteHandler($routeInfo[1]);

				// set $request->routeHandlerArgs
				$request->setRouteHandlerArgs($routeInfo[2]);

				// inject route middleware on handler
                if (is_array($routeInfo[1]) && isset($routeInfo[1][2])) {
                    $requestHandler->injectMiddleware($routeInfo[1][2]);
                }
				break;

			case Dispatcher::METHOD_NOT_ALLOWED:
				$allowedMethods = implode(', ', $routeInfo[1]);
				$e = new HttpRequestMethodException("The allowed methods are $allowedMethods");
				$e->setStatusCode(405);
				throw $e;

			default:
				$e = new HttpException("Not found");
				$e->setStatusCode(404);
				throw $e;
		}

		return $requestHandler->handle($request);
	}

    private function shouldRegenerateSitemap(): bool
    {
        $sitemapPath = $this->routePath . '/sitemap.xml'; // Path to your sitemap file
        $oneMonthInSeconds = 30 * 24 * 60 * 60; // 30 days in seconds

        // Check if the sitemap file does not exist or if it hasn't been updated in the last month
        return !file_exists($sitemapPath) || (time() - filemtime($sitemapPath) > $oneMonthInSeconds);
    }
    private function generateSitemap($dispatcher, $baseUrl = 'https://jessdigisys.com') {
        // Create the root XML structure for a sitemap
        $sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $routeData = $dispatcher->getData(); // Retrieve route data from FastRoute.

        $lastModified = date('Y-m-d');
        foreach ($routeData as $routes) {
            foreach ($routes as $routeGroup) {
                foreach ($routeGroup as $route) {
                    // Extract route information
                    if (isset($route['route'])) {
                        $path = $route['route'];

                        // Skip dynamic routes like `/blog/{id}` if needed
                        if (strpos($path, '{') !== false) {
                            continue;
                        }

                        // Add a URL entry to the sitemap
                        $entry = $sitemap->addChild('url');
                        $entry->addChild('loc', htmlspecialchars($baseUrl . $path)); // Encode URL properly
                        $entry->addChild('lastmod', $lastModified);                 // Optional: Last modified date
                        $entry->addChild('changefreq', 'monthly');                  // Optional: Change frequency
                        $entry->addChild('priority', '0.8');                        // Optional: Priority
                    }
                }
            }
        }

        // Save the XML (or you could return the XML string)
        $sitemapPath = rtrim($this->routePath, '/') . '/sitemap.xml';
        if (!is_dir(dirname($sitemapPath)) || !is_writable(dirname($sitemapPath))) {
            throw new RuntimeException('Sitemap directory is not writable: ' . dirname($sitemapPath));
        }
        $sitemap->asXML($sitemapPath);
    }

}


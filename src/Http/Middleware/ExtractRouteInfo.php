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
use SimpleXMLElement;
use function FastRoute\simpleDispatcher;

class ExtractRouteInfo implements MiddlewareInterface
{
    public function __construct(private readonly array $routes, private readonly string $routePath, private readonly string $basePath)
    {
    }

    /**
     * @throws HttpException
     * @throws HttpRequestMethodException
     * @throws Exception
     */
    public function process(Request $request, RequestHandlerInterface $requestHandler): Response
    {
        // Capture route data during setup
        $routeData = [];
        $dispatcher = simpleDispatcher(function (RouteCollector $routeCollector) use (&$routeData) {
            foreach ($this->routes as $route) {
                // Use unified method for normalization and merging
                $route[1] = $this->mergeAndNormalizeRoutePath($this->routePath, $route[1]);

                // Add route to dispatcher
                $routeCollector->addRoute(...$route);
            }

            // Capture route data from the RouteCollector
            $routeData = $routeCollector->getData();
        });

        // Generate the sitemap if it hasn't been updated in a month
        if ($this->shouldRegenerateSitemap()) {
            $this->generateSitemap($routeData); // Provide the route data to the sitemap generator
        }

        // Dispatch a URI to obtain the route info
        $routeInfo = $dispatcher->dispatch(
            $request->getMethod(),
            $request->getPathInfo()
        );

        switch ($routeInfo[0]) {
            case Dispatcher::FOUND:
                // Set $request->routeHandler
                $request->setRouteHandler($routeInfo[1]);

                // Set $request->routeHandlerArgs
                $request->setRouteHandlerArgs($routeInfo[2]);

                // Inject route middleware on handler
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

    private function mergeAndNormalizeRoutePath(string $routePath, string $route): string
    {
        // Normalize the routePath
        $routePath = trim($routePath, '/') !== '' ? '/' . trim($routePath, '/') : '';

        // Normalize and concatenate with the given route
        return rtrim($routePath . '/' . ltrim(trim($route, '/'), '/'), '/');
    }


    private function shouldRegenerateSitemap(): bool
    {
        $sitemapPath = $this->basePath . '/sitemap.xml'; // Path to your sitemap file
        $oneMonthInSeconds = 30 * 24 * 60 * 60; // 30 days in seconds

        // Check if the sitemap file does not exist or if it hasn't been updated in the last month
        return !file_exists($sitemapPath) || (time() - filemtime($sitemapPath) > $oneMonthInSeconds);
    }

    private function generateSitemap(array $routeData, string $baseUrl = 'https://jessdigisys.com'): void
    {
        // Create the root XML structure for a sitemap
        $sitemap = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>');

        $lastModified = date('Y-m-d');

        // Loop through each HTTP method (e.g., "GET", "POST")
        foreach ($routeData[0] as $httpMethod => $routes) {
            // Loop through each individual route under the given HTTP method
            foreach ($routes as $path => $handler) {
                // Skip routes with dynamic placeholders like "/freelance/blogpost/{id}"
                if (strpos($path, '{') !== false) {
                    continue;
                }

                // Add a URL entry to the sitemap
                $entry = $sitemap->addChild('url');
                $entry->addChild('loc', htmlspecialchars($baseUrl . $path)); // Encode the URL
                $entry->addChild('lastmod', $lastModified);                 // Optional: Last modified date
                $entry->addChild('changefreq', 'monthly');                  // Optional: Change frequency
                $entry->addChild('priority', '0.8');                        // Optional: Priority
            }
        }

        // Save the XML (or you could return the XML string)
        $sitemapPath = rtrim($this->basePath, '/') . '/sitemap.xml';
        if (!is_dir(dirname($sitemapPath)) || !is_writable(dirname($sitemapPath))) {
            throw new \RuntimeException('Sitemap directory is not writable: ' . dirname($sitemapPath));
        }
        $sitemap->asXML($sitemapPath);
    }
}


<?php

namespace JDS\Routing;

/**
 * # Requirements & Definitions
 * - **Requirements:** array of routes, string for routePrefix
 *
 * - **Requirements:** nikic/fast-route
 *
 * ## Warning: Do not use with other routers
 *
 * - **Definition:** array of routes are the routes that are in the file web.php in the routes folder on client side
 *
 * - **Definition:** string for routePrefix: If you have a setup where you have a subfolder to your application this is
 * where it goes. Change it in the .env file on the client side ROUTE_PATH="/path"
 *
 * - **Definition:** Routes: return [['GET', '/', [Controller::class, 'method' <optional>, [Middleware::class]]<optional breadcrumb>, ['Name', parent(null if none), position, role, permission]]
 *
 * - **Definition:** position, role, permission will be used in an update
 *
 * - __construct(private readonly array \$routes, private readonly string \$routePrefix)
 *
 */
interface BreadcrumbGeneratorInterface
{
    /**
     * Generates an array of breadcrumbs based on the provided path.
     *
     * @param string $currentPath The current path from which breadcrumbs will be generated.
     * @return array An array representing the breadcrumb trail.
     */
    public function generateBreadcrumbs(string $currentPath): array;
}
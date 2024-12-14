<?php

namespace JDS\Routing;

interface MenuGeneratorInterface
{
    // make a constructor __construct(array $routes);

    public function generateMenu(): array;
}
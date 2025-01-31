<?php

namespace JDS\Http\Generators;

class MenuGenerator
{
	public function __construct(private array $routes)
	{
	}

	public function generateMenu(): array
	{
		$menu = [];
        foreach ($this->routes as $route) {
            if ($route[0] === 'GET') {
//        $controllerMethod = $route[2]; if controllers are wanted
//        $lastArray = is_array(end($route)) ? end($route) : null; if the last array is wanted
                $menu[] = [
                    'route' => $route[1],
//            'controller' => $controllerMethod,
//            'lastArray' => $lastArray
                ];
            }
        }
        $onlyRoutes = [];
        for ($x=0; $x < count($menu); $x++) {
            $onlyRoutes[] = $menu[$x]['route'];
        }

        return $onlyRoutes;
	}



}


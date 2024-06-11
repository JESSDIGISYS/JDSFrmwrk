<?php

namespace Tests;

use Pest\Arch\ValueObjects\Dependency;

class DependencyClass
{
	public function __construct(private SubDependencyClass $subDependency)
	{
	}

	public function getSubDependency(): SubDependencyClass
	{
		return $this->subDependency;
	}
}


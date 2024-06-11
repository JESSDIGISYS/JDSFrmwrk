<?php

namespace Framework\Tests;

use JDS\Framework\Container\Container;
use JDS\Framework\Container\ContainerException;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ContainerTest extends TestCase
{

	/**
	 * @throws ContainerExceptionInterface
	 * @throws NotFoundExceptionInterface
	 * @throws ContainerException
	 */
	public function test_a_service_can_be_retrieved_from_the_container(): void
	{
		// setup
		$container = new Container();

		// do something
		// id string, concrete class name string | object
		$container->add('dependant-class', DependantClass::class);

		// make assertions
		$this->assertInstanceOf(DependantClass::class, $container->get('dependant-class'));
	}

	public function
	test_a_ContainerException_is_thrown_if_a_service_cannot_be_found(): void
	{
		// setup
		$container = new Container();

		// expect exception
		$this->expectException(ContainerException::class);

		// do something
		$container->add('foobar');
	}

	/**
	 * @throws ContainerException
	 */
	public function test_can_check_if_the_container_has_a_service(): void
	{
		// setup
		$container = new Container();

		// do something
		$container->add('dependant-class', DependantClass::class);

		// make assertion
		$this->assertTrue($container->has('dependant-class'));
		$this->assertFalse($container->has('non-existent-class'));
	}

	public function test_services_can_be_recursively_autowired()
	{
		// setup
		$container = new Container();

		// do something
		$dependantService = $container->get(DependantClass::class);

		$dependancyService = $dependantService->getDependency();

		// make assertions
		$this->assertInstanceOf(DependencyClass::class, $dependancyService);
		$this->assertInstanceOf(SubDependencyClass::class, $dependancyService->getSubDependency());

	}
}



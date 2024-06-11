<?php

namespace JDS\Framework\Container;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements ContainerInterface
{
	private array $services = [];

	public function add(string $id, string|object $concrete = null): void
	{
		if (is_null($concrete)) {
			if (!class_exists($id)) {
				throw new ContainerException("Service {$id} could not be found");
			}
			$concrete = $id;
		}
		$this->services[$id] = $concrete;
	}

	public function get(string $id)
	{
		if (!$this->has($id)) {
			if (!class_exists($id)) {
				throw new ContainerException("Service {$id} could not be resolved");
			}
			$this->add($id);
		}

		$object = $this->resolve($this->services[$id]);

		return $object;
	}

	private function resolve($class) : object
	{
		// 1 instantiate a reflection class (dump to check)
		$reflectionClass = new \ReflectionClass($class);

		// 2 use reflection to try to obtain a class constructor
		$constructor = $reflectionClass->getConstructor();

		// 3 if there is no constructor, simply instantiate
		if (is_null($constructor)) {
			return $reflectionClass->newInstance();
		}

		// 4 get the constructor parameters
		$constructorParams = $constructor->getParameters();

		// 5 obtain dependencies
		$classDependencies = $this->resolveClassDependencies
		($constructorParams);

		// 6 instantiate with dependencies
		$service = $reflectionClass->newInstanceArgs($classDependencies);

		// 7 return the object
		return $service;
	}

	private function resolveClassDependencies(array $reflectionParameters): array
	{
		// 1 instialize empty dependencies array (required by newInstanceArgs)
		$classDependencies = [];

		// 2 try to locate and instantiate each parameter
		/** @var \ReflectionParameter $parameter */
		foreach ($reflectionParameters as $parameter) {

			// get the paramter's ReflectionNamedType as $serviceType
			$serviceType = $parameter->getType();

			// try to instantiate using $serviceType's name
			$service = $this->get($serviceType->getName());

			// add the service to the classDependencies array
			$classDependencies[] = $service;
		}

		// 3 return the classDependencies array
		return $classDependencies;
	}

	public function has(string $id): bool
	{
		return array_key_exists($id, $this->services);
	}
}
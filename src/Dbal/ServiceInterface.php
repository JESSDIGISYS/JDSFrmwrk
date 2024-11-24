<?php

namespace JDS\Dbal;

interface ServiceInterface
{
    public function lookup(string $id): ?Entity;
    public function save(Entity $entity): bool;
    public function delete(Entity $entity): bool;
}



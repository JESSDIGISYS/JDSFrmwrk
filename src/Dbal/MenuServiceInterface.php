<?php

namespace JDS\Dbal;

interface MenuServiceInterface extends ServiceInterface
{
    public function getMenuById(string $id): ?Entity;

}


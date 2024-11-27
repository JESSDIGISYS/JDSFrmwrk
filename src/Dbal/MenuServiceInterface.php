<?php

namespace JDS\Dbal;

interface MenuServiceInterface extends ServiceInterface
{
    public function getWithMenus(string $id): ?Entity;
}


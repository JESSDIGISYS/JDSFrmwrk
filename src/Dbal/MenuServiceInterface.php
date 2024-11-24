<?php

namespace JDS\Dbal;

interface MenuServiceInterface extends ServiceInterface
{
    public function getPrimaryMenuById(string $id): ?Entity;

    public function getSecondaryMenusByPrimaryMenuId(string $id): array;

    public function getTertiaryMenusBySecondaryMenuId(string $id): array;

}


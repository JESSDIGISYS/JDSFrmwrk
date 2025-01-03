<?php

namespace JDS\Forms;

use JDS\Dbal\Entity;

interface FormsInterface
{
    public function setFields(array $fields): void;

    public function save(): Entity;

    public function hasValidationErrors(): bool;

    public function getValidationErrors(): array;

}
<?php

namespace JDS\Templates;

use Twig\Environment;

interface TwigFactoryInterface
{
    public function create(): Environment;

}
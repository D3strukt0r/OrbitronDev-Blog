<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class EnvExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getenv', [$this, 'getEnvVariable']),
        ];
    }

    public function getEnvVariable(string $variable)
    {
        return $_SERVER[$variable];
    }
}

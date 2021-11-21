<?php

namespace App\Twig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{


    public function getFunctions(): array
    {
        return [
            new TwigFunction('pluralize', [$this, 'pluralize']),
        ];
    }

    public function pluralize(int $count, string $singular): string
    {
        if ($count == 1) {
            return "$count $singular";
        }
        $plural = $singular . 's';
        return "$count $plural";
    }
}

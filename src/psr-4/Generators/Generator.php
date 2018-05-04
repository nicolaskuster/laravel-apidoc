<?php

namespace Nicolaskuster\ApiDoc\Generators;

use Illuminate\Support\Collection;

interface Generator
{
    /**
     * Generate documentation for the provided routes.
     *
     * @param Collection $routes
     */
    public function generate(Collection $routes): void;
}
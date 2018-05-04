<?php

namespace Nicolaskuster\ApiDoc\Loader;

use Illuminate\Support\Collection;

interface RouteLoader
{
    /**
     * Returns a array of Routes
     *
     * @return Collection
     */
    public function getRoutes(): Collection;
}
<?php

namespace Nicolaskuster\ApiDoc\Loader;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route as RouteFacade;

class LaravelRouteLoader implements RouteLoader
{
    /**
     * Loader Config
     *
     * @var array
     */
    protected $config;

    /**
     * LaravelRouteLoader constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutes(): Collection
    {
        return collect(array_map(function (Route $route) {
            return new $this->config['routeModel']($route, $this->config);
        }, RouteFacade::getRoutes()->getRoutes()));
    }
}
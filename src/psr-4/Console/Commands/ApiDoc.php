<?php

namespace Nicolaskuster\ApiDoc\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Nicolaskuster\ApiDoc\Generators\Generator;
use Nicolaskuster\ApiDoc\Loader\RouteLoader;
use Nicolaskuster\ApiDoc\Support\RouteSorter;


class ApiDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api:doc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates Api Documentation based on the routes';

    /**
     * ApiDoc Config
     *
     * @var array
     */
    protected $config;

    /**
     * Config defaults
     *
     * @var array
     */
    protected $configDefaults;

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->config = config('apiDoc');
        $this->configDefaults = $this->config['defaults'];

        $routes = $this->loadRoutes();
        $sortedRoutes = $this->sortRoutes($routes);
        $generatorConfigs = $this->getGeneratorConfigs($this->configDefaults['generators']);

        foreach ($generatorConfigs as $generatorConfig) {
            $this->generate($generatorConfig, $sortedRoutes);
        }

        return true;
    }

    /**
     * Load all routes
     * @return Collection
     */
    protected function loadRoutes(): Collection
    {
        $loaderConfig = $this->config['loaders'][$this->configDefaults['loader']];
        /** @var RouteLoader $loader */
        $loader = app()->makeWith($loaderConfig['loader'], ['config' => $loaderConfig]);
        return $loader->getRoutes();
    }

    /**
     * Sort the Routes
     *
     * @param Collection $routes
     * @return Collection
     */
    protected function sortRoutes(Collection $routes): Collection
    {
        $sorterConfig = $this->config['sorters'][$this->configDefaults['sorter']];
        /** @var RouteSorter $sorter */
        $sorter = app()->makeWith($sorterConfig['sorter'], ['config' => $sorterConfig]);
        return $sorter->sort($routes);
    }

    /**
     * Get Generator Configs for the provided Generator Names
     *
     * @param array $generatorNames
     * @return array
     */
    protected function getGeneratorConfigs($generatorNames): array
    {
        return array_map(function ($generatorName) {
            return $this->config['generators'][$generatorName];
        }, $generatorNames);
    }

    /**
     * Generate the Documentation
     *
     * @param array $generatorConfig
     * @param Collection $routes
     */
    protected function generate(array $generatorConfig, Collection $routes): void
    {
        /** @var Generator $generator */
        $generator = app()->makeWith($generatorConfig['generator'], ['config' => $generatorConfig]);
        $generator->generate($routes);
    }
}

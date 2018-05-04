<?php

namespace Nicolaskuster\ApiDoc\Generators;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Ramsey\Uuid\Uuid;
use Nicolaskuster\ApiDoc\Models\Route;
use Nicolaskuster\ApiDoc\Models\ValidationRule;

class PostmanCollectionGenerator implements Generator
{
    const OUTPUT_STYLE_FLAT = 0;
    const OUTPUT_STYLE_GROUPED = 1;

    /**
     * The output path for the documentation
     *
     * @var string
     */
    protected $outputPath;

    /**
     * Collection Config
     *
     * @var array
     */
    protected $collectionConfig = [];

    /**
     * Output style
     *
     * @var int
     */
    protected $outputStyle = self::OUTPUT_STYLE_FLAT;

    /**
     * PostmanCollectionGenerator constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->outputPath = $config['outputPath'];
        $this->collectionConfig = $config['collection'];
        $this->outputStyle = $config['outputStyle'];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(Collection $routes): void
    {
        $collection['variables'] = [];
        foreach ($this->collectionConfig['variables'] as $name => $value) {
            $collection['variables'][] = [
                'id' => $name,
                'value' => $value,
            ];
        }

        $collection['info'] = [
            'name' => $this->collectionConfig['name'],
            '_postman_id' => Uuid::uuid4()->toString(),
            'description' => $this->collectionConfig['description'],
            'schema' => 'https://schema.getpostman.com/json/collection/v2.0.0/collection.json',
        ];

        switch ($this->outputStyle) {
            case self::OUTPUT_STYLE_GROUPED:
                $routes = $routes->groupBy(function (Route $route) {
                    return $route->getGroupId();
                });
        }

        $collection['item'] = [];
        foreach ($routes as $key => $item) {
            $collection['item'][] = $this->encodeMixed($key, $item);
        }

        File::put($this->outputPath, json_encode($collection));
    }

    /**
     * Encode Collection Folder or Route
     *
     * @param string $key
     * @param Collection | Route $item
     * @return array
     */
    protected function encodeMixed($key, $item)
    {
        if ($item instanceof Route) {
            return $this->encodeRoute($item);
        } elseif ($item instanceof Collection) {
            return $this->encodeGroup($key, '', $item);
        }
    }

    /**
     * Encode Collection Folder
     *
     * @param string $name
     * @param string $description
     * @param Collection $routes
     * @return array
     */
    protected function encodeGroup($name, $description, Collection $routes)
    {
        return [
            'name' => $name,
            'description' => $description,
            'item' => array_map(function ($item) use ($name) {
                return $this->encodeMixed($name, $item);
            }, $routes->toArray()),
        ];
    }

    /**
     * Encode Collection Route
     *
     * @param Route $route
     * @return array
     */
    protected function encodeRoute(Route $route)
    {
        $config = $this->getRouteConfig($route);
        $config['headers'] = $this->encodeHeaders($config['headers']);
        return [
            'name' => $route->getName(),
            'request' => [
                'url' => '{{' . $config['urlVariableKey'] . '}}/' . $route->getUri(),
                'method' => $route->getMethods()[0],
                'header' => $config['headers'],
                'body' => [
                    'mode' => 'urlencoded',
                    'urlencoded' => $route->getValidationRules() ? array_map(function (ValidationRule $validationRule) {
                        return [
                            'key' => $validationRule->getAttributeName(),
                            'value' => '',
                            'description' => implode(' | ', $validationRule->getRules()),
                            'type' => 'text'
                        ];
                    }, $route->getValidationRules()) : []
                ],
                'description' => ''
            ],
            'response' => []
        ];
    }

    /**
     * Encode Route Headers
     *
     * @param [] $headers
     * @return array
     */
    protected function encodeHeaders($headers)
    {
        $encoded = [];
        foreach ($headers as $key => $value) {
            $header = $value;
            $header['key'] = $key;
            $encoded[] = $header;
        }
        return $encoded;
    }

    /**
     * Combine the configs for the Route and Middlewares
     *
     * @param Route $route
     * @return array
     */
    protected function getRouteConfig(Route $route)
    {
        $config = $this->collectionConfig;
        $middlewareConfig = $this->collectionConfig['perMiddleware'];

        foreach ($middlewareConfig as $key => $value) {
            if (in_array($key, $route->getMiddlewares())) {
                $config = $this->mergeConfig($config, $middlewareConfig[$key]);
            }
        }

        return $config;
    }

    /**
     * Merge configs
     *
     * @param $baseConfig
     * @param $mergeConfig
     * @return array
     */
    protected function mergeConfig($baseConfig, $mergeConfig)
    {
        return array_replace_recursive($baseConfig, $mergeConfig);
    }
}
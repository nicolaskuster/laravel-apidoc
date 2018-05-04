<?php

namespace Nicolaskuster\ApiDoc\Models;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route as OriginalRoute;
use ReflectionClass;

class LaravelRoute implements Route
{
    /**
     * Loader Config
     *
     * @var array
     */
    protected $config;

    /**
     * Original Route
     *
     * @var LaravelRoute
     */
    protected $originalRoute;

    /**
     * Route name
     *
     * @var string
     */
    protected $name;

    /**
     * is the Route action a closure
     *
     * @var bool
     */
    protected $isClosure;

    /**
     * Validation Rules
     *
     * @var ValidationRule[]
     */
    protected $validationRules;

    /**
     * Class name of the route action
     *
     * @var string
     */
    protected $actionClass = '';

    /**
     * Method name of the route action
     *
     * @var string
     */
    protected $actionMethod = '';

    /**
     * Rest methods
     * Used for sortId
     *
     * @var array
     */
    protected $restMethods = ['index', 'create', 'store', 'show', 'update', 'destroy'];

    /**
     * LaravelRoute constructor.
     *
     * @param OriginalRoute $route
     * @param array $config
     */
    public function __construct(OriginalRoute $route, $config = [])
    {
        $this->originalRoute = $route;
        $this->config = $config;

        $this->name = $route->getName() ?? $this->getUri();
        $this->isClosure = $route->getActionName() === "Closure";
        if (!$this->isClosure) {
            list($this->actionClass, $this->actionMethod) = explode('@', $route->getActionName());
        }

        $this->validationRules = $this->fetchValidationRules();
    }

    /**
     * Fetch all validation rules of the route
     * @return LaravelValidationRule[]|null
     * @throws \ReflectionException
     */
    protected function fetchValidationRules()
    {
        if ($this->isClosure) return null;

        $reflection = new ReflectionClass($this->actionClass);
        $methodParameters = $reflection->getMethod($this->actionMethod)->getParameters();

        foreach ($methodParameters as $parameter) {
            /** @var \ReflectionParameter $parameter */
            if (!$parameter->hasType()) {
                continue;
            }

            $parameterType = $parameter->getType()->getName();
            try {
                //Continue when its a primitive (int, float, array, string, ...) type
                $parameterReflection = new ReflectionClass($parameterType);
            } catch (\ReflectionException $e) {
                continue;
            }

            if ($parameterReflection->isSubclassOf(FormRequest::class)) {
                /** @var FormRequest $formRequest */
                $formRequest = new $parameterType();
                $validationRules = [];


                foreach ($formRequest->rules() as $key => $rule) {
                    $validationRules[] = new LaravelValidationRule($key, $rule);
                }

                return $validationRules;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri(): string
    {
        return $this->originalRoute->uri();
    }

    /**
     * {@inheritdoc}
     */
    public function getGroupId(): string
    {
        return $this->isClosure ? 'closures' : $this->actionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getSortKey(): string
    {
        return in_array($this->actionMethod, $this->restMethods) ? $this->actionMethod : join(' | ', $this->getMethods());
    }

    /**
     * {@inheritdoc}
     */
    public function getMethods(): array
    {
        return $this->originalRoute->methods();
    }

    /**
     * {@inheritdoc}
     */
    public function getValidationRules(): ?array
    {
        return $this->validationRules;
    }

    /**
     * {@inheritdoc}
     */
    public function getMiddlewares(): array
    {
        return $this->originalRoute->middleware();
    }
}
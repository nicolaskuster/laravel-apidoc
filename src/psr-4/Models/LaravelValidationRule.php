<?php

namespace Nicolaskuster\ApiDoc\Models;


class LaravelValidationRule implements ValidationRule
{
    /**
     * Name of the attribute
     *
     * @var string
     */
    protected $attributeName;

    /**
     * Input rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * LaravelValidationRule constructor.
     *
     * @param string $attributeName
     * @param string $rules
     */
    public function __construct($attributeName, $rules)
    {
        $this->attributeName = $attributeName;

        switch (true) {
            case is_array($rules):
                break;
            case is_string($rules):
                $rules = explode('|', $rules);
                break;
        }

        $this->rules = array_map(function ($item) {
            switch (true) {
                case is_string($item):
                    return trim($item);
                    break;
                case (is_object($item) && method_exists($item, '__toString')):
                    return $item->__toString();
                case ((new \ReflectionFunction($item))->isClosure()):
                    return '**Closure**';
                default:
                    return '**Unknown**';
            }

        }, $rules);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeName(): string
    {
        return $this->attributeName;
    }

    /**
     * {@inheritdoc}
     */
    public function getRules(): array
    {
        return $this->rules;
    }
}
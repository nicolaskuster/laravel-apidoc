<?php

namespace Nicolaskuster\ApiDoc\Models;


interface Route
{
    /**
     * Get the name of the route
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get the uri of the route
     *
     * @return string
     */
    public function getUri(): string;

    /**
     * Get the group id of the route
     *
     * @return string
     */
    public function getGroupId(): string;

    /**
     * Get the key for sorting the routes
     *
     * @return string
     */
    public function getSortKey(): string;

    /**
     * Get the http-methods of the route
     *
     * @return array
     */
    public function getMethods(): array;

    /**
     * Get the validation rules of the route
     *
     * @return ValidationRule[] | null
     */
    public function getValidationRules(): ?array;

    /**
     * Get the Middlewares for the route
     *
     * @return array
     */
    public function getMiddlewares(): array;
}
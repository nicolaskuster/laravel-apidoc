<?php

namespace Nicolaskuster\ApiDoc\Models;


interface ValidationRule
{
    /**
     * Get the attribute name
     *
     * @return string
     */
    public function getAttributeName(): string;

    /**
     * Return the rules for the input
     *
     * @return array
     */
    public function getRules(): array;
}
<?php

namespace Nicolaskuster\ApiDoc\Support;


use Illuminate\Support\Collection;

interface RouteSorter
{
    /**
     * Sort the provided Collection
     *
     * @param Collection $collection
     * @return Collection
     */
    public function sort(Collection $collection): Collection;
}